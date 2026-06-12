<?php

namespace App\Filament\Resources\Excursions;

use App\Filament\Resources\Excursions\Pages\CreateExcursion;
use App\Filament\Resources\Excursions\Pages\EditExcursion;
use App\Filament\Resources\Excursions\Pages\ListExcursions;
use App\Modules\Excursions\Data\ExcursionSeason;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExcursionResource extends Resource
{
    protected static ?string $model = Excursion::class;

    protected static ?string $modelLabel = 'экскурсия';

    protected static ?string $pluralModelLabel = 'Экскурсии';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Название')->required()->maxLength(255),
            TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            Select::make('season')->label('Сезон')->options(ExcursionSeason::options())->required(),
            Select::make('status')->label('Статус')->options(ExcursionStatus::options())->required(),
            TextInput::make('duration_minutes')->label('Длительность, минут')->numeric()->required()->minValue(1),
            TextInput::make('adult_price_cents')
                ->label('Взрослая цена, ₽')
                ->numeric()
                ->required()
                ->minValue(1)
                ->suffix('₽')
                ->formatStateUsing(fn (?int $state): ?string => $state === null ? null : (string) ($state / 100))
                ->dehydrateStateUsing(fn (mixed $state): int => (int) round(((float) str_replace(',', '.', (string) $state)) * 100)),
            TextInput::make('child_price_cents')
                ->label('Детская цена, ₽')
                ->numeric()
                ->required()
                ->minValue(1)
                ->suffix('₽')
                ->formatStateUsing(fn (?int $state): ?string => $state === null ? null : (string) ($state / 100))
                ->dehydrateStateUsing(fn (mixed $state): int => (int) round(((float) str_replace(',', '.', (string) $state)) * 100)),
            DatePicker::make('booking_starts_on')->label('Бронирование с')->required()->native(false),
            DatePicker::make('booking_ends_on')->label('Бронирование по')->required()->native(false),
            TextInput::make('meeting_point')->label('Место встречи')->required()->maxLength(255),
            FileUpload::make('cover_image')
                ->label('Обложка')
                ->helperText('Загрузите изображение в пропорции 16:9. Подойдут размеры 1280x720, 1600x900, 1920x1080. Максимальный размер файла: 4 МБ.')
                ->image()
                ->disk('public')
                ->directory('excursions/covers')
                ->visibility('public')
                ->imagePreviewHeight('180')
                ->imageAspectRatio('16:9')
                ->maxSize(4096)
                ->openable()
                ->downloadable()
                ->columnSpanFull(),
            Textarea::make('short_description')->label('Краткое описание')->required()->rows(3)->maxLength(500),
            RichEditor::make('full_description')->label('Полное описание')->required()->columnSpanFull(),
            Textarea::make('rules')->label('Организационная информация')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Название')->searchable()->sortable(),
                TextColumn::make('season')->label('Сезон')->badge()->formatStateUsing(fn (ExcursionSeason $state): string => $state->label()),
                TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn (ExcursionStatus $state): string => $state->label()),
                TextColumn::make('adult_price_cents')->label('Взрослая цена')->money('RUB', divideBy: 100)->sortable(),
                TextColumn::make('child_price_cents')->label('Детская цена')->money('RUB', divideBy: 100)->sortable(),
                TextColumn::make('booking_starts_on')->label('Даты с')->date('d.m.Y')->sortable(),
                TextColumn::make('booking_ends_on')->label('Даты по')->date('d.m.Y')->sortable(),
                TextColumn::make('duration_minutes')->label('Мин.')->sortable(),
                TextColumn::make('updated_at')->label('Обновлено')->dateTime('d.m.Y H:i')->sortable(),
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
            'index' => ListExcursions::route('/'),
            'create' => CreateExcursion::route('/create'),
            'edit' => EditExcursion::route('/{record}/edit'),
        ];
    }
}
