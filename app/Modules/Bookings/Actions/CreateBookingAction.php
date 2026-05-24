<?php

namespace App\Modules\Bookings\Actions;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionSlotStatus;
use App\Modules\Excursions\Models\ExcursionSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateBookingAction
{
    /**
     * @param  array{excursion_slot_id:int, customer_name:string, customer_email:string, customer_phone:string, passengers:array<int, array<string, mixed>>, seats_count:int}  $data
     */
    public function handle(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $slot = ExcursionSlot::query()
                ->with('excursion')
                ->lockForUpdate()
                ->findOrFail($data['excursion_slot_id']);

            if ($slot->status !== ExcursionSlotStatus::Active || $slot->starts_at->isPast()) {
                throw ValidationException::withMessages([
                    'excursion_slot_id' => 'Этот слот недоступен для бронирования.',
                ]);
            }

            return Booking::query()->create([
                'uuid' => (string) Str::uuid(),
                'excursion_slot_id' => $slot->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'passengers' => $data['passengers'],
                'seats_count' => $data['seats_count'],
                'amount_cents' => $slot->price_cents * $data['seats_count'],
                'status' => BookingStatus::PendingPayment,
            ]);
        });
    }
}
