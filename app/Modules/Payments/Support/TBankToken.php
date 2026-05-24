<?php

namespace App\Modules\Payments\Support;

class TBankToken
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function make(array $payload, string $password): string
    {
        $values = collect($payload)
            ->reject(fn (mixed $value, string $key): bool => $key === 'Token' || is_array($value) || is_object($value))
            ->put('Password', $password)
            ->sortKeys()
            ->values()
            ->implode('');

        return hash('sha256', $values);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verify(array $payload, string $password): bool
    {
        $token = (string) ($payload['Token'] ?? '');

        return $token !== '' && hash_equals($token, $this->make($payload, $password));
    }
}
