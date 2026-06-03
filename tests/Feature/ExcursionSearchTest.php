<?php

use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;

it('returns active excursions for header search', function (): void {
    $matched = Excursion::factory()->create([
        'title' => 'Кругобайкальская железная дорога',
        'short_description' => 'Поездка вдоль Байкала',
        'base_price_cents' => 302000,
        'status' => ExcursionStatus::Active,
    ]);

    Excursion::factory()->create([
        'title' => 'Ледовая прогулка',
        'status' => ExcursionStatus::Active,
    ]);

    Excursion::factory()->create([
        'title' => 'Кругобайкальская закрытая экскурсия',
        'status' => ExcursionStatus::Draft,
    ]);

    $this->getJson(route('excursions.search', ['search' => 'Кругобайкальская']))
        ->assertOk()
        ->assertJsonCount(1, 'items')
        ->assertJsonPath('items.0.title', $matched->title)
        ->assertJsonPath('items.0.price', 'от 3 020 ₽')
        ->assertJsonPath('items.0.url', route('excursions.show', $matched));
});

it('searches excursions without case sensitivity', function (): void {
    $matched = Excursion::factory()->create([
        'title' => 'Baikal Sunset Cruise',
        'short_description' => 'Evening route',
        'status' => ExcursionStatus::Active,
    ]);

    $this->getJson(route('excursions.search', ['search' => 'baikal sunset']))
        ->assertOk()
        ->assertJsonCount(1, 'items')
        ->assertJsonPath('items.0.title', $matched->title);
});

it('returns an empty list for an empty search query', function (): void {
    Excursion::factory()->create();

    $this->getJson(route('excursions.search'))
        ->assertOk()
        ->assertExactJson([
            'items' => [],
        ]);
});
