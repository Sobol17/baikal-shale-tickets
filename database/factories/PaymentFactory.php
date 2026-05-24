<?php

namespace Database\Factories;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Payments\Data\PaymentStatus;
use App\Modules\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'provider' => 'fake',
            'provider_payment_id' => null,
            'amount_cents' => 200000,
            'status' => PaymentStatus::Pending,
            'payment_url' => null,
            'raw_request' => null,
            'raw_response' => null,
        ];
    }
}
