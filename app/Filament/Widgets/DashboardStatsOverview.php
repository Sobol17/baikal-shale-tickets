<?php

namespace App\Filament\Widgets;

use App\Modules\Analytics\Actions\GetDashboardMetricsAction;
use App\Modules\Analytics\Data\DashboardDateRange;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Статистика';

    protected int|array|null $columns = 4;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $metrics = app(GetDashboardMetricsAction::class)->handle(
            DashboardDateRange::fromFilters($this->pageFilters ?? []),
        );

        return [
            Stat::make('Сумма платежей', $this->formatMoney($metrics->paidPaymentsAmountCents))
                ->description('Оплаченные платежи за период')
                ->color('success'),
            Stat::make('Кол-во платежей', number_format($metrics->paidPaymentsCount, 0, ',', ' '))
                ->description('Оплаченные платежи за период')
                ->color('info'),
            Stat::make('Активные экскурсии', number_format($metrics->activeExcursionsCount, 0, ',', ' '))
                ->description('Текущие активные экскурсии')
                ->color('warning'),
            Stat::make('Бронирования', number_format($metrics->bookingsCount, 0, ',', ' '))
                ->description('Созданы за период')
                ->color('gray'),
        ];
    }

    private function formatMoney(int $amountCents): string
    {
        return number_format($amountCents / 100, 0, ',', ' ').' ₽';
    }
}
