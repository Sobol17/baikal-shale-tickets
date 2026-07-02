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
        $payment->loadMissing('booking.excursion');
        $booking = $payment->booking;
        $excursion = $booking->excursion;
        $items = [];

        if ($booking->adult_tickets_count > 0) {
            $items[] = [
                'Name' => str($excursion->title.' - взрослый билет')->limit(128, '')->toString(),
                'Price' => $excursion->adult_price_cents,
                'Quantity' => $booking->adult_tickets_count,
                'Amount' => $excursion->adult_price_cents * $booking->adult_tickets_count,
                'Tax' => config('services.tbank.tax'),
                'PaymentMethod' => config('services.tbank.payment_method'),
                'PaymentObject' => config('services.tbank.payment_object'),
            ];
        }

        if ($booking->child_tickets_count > 0) {
            $items[] = [
                'Name' => str($excursion->title.' - детский билет')->limit(128, '')->toString(),
                'Price' => $excursion->child_price_cents,
                'Quantity' => $booking->child_tickets_count,
                'Amount' => $excursion->child_price_cents * $booking->child_tickets_count,
                'Tax' => config('services.tbank.tax'),
                'PaymentMethod' => config('services.tbank.payment_method'),
                'PaymentObject' => config('services.tbank.payment_object'),
            ];
        }

        $payload = [
            'TerminalKey' => config('services.tbank.terminal_key'),
            'Amount' => $payment->amount_cents,
            'OrderId' => $booking->uuid,
            'Description' => str($excursion->title)->limit(140, '')->toString(),
            'PayType' => 'O',
            'Language' => 'ru',
            'OperationInitiatorType' => '0',
            'NotificationURL' => config('services.tbank.notification_url'),
            'SuccessURL' => route('payment.success', ['booking' => $booking->uuid]),
            'FailURL' => route('payment.fail', ['booking' => $booking->uuid]),
            'DATA' => [
                'booking_uuid' => $booking->uuid,
                'payment_id' => (string) $payment->id,
            ],
            'Receipt' => [
                'Email' => $booking->customer_email,
                'Phone' => $booking->customer_phone,
                'Taxation' => config('services.tbank.taxation'),
                'Items' => $items,
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
}
