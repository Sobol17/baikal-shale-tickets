<?php

namespace App\Modules\Bookings\Support;

use App\Modules\Bookings\Data\BookingPrice;
use App\Modules\Excursions\Models\Excursion;

class BookingPriceCalculator
{
    public function calculate(
        Excursion $excursion,
        int $adultTicketsCount,
        int $childTicketsCount,
    ): BookingPrice {
        $adultPrepaymentCents = $this->prepaymentForTicket($excursion->adult_price_cents);
        $childPrepaymentCents = $this->prepaymentForTicket($excursion->child_price_cents);
        $fullPriceCents = ($excursion->adult_price_cents * $adultTicketsCount)
            + ($excursion->child_price_cents * $childTicketsCount);
        $prepaymentCents = ($adultPrepaymentCents * $adultTicketsCount)
            + ($childPrepaymentCents * $childTicketsCount);

        return new BookingPrice(
            adultPrepaymentCents: $adultPrepaymentCents,
            childPrepaymentCents: $childPrepaymentCents,
            fullPriceCents: $fullPriceCents,
            prepaymentCents: $prepaymentCents,
            remainingCents: $fullPriceCents - $prepaymentCents,
        );
    }

    private function prepaymentForTicket(int $fullPriceCents): int
    {
        return intdiv($fullPriceCents + 500, 1000) * 100;
    }
}
