<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Modules\Payments\Actions\SyncPaymentStateAction;
use App\Modules\Payments\Data\PaymentStatus;
use App\Modules\Payments\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $modelLabel = 'платеж';

    protected static ?string $pluralModelLabel = 'Платежи';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('booking.uuid')->label('Бронь')->searchable()->limit(8),
                TextColumn::make('provider')->label('Провайдер')->sortable(),
                TextColumn::make('provider_payment_id')->label('Provider ID')->searchable(),
                TextColumn::make('amount_cents')->label('Сумма')->money('RUB', divideBy: 100)->sortable(),
                TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn (PaymentStatus $state): string => $state->label()),
                TextColumn::make('created_at')->label('Создано')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(PaymentStatus::options()),
                SelectFilter::make('provider')->label('Провайдер')->options([
                    'fake' => 'Fake',
                    'tbank' => 'T-Bank',
                ]),
            ])
            ->recordActions([
                Action::make('sync')
                    ->label('Сверить')
                    ->requiresConfirmation()
                    ->action(fn (Payment $record, SyncPaymentStateAction $sync): Payment => $sync->handle($record)),
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
        ];
    }
}
