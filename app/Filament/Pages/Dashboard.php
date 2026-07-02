<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsOverview;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Панель управления';

    protected static ?string $navigationLabel = 'Панель управления';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date_from')
                ->label('Дата с')
                ->native(false),
            DatePicker::make('date_to')
                ->label('Дата по')
                ->native(false),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
        ];
    }
}
