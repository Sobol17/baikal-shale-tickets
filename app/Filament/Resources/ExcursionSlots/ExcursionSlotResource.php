<?php

namespace App\Filament\Resources\ExcursionSlots;

use App\Filament\Resources\ExcursionSlots\Pages\CreateExcursionSlot;
use App\Filament\Resources\ExcursionSlots\Pages\EditExcursionSlot;
use App\Filament\Resources\ExcursionSlots\Pages\ListExcursionSlots;
use App\Modules\Excursions\Data\ExcursionSlotStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Excursions\Models\ExcursionSlot;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExcursionSlotResource extends Resource
{
    protected static ?string $model = ExcursionSlot::class;

    protected static ?string $modelLabel = 'слот';

    protected static ?string $pluralModelLabel = 'Слоты';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('excursion_id')
                ->label('Экскурсия')
                ->options(fn (): array => Excursion::query()->orderBy('title')->pluck('title', 'id')->all())
                ->searchable()
                ->required(),
            DateTimePicker::make('starts_at')->label('Начало')->seconds(false)->required(),
            DateTimePicker::make('ends_at')->label('Окончание')->seconds(false)->required(),
            TextInput::make('capacity')->label('Техническая вместимость')->numeric()->required()->minValue(1),
            TextInput::make('price_cents')->label('Цена, коп.')->numeric()->required()->minValue(1),
            Select::make('status')
                ->label('Статус')
                ->options(ExcursionSlotStatus::options())
                ->default(ExcursionSlotStatus::Active->value)
                ->helperText('На сайте отображаются только активные будущие отправки.')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('excursion.title')->label('Экскурсия')->searchable()->sortable(),
                TextColumn::make('starts_at')->label('Начало')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('capacity')->label('Тех. вместимость')->sortable(),
                TextColumn::make('price_cents')->label('Цена')->money('RUB', divideBy: 100)->sortable(),
                TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn (ExcursionSlotStatus $state): string => $state->label()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExcursionSlots::route('/'),
            'create' => CreateExcursionSlot::route('/create'),
            'edit' => EditExcursionSlot::route('/{record}/edit'),
        ];
    }
}
