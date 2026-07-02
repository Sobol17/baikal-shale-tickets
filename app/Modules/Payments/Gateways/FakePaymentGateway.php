<?php

namespace App\Modules\Payments\Gateways;

use App\Modules\Payments\Contracts\PaymentGateway;
use App\Modules\Payments\Data\PaymentInitializationResult;
use App\Modules\Payments\Models\Payment;

class FakePaymentGateway implements PaymentGateway
{
    public function initialize(Payment $payment): PaymentInitializationResult
    {
        return new PaymentInitializationResult(
            providerPaymentId: 'fake-'.$payment->id,
            paymentUrl: route('payments.fake.paid', $payment),
            requestPayload: [
                'provider' => 'fake',
                'payment_id' => $payment->id,
            ],
            responsePayload: [
                'Success' => true,
                'PaymentId' => 'fake-'.$payment->id,
                'PaymentURL' => route('payments.fake.paid', $payment),
            ],
        );
    }
}
