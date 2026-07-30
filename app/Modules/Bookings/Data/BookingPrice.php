<?php

namespace App\Modules\Bookings\Data;

class BookingPrice
{
    public function __construct(
        public readonly int $adultPrepaymentCents,
        public readonly int $childPrepaymentCents,
        public readonly int $fullPriceCents,
        public readonly int $prepaymentCents,
        public readonly int $remainingCents,
    ) {}
}
