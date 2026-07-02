<?php

namespace App\Modules\Analytics\Data;

class DashboardMetrics
{
    public function __construct(
        public readonly int $paidPaymentsAmountCents,
        public readonly int $paidPaymentsCount,
        public readonly int $activeExcursionsCount,
        public readonly int $bookingsCount,
    ) {}
}
