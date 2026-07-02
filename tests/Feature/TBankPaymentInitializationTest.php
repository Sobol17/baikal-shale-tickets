<?php

use App\Modules\Bookings\Models\Booking;
use App\Modules\Payments\Actions\CreatePaymentAction;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('initializes tbank payment with booking aware result urls', function (): void {
    Config::set('services.payments.default', 'tbank');
    Config::set('services.tbank.api_url', 'https://securepay.tinkoff.ru');
    Config::set('services.tbank.terminal_key', 'terminal');
    Config::set('services.tbank.password', 'secret');
    Config::set('services.tbank.notification_url', 'https://example.com/payments/tbank/notifications');

    Http::fake([
        'securepay.tinkoff.ru/v2/Init' => Http::response([
            'Success' => true,
            'PaymentId' => '123456',
            'PaymentURL' => 'https://securepay.tinkoff.ru/payment/123456',
        ]),
    ]);

    $booking = Booking::factory()->create();
    $payment = app(CreatePaymentAction::class)->handle($booking);

    expect($payment->provider)->toBe('tbank');
    expect($payment->provider_payment_id)->toBe('123456');
    expect($payment->payment_url)->toBe('https://securepay.tinkoff.ru/payment/123456');

    Http::assertSent(function (Request $request) use ($booking, $payment): bool {
        return $request->url() === 'https://securepay.tinkoff.ru/v2/Init'
            && $request['TerminalKey'] === 'terminal'
            && $request['Amount'] === $booking->amount_cents
            && $request['OrderId'] === $booking->uuid
            && $request['OperationInitiatorType'] === '0'
            && $request['SuccessURL'] === route('payment.success', ['booking' => $booking->uuid])
            && $request['FailURL'] === route('payment.fail', ['booking' => $booking->uuid])
            && $request['NotificationURL'] === 'https://example.com/payments/tbank/notifications'
            && $request['DATA']['booking_uuid'] === $booking->uuid
            && $request['DATA']['payment_id'] === (string) $payment->id
            && filled($request['Token']);
    });
});
