<?php

namespace App\Modules\Analytics\Actions;

use App\Modules\Analytics\Data\DashboardDateRange;
use App\Modules\Analytics\Data\DashboardMetrics;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Payments\Data\PaymentStatus;
use App\Modules\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class GetDashboardMetricsAction
{
    public function handle(DashboardDateRange $dateRange): DashboardMetrics
    {
        $paidPayments = Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->tap(fn (Builder $query): Builder => $this->applyDateRange($query, 'paid_at', $dateRange));

        $bookings = Booking::query()
            ->tap(fn (Builder $query): Builder => $this->applyDateRange($query, 'created_at', $dateRange));

        return new DashboardMetrics(
            paidPaymentsAmountCents: (int) $paidPayments->sum('amount_cents'),
            paidPaymentsCount: (int) $paidPayments->count(),
            activeExcursionsCount: Excursion::query()->active()->count(),
            bookingsCount: (int) $bookings->count(),
        );
    }

    /**
     * @param  Builder<*>  $query
     * @return Builder<*>
     */
    private function applyDateRange(Builder $query, string $column, DashboardDateRange $dateRange): Builder
    {
        if ($dateRange->dateFrom !== null) {
            $query->where($column, '>=', $dateRange->dateFrom);
        }

        if ($dateRange->dateTo !== null) {
            $query->where($column, '<=', $dateRange->dateTo);
        }

        return $query;
    }
}
