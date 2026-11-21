@props([
    'title' => 'Экскурсии по озеру Байкал',
    'description' => 'Байкал Шале - бронирование экскурсий и билетов на Байкале.',
    'canonical' => null,
    'robots' => 'index,follow',
    'image' => null,
    'type' => 'website',
    'structuredData' => [],
])

@php
    $siteName = 'Байкал Шале';
    $title = trim((string) $title);
    $metaTitle = str_contains(mb_strtolower($title), mb_strtolower($siteName))
        ? $title
        : "{$title} | {$siteName}";
    $metaDescription = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $description)));
    $canonicalUrl = $canonical ?: url()->current();
    $imageUrl = $image;

    if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
        $imageUrl = url($imageUrl);
    }

    $structuredDataItems = collect(
        array_is_list((array) $structuredData) ? $structuredData : [$structuredData]
    )->filter()->values();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="robots" content="{{ $robots }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <meta property="og:locale" content="{{ app()->getLocale() }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:type" content="{{ $type }}">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        @if ($imageUrl)
            <meta property="og:image" content="{{ $imageUrl }}">
            <meta name="twitter:image" content="{{ $imageUrl }}">
        @endif
        <meta name="twitter:card" content="{{ $imageUrl ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        @foreach ($structuredDataItems as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
        @endforeach
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header
            class="site-header"
            x-data="siteSearch({ endpoint: @js(route('excursions.search')), initialQuery: @js(request('search')) })"
            x-on:keydown.escape.window="close()"
        >
            <div class="app-container site-header__inner">
                <a href="{{ route('home') }}" class="brand-logo" aria-label="Байкал Шале билеты">
                    <img class="brand-logo__image" src="{{ asset('images/logo.webp') }}" alt="" aria-hidden="true">
                    <span class="brand-logo__text">
                        <span class="brand-logo__name">Байкал Шале</span>
                        <span class="brand-logo__caption">экскурсии и билеты</span>
                    </span>
                </a>

                <div class="header-search">
                    <div class="header-search__box" x-on:click.outside="close()">
                        <input
                            class="header-search__input"
                            type="search"
                            x-model="query"
                            x-on:focus="open()"
                            x-on:input="search()"
                            x-on:keydown.enter.prevent="goToFirstResult()"
                            placeholder="Поиск экскурсии"
                            aria-label="Поиск экскурсии"
                            autocomplete="off"
                        >
                        <span class="header-search__icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M10.8 18.1a7.3 7.3 0 1 1 0-14.6 7.3 7.3 0 0 1 0 14.6ZM16.2 16.2 21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </span>

                        <section
                            class="search-popover"
                            x-cloak
                            x-show="isOpen"
                            x-transition.opacity
                            aria-label="Результаты поиска"
                        >
                            <div class="search-popover__head">
                                <h2>Результаты поиска</h2>
                            </div>

                            <div class="search-popover__results" x-show="results.length > 0">
                                <template x-for="item in results" :key="item.url">
                                    <article class="search-result">
                                        <template x-if="item.image">
                                            <img class="search-result__image" x-bind:src="item.image" x-bind:alt="item.title">
                                        </template>
                                        <template x-if="! item.image">
                                            <span class="search-result__fallback" aria-hidden="true">БШ</span>
                                        </template>

                                        <span class="search-result__body">
                                            <span class="search-result__title" x-text="item.title"></span>
                                            <span class="search-result__description" x-text="item.description"></span>
                                            <span class="search-result__meta">
                                                <span x-text="item.duration"></span>
                                                <span x-text="item.price"></span>
                                            </span>
                                        </span>

                                        <a class="search-result__button" x-bind:href="item.url">Подробнее</a>
                                    </article>
                                </template>
                            </div>

                            <p class="search-popover__empty" x-show="hasSearched && ! isLoading && results.length === 0">
                                По этому запросу экскурсий не найдено.
                            </p>
                        </section>
                    </div>

                    <a
                        class="header-social-link"
                        href="https://max.ru/u/f9LHodD0cOLfbLoAMVGgVuUjAMwTPD9KxcwfT60S8-UzqLQQe0kdjLwAP6I"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Написать в MAX"
                    >
                        <svg width="22" height="22" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M21.6826 29.9515C20.0143 29.9515 19.239 29.7069 17.8913 28.7284C17.0389 29.8292 14.3395 30.6894 14.2218 29.2177C14.2218 28.1128 13.9783 27.1792 13.7022 26.16C13.3734 24.9044 13 23.506 13 21.4798C13 16.6406 16.9537 13 21.638 13C26.3264 13 30 16.82 30 21.5247C30.0157 26.1566 26.2945 29.9268 21.6826 29.9515ZM21.7516 17.1828C19.4704 17.0646 17.6924 18.6505 17.2987 21.1374C16.974 23.1962 17.5504 25.7034 18.0415 25.8339C18.277 25.891 18.8696 25.4099 19.239 25.0389C19.8498 25.4627 20.5611 25.7172 21.3011 25.7768C23.6648 25.891 25.6846 24.0836 25.8433 21.7122C25.9357 19.3357 24.1157 17.3229 21.7516 17.1869L21.7516 17.1828Z" fill="currentColor"></path>
                        </svg>
                    </a>
                    <a class="header-phone-link" href="tel:+79397941808" aria-label="Позвонить в Байкал Шале">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6.6 3.8 8.9 3c.6-.2 1.3.1 1.6.7l1.1 2.5c.2.5.1 1.1-.3 1.5L10 9c.9 1.8 2.3 3.2 4 4l1.4-1.3c.4-.4 1-.5 1.5-.3l2.5 1.1c.6.3.9 1 .7 1.6l-.8 2.3c-.2.6-.7 1-1.3 1C11.7 17.4 6.6 12.3 6.6 6c0-.6.4-1.1 1-1.3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </div>

                <address class="site-contacts">
                    <a class="site-contacts__phone" href="tel:+79397941808">+7 (939) 794-18-08</a>
                    <a class="site-contacts__email" href="mailto:info@baikalvisa.ru">info@baikalvisa.ru</a>
                </address>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="footer" id="contact" role="contentinfo">
            <div class="u-container footer__top">
                <div class="footer__grid">
                    <div>
                        <div class="footer__brand">Baikal Shalet</div>
                        <p class="footer__muted">
                            Контактный тел.:
                            <a href="tel:+79397941808">+7(939) 794-18-08</a><br>
                            email:
                            <a href="mailto:info@baikalvisa.ru">info@baikalvisa.ru</a><br>
                            Дополнительный тел.: <a href="tel:+73952550464">+7(3952) 550-464</a>
                        </p>
                    </div>

                    <nav aria-label="Footer navigation">
                        <h3 class="u-visually-hidden">Footer navigation</h3>
                        <ul>
                            <li><a href="https://baikal-shalet.baikalvisa.ru/">Гостевой дом «Байкал Шале»</a></li>
                            <li><a href="https://baikal-shalet.baikalvisa.ru/#about" target="_blank">О нас</a></li>
                            <li><a href="https://baikal-shalet.baikalvisa.ru/#rooms" target="_blank">Наши номера</a></li>
                            <li><a href="https://baikal-shalet.baikalvisa.ru/#faq" target="_blank">FAQ</a></li>
                        </ul>
                    </nav>

                    <div class="footer__social">
                        <a href="https://max.ru/u/f9LHodD0cOLfbLoAMVGgVuUjAMwTPD9KxcwfT60S8-UzqLQQe0kdjLwAP6I" target="_blank">
                            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="44" height="44" rx="22" fill="#efe6d9b8"></rect><path fill-rule="evenodd" clip-rule="evenodd" d="M21.6826 29.9515C20.0143 29.9515 19.239 29.7069 17.8913 28.7284C17.0389 29.8292 14.3395 30.6894 14.2218 29.2177C14.2218 28.1128 13.9783 27.1792 13.7022 26.16C13.3734 24.9044 13 23.506 13 21.4798C13 16.6406 16.9537 13 21.638 13C26.3264 13 30 16.82 30 21.5247C30.0157 26.1566 26.2945 29.9268 21.6826 29.9515ZM21.7516 17.1828C19.4704 17.0646 17.6924 18.6505 17.2987 21.1374C16.974 23.1962 17.5504 25.7034 18.0415 25.8339C18.277 25.891 18.8696 25.4099 19.239 25.0389C19.8498 25.4627 20.5611 25.7172 21.3011 25.7768C23.6648 25.891 25.6846 24.0836 25.8433 21.7122C25.9357 19.3357 24.1157 17.3229 21.7516 17.1869L21.7516 17.1828Z" fill="#4b2e1e"></path></svg>
                        </a>
                        <a href="https://t.me/baikalprofi" target="_blank">
                            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="44" height="44" rx="22" fill="#efe6d9b8"></rect><path d="M28.6199 15.1591L13.5587 20.8711C12.9526 21.1371 12.7476 21.6698 13.4122 21.9588L17.2761 23.1664L26.6184 17.4882C27.1284 17.1317 27.6507 17.2268 27.2013 17.6189L19.1775 24.7638L18.9255 27.7875C19.159 28.2543 19.5864 28.2565 19.8591 28.0245L22.079 25.9587L25.8809 28.7586C26.7639 29.2727 27.2444 28.9409 27.4344 27.9986L29.9281 16.3857C30.187 15.2258 29.7455 14.7147 28.6199 15.1591Z" fill="#4b2e1e"></path></svg>
                        </a>
                        <a href="https://vk.com/baikal_profi_tour" target="_blank">
                            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="44" height="44" rx="22" fill="#efe6d9b8"></rect><path d="M31.5644 16.8804C31.6971 16.3725 31.5644 16 30.9367 16H28.8573C28.3282 16 28.0864 16.3222 27.9537 16.6761C27.9537 16.6761 26.8965 19.6459 25.3984 21.5716C24.9148 22.1319 24.6939 22.3089 24.4303 22.3089C24.2985 22.3089 24.1069 22.1319 24.1069 21.624V16.8804C24.1069 16.2709 23.9543 16 23.5134 16H20.246C19.916 16 19.7169 16.2818 19.7169 16.5505C19.7169 17.1272 20.4659 17.2605 20.5428 18.8857V22.4115C20.5428 23.1838 20.4223 23.3247 20.1568 23.3247C19.4523 23.3247 17.738 20.3439 16.7206 16.9317C16.5225 16.2687 16.3224 16.0011 15.7914 16.0011H13.713C13.1185 16.0011 13 16.3233 13 16.6772C13 17.3129 13.7045 20.4586 16.2816 24.619C17.9997 27.4599 20.4185 29 22.6221 29C23.9429 29 24.106 28.6581 24.106 28.0683V25.9209C24.106 25.2372 24.2321 25.0996 24.6503 25.0996C24.9575 25.0996 25.4865 25.2787 26.7201 26.6473C28.1291 28.2704 28.3624 28.9989 29.1541 28.9989H31.2325C31.8261 28.9989 32.1228 28.657 31.9522 27.982C31.7654 27.3103 31.0922 26.3327 30.199 25.176C29.7135 24.5163 28.9882 23.8063 28.7672 23.4514C28.4591 22.9937 28.5482 22.7916 28.7672 22.3853C28.7672 22.3853 31.3008 18.2773 31.5644 16.8804Z" fill="#4b2e1e"></path></svg>
                        </a>
                    </div>

                    <a class="footer__link" href="https://t.me/baikalchale" target="_blank">
                        Подписаться на телеграмм канал "Байкал Шале Листвянка"
                    </a>
                    <a class="footer_link" href="https://tourism.fsa.gov.ru/ru/resorts/hotels/20e945c6-c51c-11f0-af25-078f7c6ef7ec/about-resort" target="_blank">
                        Наш номер в реестре: С382025014651
                    </a>
                </div>
            </div>
            <div class="footer__bottom">
                <div class="u-container">
                    © <span id="year">2026</span>
                    ВНИМАНИЕ! Вся информация, опубликованная на сайте https://baikal-shalet.baikalvisa.ru, не является публичной офертой. Детали по каждому конкретному туру или услуге уточняйте у менеджеров. © ООО «Байкал премиум». Правообладателем данного доменного имени и материалов, размещенных на нём является Леник Станислав Евгеньевич, электронная почта - 890251329sl@gmail.com Любое использование этих материалов без получения письменного согласования запрещено.
                </div>
            </div>
        </footer>
    </body>
</html>
