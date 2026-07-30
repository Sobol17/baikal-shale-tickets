<?php

use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Models\Excursion;
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

    $excursion = Excursion::factory()->create([
        'adult_price_cents' => 100550,
        'child_price_cents' => 50450,
    ]);
    $booking = Booking::factory()->create([
        'excursion_id' => $excursion,
        'adult_tickets_count' => 2,
        'child_tickets_count' => 1,
        'seats_count' => 3,
        'amount_cents' => 25200,
    ]);
    $payment = app(CreatePaymentAction::class)->handle($booking);

    expect($payment->provider)->toBe('tbank');
    expect($payment->provider_payment_id)->toBe('123456');
    expect($payment->payment_url)->toBe('https://securepay.tinkoff.ru/payment/123456');

    Http::assertSent(function (Request $request) use ($booking, $payment): bool {
        return $request->url() === 'https://securepay.tinkoff.ru/v2/Init'
            && $request['TerminalKey'] === 'terminal'
            && $request['Amount'] === $booking->amount_cents
            && $request['Receipt']['Items'][0]['Price'] === 10100
            && $request['Receipt']['Items'][0]['Quantity'] === 2
            && $request['Receipt']['Items'][0]['Amount'] === 20200
            && $request['Receipt']['Items'][1]['Price'] === 5000
            && $request['Receipt']['Items'][1]['Quantity'] === 1
            && $request['Receipt']['Items'][1]['Amount'] === 5000
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
