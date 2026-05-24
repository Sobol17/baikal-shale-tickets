<?php

use App\Modules\Payments\Support\TBankToken;

it('builds and verifies tbank token ignoring nested values and token field', function (): void {
    $tokens = new TBankToken;
    $payload = [
        'TerminalKey' => 'terminal',
        'Amount' => 10000,
        'OrderId' => 'order-1',
        'Receipt' => ['Items' => []],
    ];

    $payload['Token'] = $tokens->make($payload, 'password');

    expect($tokens->verify($payload, 'password'))->toBeTrue();
    expect($tokens->verify($payload, 'wrong-password'))->toBeFalse();
});
