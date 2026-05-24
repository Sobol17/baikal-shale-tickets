<?php

namespace App\Modules\Payments\Actions;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Payments\Data\PaymentStatus;
use App\Modules\Payments\Models\Payment;
use Illuminate\Support\Facades\DB;

class MarkPaymentFailedAction
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(Payment $payment, array $payload = []): Payment
    {
        return DB::transaction(function () use ($payment, $payload): Payment {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === PaymentStatus::Paid) {
                return $payment;
            }

            $payment->update([
                'status' => PaymentStatus::Failed,
                'raw_response' => array_replace($payment->raw_response ?? [], $payload),
                'failed_at' => $payment->failed_at ?? now(),
            ]);

            $payment->booking()->update([
                'status' => BookingStatus::Failed,
            ]);

            return $payment->refresh();
        });
    }
}
