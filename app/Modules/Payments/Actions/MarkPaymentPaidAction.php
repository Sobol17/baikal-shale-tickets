<?php

namespace App\Modules\Payments\Actions;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Events\BookingPaid;
use App\Modules\Payments\Data\PaymentStatus;
use App\Modules\Payments\Models\Payment;
use Illuminate\Support\Facades\DB;

class MarkPaymentPaidAction
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(Payment $payment, array $payload = []): Payment
    {
        $paidBooking = null;

        $payment = DB::transaction(function () use ($payment, $payload, &$paidBooking): Payment {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $booking = $payment->booking()->lockForUpdate()->firstOrFail();
            $wasPaid = $booking->status === BookingStatus::Paid;
            $paidAt = now();

            if ($payment->status !== PaymentStatus::Paid) {
                $payment->update([
                    'status' => PaymentStatus::Paid,
                    'raw_response' => array_replace($payment->raw_response ?? [], $payload),
                    'paid_at' => $payment->paid_at ?? $paidAt,
                ]);
            }

            if (! $wasPaid) {
                $booking->update([
                    'status' => BookingStatus::Paid,
                    'paid_at' => $booking->paid_at ?? $paidAt,
                ]);

                $paidBooking = $booking->refresh();
            }

            return $payment->refresh();
        });

        if ($paidBooking !== null) {
            BookingPaid::dispatch($paidBooking);
        }

        return $payment;
    }
}
