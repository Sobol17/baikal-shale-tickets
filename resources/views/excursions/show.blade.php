@php
    $canonical = route('excursions.show', $excursion, absolute: true);
    $image = $excursion->cover_image_url;

    if ($image && ! str_starts_with($image, 'http://') && ! str_starts_with($image, 'https://')) {
        $image = url($image);
    }

    $offerPriceCents = $slots->first()?->price_cents ?? $excursion->base_price_cents;
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
            'availability' => $slots->isNotEmpty()
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
    <div class="app-container detail-layout">
        <main class="detail-main">
            <section class="panel detail-hero">
                <a href="{{ route('home') }}" class="button button--ghost">Назад к экскурсиям</a>
                <p class="kicker kicker--spaced">{{ $excursion->season->label() }}</p>
                <h1>{{ $excursion->title }}</h1>
                <p class="lead">{{ $excursion->short_description }}</p>

                <div class="trip-facts">
                    <div>
                        <div class="fact-label">Длительность</div>
                        <div class="fact-value">{{ $excursion->duration_minutes }} мин.</div>
                    </div>
                    <div>
                        <div class="fact-label">Место встречи</div>
                        <div class="fact-value">{{ $excursion->meeting_point }}</div>
                    </div>
                </div>
            </section>

            <section class="panel content-panel" id="schedule">
                <h2>Ближайшие отправки</h2>

                @if ($slots->isEmpty())
                    <p>На эту экскурсию пока нет доступных отправок.</p>
                @else
                    <ul class="slot-list">
                        @foreach ($slots as $slot)
                            <li class="slot-card">
                                <div>
                                    <div class="slot-card__time">{{ $slot->starts_at->format('H:i') }}</div>
                                    <span class="slot-card__date">{{ $slot->starts_at->format('d.m.Y') }}</span>
                                </div>

                                <div class="slot-card__route">
                                    <span><strong>Отправление:</strong> {{ $slot->starts_at->format('d.m.Y H:i') }}</span>
                                    <span><strong>Окончание:</strong> {{ $slot->ends_at->format('d.m.Y H:i') }}</span>
                                    <span>{{ $excursion->meeting_point }}</span>
                                </div>

                                <div>
                                    <div class="slot-card__price">{{ number_format($slot->price_cents / 100, 0, ',', ' ') }} ₽</div>
                                    <a href="{{ route('bookings.create', $slot) }}" class="button button--accent button-spaced-sm">Купить билет</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

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
        </main>

        <aside class="panel detail-sidebar">
            <h2 class="aside-title">Выберите ближайший рейс</h2>
            <p class="muted-copy">Выберите удобное время отправления и перейдите к оформлению билетов.</p>

            @if ($slots->isNotEmpty())
                <a class="button button--primary" href="#schedule">Смотреть отправки</a>
            @else
                <a class="button button--ghost" href="{{ route('home') }}">Другие экскурсии</a>
            @endif
        </aside>
    </div>
</x-layouts.app>
