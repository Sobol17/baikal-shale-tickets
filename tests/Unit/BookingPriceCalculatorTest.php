<?php

use App\Modules\Bookings\Support\BookingPriceCalculator;
use App\Modules\Excursions\Models\Excursion;

it('calculates prepayment per ticket and rounds it to the nearest ruble', function (): void {
    $excursion = new Excursion([
        'adult_price_cents' => 100550,
        'child_price_cents' => 50450,
    ]);

    $price = app(BookingPriceCalculator::class)->calculate($excursion, 2, 1);

    expect($price->adultPrepaymentCents)->toBe(10100)
        ->and($price->childPrepaymentCents)->toBe(5000)
        ->and($price->fullPriceCents)->toBe(251550)
        ->and($price->prepaymentCents)->toBe(25200)
        ->and($price->remainingCents)->toBe(226350);
});
