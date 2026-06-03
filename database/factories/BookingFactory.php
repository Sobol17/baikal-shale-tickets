<?php

namespace Database\Factories;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Models\ExcursionSlot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'excursion_slot_id' => ExcursionSlot::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'passengers' => [
                [
                    'ticket_type' => 'adult',
                    'last_name' => fake()->lastName(),
                    'first_name' => fake()->firstName(),
                    'middle_name' => null,
                    'citizenship' => 'Россия',
                    'phone' => fake()->phoneNumber(),
                ],
            ],
            'seats_count' => 1,
            'amount_cents' => 200000,
            'status' => BookingStatus::PendingPayment,
        ];
    }
}
