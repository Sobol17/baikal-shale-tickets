<?php

namespace App\Filament\Pages;

use App\Modules\Content\Actions\UpdateHomePageSettingsAction;
use App\Modules\Content\Models\HomePageSetting;
use App\Modules\Excursions\Data\ExcursionSeason;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class HomePageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Главная страница';

    protected static ?string $title = 'Главная страница';

    protected static ?string $slug = 'home-page';

    protected static ?int $navigationSort = 1;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->formState(HomePageSetting::current()));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Textarea::make('text')
                    ->label('Текст')
                    ->required()
                    ->rows(4)
                    ->maxLength(500)
                    ->columnSpanFull(),
                FileUpload::make('background_image')
                    ->label('Фоновое изображение')
                    ->helperText('Рекомендуемый размер — 1920 × 700 px. Поддерживаются JPEG, PNG и WebP до 5 МБ.')
                    ->image()
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->maxSize(5120)
                    ->disk('public')
                    ->directory('homepage/hero')
                    ->visibility('public')
                    ->imagePreviewHeight('240')
                    ->openable()
                    ->downloadable()
                    ->columnSpanFull(),
                Repeater::make('season_order')
                    ->label('Порядок подборок')
                    ->helperText('Перетащите, чтобы изменить порядок.')
                    ->simple(
                        Select::make('season')
                            ->label('Подборка')
                            ->options(ExcursionSeason::options())
                            ->required()
                            ->distinct()
                            ->selectablePlaceholder(false)
                            ->live()
                            ->afterStateUpdated($this->swapSeasonWithSibling(...)),
                    )
                    ->addable(false)
                    ->deletable(false)
                    ->reorderableWithDragAndDrop()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    EmbeddedSchema::make('form'),
                ])
                    ->id('home-page-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Сохранить')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    /**
     * Меняет подборки местами, когда в двух строках выбрана одна и та же:
     * соседу достаётся значение, которое было в текущей строке.
     */
    protected function swapSeasonWithSibling(Select $component, mixed $state, mixed $old, Set $set): void
    {
        $repeater = $component->getParentRepeater();

        if (blank($state) || blank($old) || ! $repeater instanceof Repeater) {
            return;
        }

        $currentItemKey = str($component->getStatePath())
            ->after($repeater->getStatePath().'.')
            ->before('.'.$component->getName())
            ->toString();

        foreach ($repeater->getRawState() ?? [] as $itemKey => $itemState) {
            if ($itemKey === $currentItemKey || data_get($itemState, $component->getName()) !== $state) {
                continue;
            }

            $set(
                path: $repeater->getStatePath().'.'.$itemKey.'.'.$component->getName(),
                state: $old,
                isAbsolute: true,
            );
        }
    }

    /**
     * @return array{title: string, text: string, background_image: ?string, season_order: array<int, string>}
     */
    protected function formState(HomePageSetting $settings): array
    {
        return [
            ...$settings->only([
                'title',
                'text',
                'background_image',
            ]),
            'season_order' => array_map(
                fn (ExcursionSeason $season): string => $season->value,
                $settings->orderedSeasons(),
            ),
        ];
    }

    public function save(UpdateHomePageSettingsAction $updateSettings): void
    {
        /** @var array{title: string, text: string, background_image: ?string, season_order: array<int, string>} $data */
        $data = $this->form->getState();
        $settings = $updateSettings->handle($data);

        $this->form->fill($this->formState($settings));

        Notification::make()
            ->success()
            ->title('Настройки главной страницы сохранены')
            ->send();
    }
}
