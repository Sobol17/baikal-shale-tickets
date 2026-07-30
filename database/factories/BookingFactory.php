<?php

namespace Database\Factories;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Models\Excursion;
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
            'excursion_id' => Excursion::factory()->state([
                'adult_price_cents' => 200000,
                'child_price_cents' => 100000,
            ]),
            'excursion_date' => now()->addWeek()->toDateString(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'adult_tickets_count' => 1,
            'child_tickets_count' => 0,
            'companions' => null,
            'seats_count' => 1,
            'amount_cents' => 20000,
            'status' => BookingStatus::PendingPayment,
        ];
    }
}
