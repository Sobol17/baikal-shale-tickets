@php
    $excursions = $excursionsBySeason->flatten(1)->values();
    $homeUrl = route('home', absolute: true);
    $description = 'Экскурсии Байкал Шале: маршруты по Байкалу, выбор даты и бронирование билетов без регистрации.';
    $structuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Байкал Шале',
            'url' => $homeUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('home', ['search' => '{search_term_string}'], absolute: true),
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Байкал Шале',
            'url' => $homeUrl,
            'email' => 'info@baikalvisa.ru',
            'telephone' => '+79397941808',
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Экскурсии',
                    'item' => $homeUrl,
                ],
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Экскурсии Байкал Шале',
            'itemListElement' => $excursions
                ->map(fn ($excursion, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => route('excursions.show', $excursion, absolute: true),
                    'name' => $excursion->title,
                ])
                ->all(),
        ],
    ];
@endphp

<x-layouts.app
    title="Экскурсии Байкал Шале"
    :description="$description"
    :canonical="$homeUrl"
    :structured-data="$structuredData"
>
    <section class="catalog-intro">
        <div class="app-container catalog-intro__grid">
            <div>
                <h1 class="page-title">Экскурсии Байкал Шале</h1>
                <p class="lead">Выберите маршрут, дату экскурсии и оформите билеты без регистрации.</p>
            </div>
        </div>
    </section>

    <section class="app-container page-section">
        @php($hasResults = $excursionsBySeason->flatten(1)->isNotEmpty())

        @if (! $hasResults)
            <div class="empty-state">
                @if ($search)
                    По запросу «{{ $search }}» ничего не найдено.
                @else
                    Активных экскурсий пока нет.
                @endif
            </div>
        @endif

        @foreach ($seasons as $season)
            @php($seasonExcursions = $excursionsBySeason->get($season->value, collect()))

            @continue($seasonExcursions->isEmpty())

            <section class="season-section">
                <div class="season-heading">
                    <h2>{{ $season->label() }}</h2>
                    <span class="season-count">{{ $seasonExcursions->count() }} маршрутов</span>
                </div>

                <div class="excursion-grid">
                    @foreach ($seasonExcursions as $excursion)
                        <article class="excursion-card">
                            @if ($excursion->cover_image_url)
                                <a class="excursion-card__media-link" href="{{ route('excursions.show', $excursion) }}" aria-label="{{ $excursion->title }}">
                                    <img class="excursion-card__image" src="{{ $excursion->cover_image_url }}" alt="{{ $excursion->title }}">
                                </a>
                            @else
                                <a class="excursion-card__media-link" href="{{ route('excursions.show', $excursion) }}" aria-label="{{ $excursion->title }}">
                                    <div class="excursion-card__fallback" aria-hidden="true">
                                        <img class="excursion-card__fallback-logo" src="{{ asset('images/logo.webp') }}" alt="">
                                    </div>
                                </a>
                            @endif

                            <div class="excursion-card__body">
                                <div>
                                    <h3>
                                        <a href="{{ route('excursions.show', $excursion) }}">{{ $excursion->title }}</a>
                                    </h3>
                                    <p>{{ $excursion->short_description }}</p>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-pill">{{ $excursion->duration_minutes }} мин.</span>
                                    <span class="meta-pill">взрослый {{ number_format($excursion->adult_price_cents / 100, 0, ',', ' ') }} ₽</span>
                                    <span class="meta-pill">детский {{ number_format($excursion->child_price_cents / 100, 0, ',', ' ') }} ₽</span>
                                </div>

                                @include('excursions.partials.booking-form', ['excursion' => $excursion])

                                <a href="{{ route('excursions.show', $excursion) }}" class="button button--ghost">Подробнее</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </section>
</x-layouts.app>
