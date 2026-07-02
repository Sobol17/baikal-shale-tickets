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
            ->reject(fn (mixed $value, string $key): bool => $key === 'Token' || $value === null || is_array($value) || is_object($value))
            ->map(fn (mixed $value): string => $this->normalizeValue($value))
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

    private function normalizeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
