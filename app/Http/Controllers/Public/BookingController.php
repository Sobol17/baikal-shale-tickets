<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Actions\CreateBookingAction;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Payments\Actions\CreatePaymentAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function create(Request $request): View
    {
        $data = $request->validate([
            'excursion_id' => ['required', 'integer', 'exists:excursions,id'],
            'excursion_date' => ['required', 'date_format:Y-m-d'],
            'adult_tickets_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'child_tickets_count' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $this->ensureTicketCountIsValid(
            (int) ($data['adult_tickets_count'] ?? 1),
            (int) ($data['child_tickets_count'] ?? 0),
        );

        $excursion = Excursion::query()->findOrFail($data['excursion_id']);

        abort_unless(
            $excursion->status === ExcursionStatus::Active
            && $this->dateIsBookable($excursion, $data['excursion_date']),
            404,
        );

        return view('bookings.create', [
            'excursion' => $excursion,
            'excursionDate' => $data['excursion_date'],
            'adultTicketsCount' => (int) ($data['adult_tickets_count'] ?? 1),
            'childTicketsCount' => (int) ($data['child_tickets_count'] ?? 0),
        ]);
    }

    public function store(Request $request, CreateBookingAction $createBooking, CreatePaymentAction $createPayment): RedirectResponse
    {
        $adultTicketsCount = (int) $request->input('adult_tickets_count', 0);
        $childTicketsCount = (int) $request->input('child_tickets_count', 0);
        $companionsCount = max(0, ($adultTicketsCount + $childTicketsCount) - 1);

        $data = $request->validate([
            'excursion_id' => ['required', 'integer', 'exists:excursions,id'],
            'excursion_date' => ['required', 'date_format:Y-m-d'],
            'adult_tickets_count' => ['required', 'integer', 'min:0', 'max:20'],
            'child_tickets_count' => ['required', 'integer', 'min:0', 'max:20'],
            'companions' => [$companionsCount > 0 ? 'required' : 'nullable', 'array', 'size:'.$companionsCount],
            'companions.*.last_name' => ['required', 'string', 'max:255'],
            'companions.*.first_name' => ['required', 'string', 'max:255'],
            'customer_last_name' => ['required', 'string', 'max:255'],
            'customer_first_name' => ['required', 'string', 'max:255'],
            'customer_middle_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['required', 'email:rfc', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:64'],
            'terms_accepted' => ['accepted'],
        ]);

        $seatsCount = $this->ensureTicketCountIsValid(
            (int) $data['adult_tickets_count'],
            (int) $data['child_tickets_count'],
        );

        $booking = $createBooking->handle([
            'excursion_id' => $data['excursion_id'],
            'excursion_date' => $data['excursion_date'],
            'customer_name' => $this->fullName(
                $data['customer_last_name'],
                $data['customer_first_name'],
                $data['customer_middle_name'] ?? null,
            ),
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'companions' => $this->normalizeCompanions($data['companions'] ?? []),
            'adult_tickets_count' => (int) $data['adult_tickets_count'],
            'child_tickets_count' => (int) $data['child_tickets_count'],
            'seats_count' => $seatsCount,
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
            'booking' => $booking->loadMissing('excursion', 'payments'),
        ]);
    }

    private function fullName(string $lastName, string $firstName, ?string $middleName): string
    {
        return collect([$lastName, $firstName, $middleName])
            ->filter(fn (?string $part): bool => filled($part))
            ->implode(' ');
    }

    /**
     * @param  array<int, array<string, mixed>>  $companions
     * @return array<int, array<string, mixed>>
     */
    private function normalizeCompanions(array $companions): array
    {
        return collect($companions)
            ->map(fn (array $companion): array => [
                'last_name' => $companion['last_name'],
                'first_name' => $companion['first_name'],
            ])
            ->all();
    }

    private function ensureTicketCountIsValid(int $adultTicketsCount, int $childTicketsCount): int
    {
        $seatsCount = $adultTicketsCount + $childTicketsCount;

        if ($seatsCount < 1 || $seatsCount > 20) {
            throw ValidationException::withMessages([
                'adult_tickets_count' => 'Выберите от 1 до 20 билетов.',
            ]);
        }

        return $seatsCount;
    }

    private function dateIsBookable(Excursion $excursion, string $date): bool
    {
        $excursionDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();

        return $excursion->booking_starts_on !== null
            && $excursion->booking_ends_on !== null
            && ! $excursionDate->isBefore(today())
            && ! $excursionDate->isBefore($excursion->booking_starts_on->startOfDay())
            && ! $excursionDate->isAfter($excursion->booking_ends_on->startOfDay());
    }
}
