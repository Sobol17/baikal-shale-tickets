<?php

namespace App\Modules\Payments\Gateways;

use App\Modules\Payments\Contracts\PaymentGateway;
use App\Modules\Payments\Data\PaymentInitializationResult;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Support\TBankToken;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TBankPaymentGateway implements PaymentGateway
{
    public function __construct(private readonly TBankToken $tokens) {}

    public function initialize(Payment $payment): PaymentInitializationResult
    {
        $payment->loadMissing('booking.slot.excursion');
        $booking = $payment->booking;
        $slot = $booking->slot;
        $excursion = $slot->excursion;

        $payload = [
            'TerminalKey' => config('services.tbank.terminal_key'),
            'Amount' => $payment->amount_cents,
            'OrderId' => $booking->uuid,
            'Description' => str($excursion->title)->limit(140, '')->toString(),
            'PayType' => 'O',
            'Language' => 'ru',
            'NotificationURL' => config('services.tbank.notification_url'),
            'SuccessURL' => config('services.tbank.success_url'),
            'FailURL' => config('services.tbank.fail_url'),
            'Receipt' => [
                'Email' => $booking->customer_email,
                'Phone' => $booking->customer_phone,
                'Taxation' => config('services.tbank.taxation'),
                'Items' => [[
                    'Name' => str($excursion->title)->limit(128, '')->toString(),
                    'Price' => $slot->price_cents,
                    'Quantity' => $booking->seats_count,
                    'Amount' => $payment->amount_cents,
                    'Tax' => config('services.tbank.tax'),
                    'PaymentMethod' => config('services.tbank.payment_method'),
                    'PaymentObject' => config('services.tbank.payment_object'),
                ]],
            ],
        ];
        $payload['Token'] = $this->tokens->make($payload, (string) config('services.tbank.password'));

        $response = Http::asJson()
            ->acceptJson()
            ->timeout((int) config('services.tbank.timeout', 10))
            ->post(rtrim((string) config('services.tbank.api_url'), '/').'/v2/Init', $payload)
            ->throw()
            ->json();

        if (($response['Success'] ?? false) !== true || blank($response['PaymentURL'] ?? null)) {
            throw new RuntimeException((string) ($response['Message'] ?? 'T-Bank payment initialization failed.'));
        }

        return new PaymentInitializationResult(
            providerPaymentId: (string) $response['PaymentId'],
            paymentUrl: (string) $response['PaymentURL'],
            requestPayload: $payload,
            responsePayload: $response,
        );
    }

    public function getState(Payment $payment): array
    {
        $payload = [
            'TerminalKey' => config('services.tbank.terminal_key'),
            'PaymentId' => $payment->provider_payment_id,
        ];
        $payload['Token'] = $this->tokens->make($payload, (string) config('services.tbank.password'));

        return Http::asJson()
            ->acceptJson()
            ->timeout((int) config('services.tbank.timeout', 10))
            ->post(rtrim((string) config('services.tbank.api_url'), '/').'/v2/GetState', $payload)
            ->throw()
            ->json();
    }
}
