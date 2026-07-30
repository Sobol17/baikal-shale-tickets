<?php

namespace App\Filament\Pages;

use App\Modules\Content\Actions\UpdateHomePageSettingsAction;
use App\Modules\Content\Models\HomePageSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
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
        $this->form->fill(HomePageSetting::current()->only([
            'title',
            'text',
            'background_image',
        ]));
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

    public function save(UpdateHomePageSettingsAction $updateSettings): void
    {
        /** @var array{title: string, text: string, background_image: ?string} $data */
        $data = $this->form->getState();
        $settings = $updateSettings->handle($data);

        $this->form->fill($settings->only([
            'title',
            'text',
            'background_image',
        ]));

        Notification::make()
            ->success()
            ->title('Настройки главной страницы сохранены')
            ->send();
    }
}
