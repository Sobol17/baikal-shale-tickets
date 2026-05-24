<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $modelLabel = 'бронирование';

    protected static ?string $pluralModelLabel = 'Бронирования';

    protected static ?string $recordTitleAttribute = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('uuid')->label('UUID')->disabled(),
            Select::make('status')->label('Статус')->options(BookingStatus::options())->required(),
            TextInput::make('customer_name')->label('Имя')->required()->maxLength(255),
            TextInput::make('customer_email')->label('Email')->email()->required()->maxLength(255),
            TextInput::make('customer_phone')->label('Телефон')->required()->maxLength(64),
            TextInput::make('seats_count')->label('Билеты')->numeric()->required()->minValue(1),
            TextInput::make('amount_cents')->label('Сумма, коп.')->numeric()->required()->minValue(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')->label('UUID')->searchable()->limit(8),
                TextColumn::make('slot.excursion.title')->label('Экскурсия')->searchable(),
                TextColumn::make('slot.starts_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('customer_name')->label('Клиент')->searchable(),
                TextColumn::make('customer_email')->label('Email')->searchable(),
                TextColumn::make('seats_count')->label('Билеты')->sortable(),
                TextColumn::make('amount_cents')->label('Сумма')->money('RUB', divideBy: 100)->sortable(),
                TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn (BookingStatus $state): string => $state->label()),
                TextColumn::make('created_at')->label('Создано')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(BookingStatus::options()),
            ])
            ->recordActions([
                Action::make('cancel')
                    ->label('Отменить')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record): bool => $record->status !== BookingStatus::Cancelled)
                    ->action(fn (Booking $record): bool => $record->update([
                        'status' => BookingStatus::Cancelled,
                        'cancelled_at' => now(),
                    ])),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
