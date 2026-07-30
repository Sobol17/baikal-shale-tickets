<?php

use App\Filament\Pages\HomePageSettings;
use App\Models\User;
use App\Modules\Content\Actions\UpdateHomePageSettingsAction;
use App\Modules\Content\Models\HomePageSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders the configured hero content and seo metadata on the homepage', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('homepage/hero/baikal.webp', 'hero-image');

    HomePageSetting::query()
        ->findOrFail(HomePageSetting::SINGLETON_ID)
        ->update([
            'title' => 'Летние экскурсии по Байкалу',
            'text' => 'Выберите маршрут и забронируйте поездку на подходящую дату.',
            'background_image' => 'homepage/hero/baikal.webp',
        ]);

    $imageUrl = url(Storage::disk('public')->url('homepage/hero/baikal.webp'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<title>Летние экскурсии по Байкалу | Байкал Шале</title>', false)
        ->assertSee('<meta name="description" content="Выберите маршрут и забронируйте поездку на подходящую дату.">', false)
        ->assertSee('<meta property="og:image" content="'.$imageUrl.'">', false)
        ->assertSee('<h1 class="page-title">Летние экскурсии по Байкалу</h1>', false)
        ->assertSee('Выберите маршрут и забронируйте поездку на подходящую дату.')
        ->assertSee('src="'.Storage::disk('public')->url('homepage/hero/baikal.webp').'"', false)
        ->assertSee('"name":"Летние экскурсии по Байкалу"', false);
});

it('shows the default hero without an image', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(HomePageSetting::DEFAULT_TITLE)
        ->assertSee(HomePageSetting::DEFAULT_TEXT)
        ->assertDontSee('catalog-intro__background');
});

it('allows an admin to update hero settings and removes a replaced image', function (): void {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(HomePageSettings::class)
        ->fillForm([
            'title' => 'Новый заголовок',
            'text' => 'Новый текст главного баннера.',
            'background_image' => UploadedFile::fake()->image('hero.jpg', 1920, 700),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = HomePageSetting::query()->findOrFail(HomePageSetting::SINGLETON_ID);

    expect($settings->title)->toBe('Новый заголовок')
        ->and($settings->text)->toBe('Новый текст главного баннера.')
        ->and($settings->background_image)->not->toBeNull();

    Storage::disk('public')->assertExists($settings->background_image);

    $uploadedImage = $settings->background_image;
    Storage::disk('public')->put('homepage/hero/replacement.webp', 'replacement-image');

    app(UpdateHomePageSettingsAction::class)->handle([
        'title' => $settings->title,
        'text' => $settings->text,
        'background_image' => 'homepage/hero/replacement.webp',
    ]);

    Storage::disk('public')->assertMissing($uploadedImage);
    Storage::disk('public')->assertExists('homepage/hero/replacement.webp');
});

it('validates hero settings and denies the page to non admins', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    Livewire::actingAs($admin)
        ->test(HomePageSettings::class)
        ->fillForm([
            'title' => '',
            'text' => str_repeat('а', 501),
            'background_image' => UploadedFile::fake()->create(
                'hero.svg',
                10,
                'image/svg+xml',
            ),
        ])
        ->call('save')
        ->assertHasFormErrors([
            'title' => 'required',
            'text' => 'max',
            'background_image',
        ]);

    $this->actingAs($user)
        ->get('/admin/home-page')
        ->assertForbidden();
});
