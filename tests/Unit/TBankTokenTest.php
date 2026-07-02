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

it('normalizes notification boolean values like tbank expects', function (): void {
    $tokens = new TBankToken;
    $payload = [
        'TerminalKey' => '1234567890DEMO',
        'OrderId' => '000000',
        'Success' => true,
        'Status' => 'AUTHORIZED',
        'PaymentId' => '0000000',
        'ErrorCode' => '0',
        'Amount' => '1111',
        'CardId' => '000000',
        'Pan' => '200000******0000',
        'ExpDate' => '1111',
        'RebillId' => '000000',
        'Data' => ['ignored' => 'value'],
        'Receipt' => ['ignored' => 'value'],
        'Nullable' => null,
    ];

    expect($tokens->make($payload, '11111111111'))
        ->toBe('1c0964277d0213349243065a0d5b838b8e90d2d25f740d0f2767836e710e80c8');

    $payload['Success'] = 'true';

    expect($tokens->make($payload, '11111111111'))
        ->toBe('1c0964277d0213349243065a0d5b838b8e90d2d25f740d0f2767836e710e80c8');
});
