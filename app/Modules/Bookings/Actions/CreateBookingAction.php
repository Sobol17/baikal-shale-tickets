<?php

namespace App\Modules\Bookings\Actions;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Support\BookingPriceCalculator;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateBookingAction
{
    public function __construct(private readonly BookingPriceCalculator $prices) {}

    /**
     * @param  array{excursion_id:int, excursion_date:string, customer_name:string, customer_email:string, customer_phone:string, companions:array<int, array<string, mixed>>, adult_tickets_count:int, child_tickets_count:int, seats_count:int}  $data
     */
    public function handle(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $excursion = Excursion::query()
                ->lockForUpdate()
                ->findOrFail($data['excursion_id']);

            $excursionDate = Carbon::parse($data['excursion_date'])->startOfDay();
            $bookingStartsOn = $excursion->booking_starts_on?->startOfDay();
            $bookingEndsOn = $excursion->booking_ends_on?->startOfDay();

            if (
                $excursion->status !== ExcursionStatus::Active
                || $bookingStartsOn === null
                || $bookingEndsOn === null
                || $excursionDate->isBefore(today())
                || $excursionDate->isBefore($bookingStartsOn)
                || $excursionDate->isAfter($bookingEndsOn)
            ) {
                throw ValidationException::withMessages([
                    'excursion_date' => 'Эта дата недоступна для бронирования.',
                ]);
            }

            $price = $this->prices->calculate(
                $excursion,
                $data['adult_tickets_count'],
                $data['child_tickets_count'],
            );

            return Booking::query()->create([
                'uuid' => (string) Str::uuid(),
                'excursion_id' => $excursion->id,
                'excursion_date' => $excursionDate->toDateString(),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'adult_tickets_count' => $data['adult_tickets_count'],
                'child_tickets_count' => $data['child_tickets_count'],
                'companions' => $data['companions'] === [] ? null : $data['companions'],
                'seats_count' => $data['seats_count'],
                'amount_cents' => $price->prepaymentCents,
                'status' => BookingStatus::PendingPayment,
            ]);
        });
    }
}
