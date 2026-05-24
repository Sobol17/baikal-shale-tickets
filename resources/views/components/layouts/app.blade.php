<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'Shale Tickets') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header class="site-header">
            <div class="app-container site-header__inner">
                <a href="{{ route('home') }}" class="brand-logo" aria-label="Байкал Шале билеты">
                    <span class="brand-logo__mark">БШ</span>
                    <span class="brand-logo__text">
                        <span class="brand-logo__name">Байкал Шале</span>
                        <span class="brand-logo__caption">экскурсии и билеты</span>
                    </span>
                </a>

                <form class="header-search" method="GET" action="{{ route('home') }}">
                    <input
                        class="header-search__input"
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Поиск экскурсии"
                        aria-label="Поиск экскурсии"
                    >
                    <button class="header-search__button" type="submit">Найти</button>
                </form>

                <address class="site-contacts">
                    <a class="site-contacts__phone" href="tel:+79397941808">+7 (939) 794-18-08</a>
                    <a class="site-contacts__email" href="mailto:info@baikalvisa.ru">info@baikalvisa.ru</a>
                </address>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="site-footer">
            <div class="app-container">
                © {{ now()->year }} Байкал Шале. Бронирование экскурсий и билетов.
            </div>
        </footer>
    </body>
</html>
