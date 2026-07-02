<?php

use App\Modules\Analytics\Actions\GetDashboardMetricsAction;
use App\Modules\Analytics\Data\DashboardDateRange;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Payments\Data\PaymentStatus;
use App\Modules\Payments\Models\Payment;

it('calculates dashboard metrics with date filters', function (): void {
    $activeExcursion = Excursion::factory()->create(['status' => ExcursionStatus::Active]);
    Excursion::factory()->create(['status' => ExcursionStatus::Draft]);

    $bookingInPeriod = Booking::factory()->create([
        'excursion_id' => $activeExcursion->id,
        'created_at' => '2026-01-15 10:00:00',
        'updated_at' => '2026-01-15 10:00:00',
    ]);
    $anotherBookingInPeriod = Booking::factory()->create([
        'excursion_id' => $activeExcursion->id,
        'created_at' => '2026-01-20 23:30:00',
        'updated_at' => '2026-01-20 23:30:00',
    ]);
    $bookingOutsidePeriod = Booking::factory()->create([
        'excursion_id' => $activeExcursion->id,
        'created_at' => '2026-02-01 10:00:00',
        'updated_at' => '2026-02-01 10:00:00',
    ]);

    Payment::factory()->create([
        'booking_id' => $bookingInPeriod->id,
        'amount_cents' => 150000,
        'status' => PaymentStatus::Paid,
        'paid_at' => '2026-01-15 12:00:00',
    ]);
    Payment::factory()->create([
        'booking_id' => $anotherBookingInPeriod->id,
        'amount_cents' => 250000,
        'status' => PaymentStatus::Failed,
        'paid_at' => '2026-01-16 12:00:00',
    ]);
    Payment::factory()->create([
        'booking_id' => $bookingOutsidePeriod->id,
        'amount_cents' => 500000,
        'status' => PaymentStatus::Paid,
        'paid_at' => '2026-02-01 12:00:00',
    ]);

    $metrics = app(GetDashboardMetricsAction::class)->handle(
        DashboardDateRange::fromFilters([
            'date_from' => '2026-01-10',
            'date_to' => '2026-01-20',
        ]),
    );

    expect($metrics->paidPaymentsAmountCents)->toBe(150000);
    expect($metrics->paidPaymentsCount)->toBe(1);
    expect($metrics->activeExcursionsCount)->toBe(1);
    expect($metrics->bookingsCount)->toBe(2);
});
