<?php

namespace App\Modules\Analytics\Data;

use Carbon\CarbonImmutable;

class DashboardDateRange
{
    public function __construct(
        public readonly ?CarbonImmutable $dateFrom,
        public readonly ?CarbonImmutable $dateTo,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function fromFilters(array $filters): self
    {
        return new self(
            dateFrom: self::parseDate($filters['date_from'] ?? null)?->startOfDay(),
            dateTo: self::parseDate($filters['date_to'] ?? null)?->endOfDay(),
        );
    }

    private static function parseDate(mixed $value): ?CarbonImmutable
    {
        if (blank($value)) {
            return null;
        }

        return CarbonImmutable::parse((string) $value);
    }
}
