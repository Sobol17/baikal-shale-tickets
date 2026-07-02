<?php

namespace App\Modules\Payments\Contracts;

use App\Modules\Payments\Data\PaymentInitializationResult;
use App\Modules\Payments\Models\Payment;

interface PaymentGateway
{
    public function initialize(Payment $payment): PaymentInitializationResult;
}
