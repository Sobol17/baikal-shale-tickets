<?php

namespace App\Modules\Payments\Actions;

use App\Modules\Payments\Contracts\PaymentGateway;
use App\Modules\Payments\Models\Payment;

class SyncPaymentStateAction
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly MarkPaymentPaidAction $markPaid,
        private readonly MarkPaymentFailedAction $markFailed,
    ) {}

    public function handle(Payment $payment): Payment
    {
        $state = $this->gateway->getState($payment);
        $status = strtoupper((string) ($state['Status'] ?? ''));

        if (in_array($status, ['CONFIRMED', 'AUTHORIZED'], true)) {
            return $this->markPaid->handle($payment, ['sync' => $state]);
        }

        if (in_array($status, ['REJECTED', 'CANCELED', 'DEADLINE_EXPIRED'], true)) {
            return $this->markFailed->handle($payment, ['sync' => $state]);
        }

        $payment->update([
            'raw_response' => array_replace($payment->raw_response ?? [], ['sync' => $state]),
        ]);

        return $payment->refresh();
    }
}
