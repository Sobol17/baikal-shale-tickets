<?php

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Notifications\Mail\BookingPaidMail;
use App\Modules\Payments\Data\PaymentStatus;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Support\TBankToken;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

it('confirms payment from a valid tbank notification only once', function (): void {
    Mail::fake();
    Config::set('services.tbank.password', 'secret');
    Config::set('services.tbank.terminal_key', 'terminal');

    $booking = Booking::factory()->create();
    $payment = Payment::factory()->create([
        'booking_id' => $booking->id,
        'provider' => 'tbank',
        'provider_payment_id' => '123456',
        'amount_cents' => $booking->amount_cents,
    ]);
    $payload = [
        'TerminalKey' => 'terminal',
        'OrderId' => $booking->uuid,
        'PaymentId' => '123456',
        'Amount' => (string) $payment->amount_cents,
        'Status' => 'CONFIRMED',
        'Success' => true,
    ];
    $payload['Token'] = app(TBankToken::class)->make($payload, 'secret');

    $this->post(route('payments.tbank.notifications'), $payload)->assertOk();
    $this->post(route('payments.tbank.notifications'), $payload)->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Paid);
    expect($booking->refresh()->status)->toBe(BookingStatus::Paid);

    Mail::assertSent(BookingPaidMail::class, 1);
});

it('rejects tbank notification with invalid token', function (): void {
    Config::set('services.tbank.password', 'secret');

    $this->post(route('payments.tbank.notifications'), [
        'PaymentId' => '123456',
        'Status' => 'CONFIRMED',
        'Token' => 'invalid',
    ])->assertForbidden();
});

it('confirms payment from any successful tbank notification', function (): void {
    Mail::fake();
    Config::set('services.tbank.password', 'secret');
    Config::set('services.tbank.terminal_key', 'terminal');

    $booking = Booking::factory()->create();
    $payment = Payment::factory()->create([
        'booking_id' => $booking->id,
        'provider' => 'tbank',
        'provider_payment_id' => '123456',
        'amount_cents' => $booking->amount_cents,
    ]);
    $payload = [
        'TerminalKey' => 'terminal',
        'OrderId' => $booking->uuid,
        'PaymentId' => '123456',
        'Amount' => (string) $payment->amount_cents,
        'Status' => 'AUTHORIZED',
        'Success' => 'true',
    ];
    $payload['Token'] = app(TBankToken::class)->make($payload, 'secret');

    $this->post(route('payments.tbank.notifications'), $payload)->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Paid);
    expect($booking->refresh()->status)->toBe(BookingStatus::Paid);

    Mail::assertSent(BookingPaidMail::class, 1);
});

it('rejects tbank notification for another order', function (): void {
    Config::set('services.tbank.password', 'secret');
    Config::set('services.tbank.terminal_key', 'terminal');

    $booking = Booking::factory()->create();
    $payment = Payment::factory()->create([
        'booking_id' => $booking->id,
        'provider' => 'tbank',
        'provider_payment_id' => '123456',
        'amount_cents' => $booking->amount_cents,
    ]);
    $payload = [
        'TerminalKey' => 'terminal',
        'OrderId' => 'another-order',
        'PaymentId' => '123456',
        'Amount' => (string) $payment->amount_cents,
        'Status' => 'CONFIRMED',
        'Success' => true,
    ];
    $payload['Token'] = app(TBankToken::class)->make($payload, 'secret');

    $this->post(route('payments.tbank.notifications'), $payload)->assertUnprocessable();

    expect($booking->refresh()->status)->toBe(BookingStatus::PendingPayment);
});

it('rejects tbank notification with mismatched amount', function (): void {
    Config::set('services.tbank.password', 'secret');
    Config::set('services.tbank.terminal_key', 'terminal');

    $booking = Booking::factory()->create();
    $payment = Payment::factory()->create([
        'booking_id' => $booking->id,
        'provider' => 'tbank',
        'provider_payment_id' => '123456',
        'amount_cents' => $booking->amount_cents,
    ]);
    $payload = [
        'TerminalKey' => 'terminal',
        'OrderId' => $booking->uuid,
        'PaymentId' => '123456',
        'Amount' => (string) ($payment->amount_cents + 1),
        'Status' => 'CONFIRMED',
        'Success' => true,
    ];
    $payload['Token'] = app(TBankToken::class)->make($payload, 'secret');

    $this->post(route('payments.tbank.notifications'), $payload)->assertUnprocessable();

    expect($booking->refresh()->status)->toBe(BookingStatus::PendingPayment);
});
