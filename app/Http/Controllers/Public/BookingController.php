<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Actions\CreateBookingAction;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionSlotStatus;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\ExcursionSlot;
use App\Modules\Payments\Actions\CreatePaymentAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function create(ExcursionSlot $slot): View
    {
        $slot->loadMissing('excursion');

        abort_unless(
            $slot->excursion->status === ExcursionStatus::Active
            && $slot->status === ExcursionSlotStatus::Active
            && $slot->starts_at->isFuture(),
            404,
        );

        return view('bookings.create', [
            'slot' => $slot,
        ]);
    }

    public function store(Request $request, CreateBookingAction $createBooking, CreatePaymentAction $createPayment): RedirectResponse
    {
        $passengersCount = max(1, min(20, (int) $request->input('seats_count', 1)));

        $data = $request->validate([
            'excursion_slot_id' => ['required', 'integer', 'exists:excursion_slots,id'],
            'passengers' => ['required', 'array', 'size:'.$passengersCount],
            'passengers.*.ticket_type' => ['required', Rule::in(['adult', 'child'])],
            'passengers.*.last_name' => ['required', 'string', 'max:255'],
            'passengers.*.first_name' => ['required', 'string', 'max:255'],
            'passengers.*.middle_name' => ['nullable', 'string', 'max:255'],
            'passengers.*.no_middle_name' => ['nullable', 'boolean'],
            'passengers.*.citizenship' => ['required', 'string', 'max:255'],
            'passengers.*.phone' => ['nullable', 'string', 'max:64'],
            'customer_last_name' => ['required', 'string', 'max:255'],
            'customer_first_name' => ['required', 'string', 'max:255'],
            'customer_middle_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['required', 'email:rfc', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:64'],
            'seats_count' => ['required', 'integer', 'min:1', 'max:20'],
            'terms_accepted' => ['accepted'],
        ]);

        $booking = $createBooking->handle([
            'excursion_slot_id' => $data['excursion_slot_id'],
            'customer_name' => $this->fullName(
                $data['customer_last_name'],
                $data['customer_first_name'],
                $data['customer_middle_name'] ?? null,
            ),
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'passengers' => $this->normalizePassengers($data['passengers']),
            'seats_count' => $data['seats_count'],
        ]);
        $payment = $createPayment->handle($booking);

        if (blank($payment->payment_url)) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('status', 'Бронирование создано, но платежная ссылка временно недоступна. Попробуйте продолжить оплату позже.');
        }

        return redirect()->away((string) $payment->payment_url);
    }

    public function show(Booking $booking): View
    {
        return view('bookings.show', [
            'booking' => $booking->loadMissing('slot.excursion', 'payments'),
        ]);
    }

    private function fullName(string $lastName, string $firstName, ?string $middleName): string
    {
        return collect([$lastName, $firstName, $middleName])
            ->filter(fn (?string $part): bool => filled($part))
            ->implode(' ');
    }

    /**
     * @param  array<int, array<string, mixed>>  $passengers
     * @return array<int, array<string, mixed>>
     */
    private function normalizePassengers(array $passengers): array
    {
        return collect($passengers)
            ->map(fn (array $passenger): array => [
                'ticket_type' => $passenger['ticket_type'],
                'last_name' => $passenger['last_name'],
                'first_name' => $passenger['first_name'],
                'middle_name' => $passenger['middle_name'] ?? null,
                'no_middle_name' => (bool) ($passenger['no_middle_name'] ?? false),
                'citizenship' => $passenger['citizenship'],
                'phone' => $passenger['phone'] ?? null,
            ])
            ->all();
    }
}
