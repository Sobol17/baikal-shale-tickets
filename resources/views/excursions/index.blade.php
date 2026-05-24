<x-layouts.app title="Экскурсии Байкал Шале">
    <section class="catalog-intro">
        <div class="app-container catalog-intro__grid">
            <div>
                <p class="kicker">Купить билет</p>
                <h1 class="page-title">Экскурсии Байкал Шале</h1>
                <p class="lead">Выберите маршрут, ближайшую отправку и оформите билеты без регистрации.</p>
            </div>

            <form class="header-search" method="GET" action="{{ route('home') }}">
                <input
                    class="header-search__input"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Название экскурсии или маршрута"
                    aria-label="Название экскурсии или маршрута"
                >
                <button class="header-search__button" type="submit">Найти</button>
            </form>
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
                        @php($nextSlot = $excursion->slots->first())

                        <article class="excursion-card">
                            @if ($excursion->cover_image)
                                <img class="excursion-card__image" src="{{ $excursion->cover_image }}" alt="{{ $excursion->title }}">
                            @else
                                <div class="excursion-card__fallback" aria-hidden="true">БШ</div>
                            @endif

                            <div class="excursion-card__body">
                                <div>
                                    <h3>{{ $excursion->title }}</h3>
                                    <p>{{ $excursion->short_description }}</p>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-pill">{{ $excursion->duration_minutes }} мин.</span>
                                    <span class="meta-pill">от {{ number_format($excursion->base_price_cents / 100, 0, ',', ' ') }} ₽</span>
                                    @if ($nextSlot)
                                        <span class="meta-pill">ближайшая {{ $nextSlot->starts_at->format('d.m H:i') }}</span>
                                    @endif
                                </div>

                                <a href="{{ route('excursions.show', $excursion) }}" class="button button--primary">Купить билет</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </section>
</x-layouts.app>
