<?php

use App\Filament\Pages\HomePageSettings;
use App\Models\User;
use App\Modules\Content\Actions\UpdateHomePageSettingsAction;
use App\Modules\Content\Models\HomePageSetting;
use App\Modules\Excursions\Data\ExcursionSeason;
use App\Modules\Excursions\Models\Excursion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * Repeater в simple-режиме хранит состояние формы как список элементов,
 * поэтому в тестах порядок подборок заполняется в «сыром» виде.
 *
 * @param  array<int, string>  $seasons
 * @return array<int, array<string, string>>
 */
function seasonOrderFormState(array $seasons): array
{
    return array_map(fn (string $season): array => ['season' => $season], $seasons);
}

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

it('renders season sections in the configured order', function (): void {
    Excursion::factory()->create(['season' => ExcursionSeason::SummerAutumn]);
    Excursion::factory()->create(['season' => ExcursionSeason::WinterSpring]);

    $settings = HomePageSetting::query()->findOrFail(HomePageSetting::SINGLETON_ID);

    $settings->update([
        'season_order' => [
            ExcursionSeason::WinterSpring->value,
            ExcursionSeason::SummerAutumn->value,
        ],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            ExcursionSeason::WinterSpring->label(),
            ExcursionSeason::SummerAutumn->label(),
        ]);

    $settings->update([
        'season_order' => [
            ExcursionSeason::SummerAutumn->value,
            ExcursionSeason::WinterSpring->value,
        ],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            ExcursionSeason::SummerAutumn->label(),
            ExcursionSeason::WinterSpring->label(),
        ]);
});

it('allows an admin to reorder the collections', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $reversedOrder = array_reverse(HomePageSetting::defaultSeasonOrder());

    Livewire::actingAs($admin)
        ->test(HomePageSettings::class)
        ->fillForm(['season_order' => seasonOrderFormState($reversedOrder)])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(HomePageSetting::query()->findOrFail(HomePageSetting::SINGLETON_ID)->season_order)
        ->toBe($reversedOrder);

    // Форма подхватывает сохраненный порядок при открытии страницы.
    Livewire::actingAs($admin)
        ->test(HomePageSettings::class)
        ->call('save')
        ->assertHasNoFormErrors();

    expect(HomePageSetting::query()->findOrFail(HomePageSetting::SINGLETON_ID)->season_order)
        ->toBe($reversedOrder);
});

it('normalizes an incomplete or broken collection order', function (): void {
    $settings = HomePageSetting::query()->findOrFail(HomePageSetting::SINGLETON_ID);

    $settings->season_order = null;
    expect($settings->orderedSeasons())->toBe(ExcursionSeason::cases());

    $settings->season_order = [
        'autumn_of_the_patriarch',
        ExcursionSeason::WinterSpring->value,
        ExcursionSeason::WinterSpring->value,
    ];

    expect($settings->orderedSeasons())->toBe([
        ExcursionSeason::WinterSpring,
        ExcursionSeason::SummerAutumn,
    ]);

    app(UpdateHomePageSettingsAction::class)->handle([
        'title' => $settings->title,
        'text' => $settings->text,
        'background_image' => null,
        'season_order' => ['autumn_of_the_patriarch', ExcursionSeason::WinterSpring->value],
    ]);

    expect(HomePageSetting::query()->findOrFail(HomePageSetting::SINGLETON_ID)->season_order)
        ->toBe([
            ExcursionSeason::WinterSpring->value,
            ExcursionSeason::SummerAutumn->value,
        ]);
});

it('swaps the collections when the same one is picked twice', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $component = Livewire::actingAs($admin)->test(HomePageSettings::class);

    $firstItemKey = array_key_first($component->get('data.season_order'));

    // В первой строке выбираем подборку, которая уже стоит во второй.
    $component
        ->set("data.season_order.{$firstItemKey}.season", ExcursionSeason::WinterSpring->value)
        ->call('save')
        ->assertHasNoFormErrors();

    expect(HomePageSetting::query()->findOrFail(HomePageSetting::SINGLETON_ID)->season_order)
        ->toBe([
            ExcursionSeason::WinterSpring->value,
            ExcursionSeason::SummerAutumn->value,
        ]);
});
