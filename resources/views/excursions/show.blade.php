@php
    $canonical = route('excursions.show', $excursion, absolute: true);
    $image = $excursion->cover_image_url;

    if ($image && ! str_starts_with($image, 'http://') && ! str_starts_with($image, 'https://')) {
        $image = url($image);
    }

    $offerPriceCents = $excursion->adult_price_cents;
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'TouristTrip',
        'name' => $excursion->title,
        'description' => trim(strip_tags($excursion->short_description)),
        'url' => $canonical,
        'duration' => 'PT'.$excursion->duration_minutes.'M',
        'touristType' => 'экскурсанты',
        'itinerary' => [
            '@type' => 'Place',
            'name' => $excursion->meeting_point,
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'Байкал Шале',
            'url' => route('home', absolute: true),
            'email' => 'info@baikalvisa.ru',
            'telephone' => '+79397941808',
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => $canonical,
            'priceCurrency' => 'RUB',
            'price' => number_format($offerPriceCents / 100, 2, '.', ''),
            'availability' => $excursion->booking_ends_on && ! $excursion->booking_ends_on->isBefore(today())
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
        ],
    ];

    if ($image) {
        $structuredData['image'] = $image;
    }
@endphp

<x-layouts.app
    :title="$excursion->title"
    :description="$excursion->short_description"
    :canonical="$canonical"
    :image="$image"
    type="product"
    :structured-data="$structuredData"
>
    <div class="app-container page-breadcrumbs">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <ol>
                <li><a href="{{ route('home') }}">Экскурсии</a></li>
                <li aria-current="page"><span>{{ $excursion->title }}</span></li>
            </ol>
        </nav>
    </div>

    <div class="app-container detail-layout">
        <section class="panel detail-hero">
            <p class="kicker">{{ $excursion->season->label() }}</p>
            <h1>{{ $excursion->title }}</h1>
            <p class="lead">{{ $excursion->short_description }}</p>

            <div class="trip-facts">
                <div>
                    <div class="fact-label">Длительность</div>
                    <div class="fact-value">{{ $excursion->duration_hours_label }}</div>
                </div>
                <div>
                    <div class="fact-label">Место встречи</div>
                    <div class="fact-value">{{ $excursion->meeting_point }}</div>
                </div>
            </div>
        </section>

        <aside class="detail-sidebar">
            <section class="panel detail-purchase">
                <h2 class="aside-title">Купить билет</h2>
                <p class="muted-copy">Выберите дату и количество участников.</p>

                @include('excursions.partials.booking-form', [
                    'excursion' => $excursion,
                    'showPrepaymentDetails' => true,
                ])
            </section>

            <a class="stay-card" href="https://baikal-shalet.baikalvisa.ru/#rooms" aria-label="Забронировать номер в «Байкал Шале»">
                <img class="stay-card__image" src="{{ asset('images/baikal-shalet.webp') }}" alt="" width="800" height="534" loading="lazy">
                <div class="stay-card__body">
                    <h2>Гостевой дом «Байкал Шале»</h2>
                    <p>Листвянка</p>
                    <span class="stay-card__action">
                        <span class="stay-card__label">Забронировать</span>
                        <span class="stay-card__arrow" aria-hidden="true">↗</span>
                    </span>
                </div>
            </a>
        </aside>

        <div class="detail-main">
            <section class="panel content-panel">
                <h2>Описание экскурсии</h2>
                <div class="content-body">{!! $excursion->full_description !!}</div>
            </section>

            @if ($excursion->rules)
                <section class="panel content-panel">
                    <h2>Организационная информация</h2>
                    <p>{{ $excursion->rules }}</p>
                </section>
            @endif
        </div>
    </div>
</x-layouts.app>
