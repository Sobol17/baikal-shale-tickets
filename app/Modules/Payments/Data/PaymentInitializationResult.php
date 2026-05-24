<?php

namespace App\Modules\Payments\Data;

final readonly class PaymentInitializationResult
{
    /**
     * @param  array<string, mixed>  $requestPayload
     * @param  array<string, mixed>  $responsePayload
     */
    public function __construct(
        public ?string $providerPaymentId,
        public string $paymentUrl,
        public array $requestPayload = [],
        public array $responsePayload = [],
    ) {}
}
