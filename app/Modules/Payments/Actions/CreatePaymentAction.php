<?php

namespace App\Modules\Payments\Actions;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Payments\Contracts\PaymentGateway;
use App\Modules\Payments\Data\PaymentStatus;
use App\Modules\Payments\Models\Payment;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreatePaymentAction
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    public function handle(Booking $booking): Payment
    {
        if ($booking->status !== BookingStatus::PendingPayment) {
            throw ValidationException::withMessages([
                'booking' => 'Оплату можно создать только для бронирования в ожидании оплаты.',
            ]);
        }

        $payment = Payment::query()->create([
            'booking_id' => $booking->id,
            'provider' => config('services.payments.default', 'fake'),
            'amount_cents' => $booking->amount_cents,
            'status' => PaymentStatus::Pending,
        ]);

        try {
            $result = $this->gateway->initialize($payment->loadMissing('booking.slot.excursion'));
        } catch (Throwable $exception) {
            report($exception);

            return $payment;
        }

        $payment->update([
            'provider_payment_id' => $result->providerPaymentId,
            'payment_url' => $result->paymentUrl,
            'raw_request' => $result->requestPayload,
            'raw_response' => $result->responsePayload,
        ]);

        return $payment->refresh();
    }
}
