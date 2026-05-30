<?php

use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionSlotStatus;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Excursions\Models\ExcursionSlot;

it('renders homepage seo metadata and structured data', function (): void {
    $activeExcursion = Excursion::factory()->create([
        'title' => 'Кругобайкальская железная дорога',
        'short_description' => 'Экскурсия вдоль Байкала с остановками у старых тоннелей.',
        'status' => ExcursionStatus::Active,
    ]);

    Excursion::factory()->create([
        'title' => 'Черновик экскурсии',
        'status' => ExcursionStatus::Draft,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<title>Экскурсии Байкал Шале</title>', false)
        ->assertSee('<meta name="description" content="Экскурсии Байкал Шале: маршруты по Байкалу, ближайшие отправки и бронирование билетов без регистрации.">', false)
        ->assertSee('<link rel="canonical" href="'.route('home', absolute: true).'">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('"@type":"ItemList"', false)
        ->assertSee($activeExcursion->title)
        ->assertDontSee('Черновик экскурсии');
});

it('renders excursion seo metadata and tourist trip structured data', function (): void {
    $excursion = Excursion::factory()->create([
        'title' => 'Листвянка - Большие Коты',
        'short_description' => 'Водная экскурсия по Байкалу из Листвянки до Больших Котов.',
        'duration_minutes' => 120,
        'meeting_point' => 'Причал Листвянка',
        'base_price_cents' => 150000,
        'status' => ExcursionStatus::Active,
    ]);
    ExcursionSlot::factory()->create([
        'excursion_id' => $excursion->id,
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(12, 0),
        'price_cents' => 110000,
        'status' => ExcursionSlotStatus::Active,
    ]);

    $this->get(route('excursions.show', $excursion))
        ->assertOk()
        ->assertSee('<title>Листвянка - Большие Коты | Байкал Шале</title>', false)
        ->assertSee('<meta name="description" content="Водная экскурсия по Байкалу из Листвянки до Больших Котов.">', false)
        ->assertSee('<link rel="canonical" href="'.route('excursions.show', $excursion, absolute: true).'">', false)
        ->assertSee('<meta property="og:type" content="product">', false)
        ->assertSee('"@type":"TouristTrip"', false)
        ->assertSee('"@type":"Offer"', false)
        ->assertSee('"price":"1100.00"', false)
        ->assertSee('"duration":"PT120M"', false)
        ->assertSee('Причал Листвянка');
});

it('marks checkout booking and payment pages as noindex', function (): void {
    $excursion = Excursion::factory()->create([
        'status' => ExcursionStatus::Active,
    ]);
    $slot = ExcursionSlot::factory()->create([
        'excursion_id' => $excursion->id,
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHours(2),
        'status' => ExcursionSlotStatus::Active,
    ]);
    $booking = Booking::factory()->create([
        'excursion_slot_id' => $slot->id,
    ]);

    $this->get(route('bookings.create', $slot))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false);

    $this->get(route('bookings.show', $booking))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false);

    $this->get(route('payment.success', ['booking' => $booking->uuid]))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false);

    $this->get(route('payment.fail', ['booking' => $booking->uuid]))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
});

it('renders sitemap with homepage and active excursions only', function (): void {
    $activeExcursion = Excursion::factory()->create([
        'title' => 'Активная экскурсия',
        'status' => ExcursionStatus::Active,
    ]);
    $draftExcursion = Excursion::factory()->create([
        'title' => 'Черновая экскурсия',
        'status' => ExcursionStatus::Draft,
    ]);

    $response = $this->get(route('seo.sitemap'));

    $response
        ->assertOk()
        ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
        ->assertSee('<loc>'.route('home', absolute: true).'</loc>', false)
        ->assertSee('<loc>'.route('excursions.show', $activeExcursion, absolute: true).'</loc>', false)
        ->assertDontSee(route('excursions.show', $draftExcursion, absolute: true));

    expect($response->headers->get('Content-Type'))->toContain('application/xml');
});

it('renders robots txt with closed service paths and sitemap link', function (): void {
    $response = $this->get(route('seo.robots'));

    $response
        ->assertOk()
        ->assertSee('User-agent: *')
        ->assertSee('Allow: /')
        ->assertSee('Disallow: /admin')
        ->assertSee('Disallow: /slots')
        ->assertSee('Disallow: /bookings')
        ->assertSee('Disallow: /payment')
        ->assertSee('Disallow: /payments')
        ->assertSee('Sitemap: '.rtrim((string) config('app.url'), '/').'/sitemap.xml');

    expect($response->headers->get('Content-Type'))->toContain('text/plain');
});
