@php
    $adultTicketsCount = (int) old('adult_tickets_count', $adultTicketsCount);
    $childTicketsCount = (int) old('child_tickets_count', $childTicketsCount);
    $seatsCount = max(1, $adultTicketsCount + $childTicketsCount);
    $companionsCount = max(0, $seatsCount - 1);
    $defaultCompanions = array_fill(0, $companionsCount, [
        'last_name' => '',
        'first_name' => '',
    ]);
    $initialCompanions = array_values(old('companions', $defaultCompanions));

    if (count($initialCompanions) < $companionsCount) {
        $initialCompanions = array_merge($initialCompanions, array_slice($defaultCompanions, count($initialCompanions)));
    }

    $initialCompanions = array_slice($initialCompanions, 0, $companionsCount);
    $initialPrice = app(\App\Modules\Bookings\Support\BookingPriceCalculator::class)
        ->calculate($excursion, $adultTicketsCount, $childTicketsCount);
@endphp

<x-layouts.app title="Оформление заказа" robots="noindex,nofollow">
    <div class="app-container page-breadcrumbs">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <ol>
                <li><a href="{{ route('home') }}">Экскурсии</a></li>
                <li><a href="{{ route('excursions.show', $excursion) }}">{{ $excursion->title }}</a></li>
                <li aria-current="page"><span>Оформление</span></li>
            </ol>
        </nav>
    </div>

    <form
        class="app-container checkout-grid"
        method="POST"
        action="{{ route('bookings.store') }}"
        x-data="bookingCheckout({
            adultPriceCents: {{ $excursion->adult_price_cents }},
            childPriceCents: {{ $excursion->child_price_cents }},
            adultTicketsCount: {{ $adultTicketsCount }},
            childTicketsCount: {{ $childTicketsCount }},
            initialCompanions: @js($initialCompanions)
        })"
    >
        @csrf
        <input type="hidden" name="excursion_id" value="{{ $excursion->id }}">
        <input type="hidden" name="excursion_date" value="{{ $excursionDate }}">

        <main class="checkout-main">
            <section class="panel checkout-brief">
                <h1>{{ $excursion->title }}</h1>

                <div class="trip-facts">
                    <div>
                        <div class="fact-label">Дата</div>
                        <div class="fact-value">{{ \Illuminate\Support\Carbon::parse($excursionDate)->format('d.m.Y') }}</div>
                        <p class="field-help">{{ $excursion->meeting_point }}</p>
                    </div>
                    <div>
                        <div class="fact-label">Длительность</div>
                        <div class="fact-value">{{ $excursion->duration_hours_label }}</div>
                    </div>
                    <div>
                        <div class="fact-label">Билеты</div>
                        <div class="fact-value" x-text="seatsCount">{{ $seatsCount }}</div>
                        <p class="field-help">
                            Взрослые: <span x-text="adultTicketsCount">{{ $adultTicketsCount }}</span>,
                            дети: <span x-text="childTicketsCount">{{ $childTicketsCount }}</span>
                        </p>
                    </div>
                </div>
            </section>

            @if ($errors->any())
                <div class="alert alert--error">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="panel checkout-section">
                <div class="ticket-toolbar">
                    <div>
                        <h2>Билеты</h2>
                    </div>
                </div>

                <div class="field-grid field-grid--two">
                    <label class="form-field">
                        <span class="form-label">Взрослые <span class="required">*</span></span>
                        <input
                            class="form-control"
                            type="number"
                            name="adult_tickets_count"
                            min="0"
                            max="20"
                            required
                            x-model.number="adultTicketsCount"
                            x-on:input="normalizeTickets()"
                            x-on:change="normalizeTickets()"
                        >
                    </label>

                    <label class="form-field">
                        <span class="form-label">Дети <span class="required">*</span></span>
                        <input
                            class="form-control"
                            type="number"
                            name="child_tickets_count"
                            min="0"
                            max="20"
                            required
                            x-model.number="childTicketsCount"
                            x-on:input="normalizeTickets()"
                            x-on:change="normalizeTickets()"
                        >
                    </label>
                </div>
            </section>

            <section class="panel checkout-section" x-show="companionsCount > 0">
                <div class="ticket-toolbar">
                    <div>
                        <h2>Кто едет с вами</h2>
                        <p class="field-help">Укажите имя и фамилию каждого сопровождающего.</p>
                    </div>
                </div>

                <div class="companion-list">
                    <template x-for="(companion, index) in companions" :key="companion.key">
                        <article class="ticket-card">
                            <div class="ticket-card__head">
                                <div class="ticket-card__title" x-text="'Участник ' + (index + 2)"></div>
                            </div>

                            <div class="field-grid field-grid--two">
                                <label class="form-field">
                                    <span class="form-label">Фамилия <span class="required">*</span></span>
                                    <input class="form-control" x-bind:name="'companions[' + index + '][last_name]'" x-model="companion.last_name" placeholder="Фамилия" required>
                                </label>

                                <label class="form-field">
                                    <span class="form-label">Имя <span class="required">*</span></span>
                                    <input class="form-control" x-bind:name="'companions[' + index + '][first_name]'" x-model="companion.first_name" placeholder="Имя" required>
                                </label>
                            </div>
                        </article>
                    </template>
                </div>
            </section>

            <section class="panel checkout-section">
                <h2>Покупатель билетов</h2>

                <div class="field-grid">
                    <label class="form-field">
                        <span class="form-label">Фамилия <span class="required">*</span></span>
                        <input class="form-control" name="customer_last_name" value="{{ old('customer_last_name') }}" placeholder="Фамилия" required>
                    </label>

                    <label class="form-field">
                        <span class="form-label">Имя <span class="required">*</span></span>
                        <input class="form-control" name="customer_first_name" value="{{ old('customer_first_name') }}" placeholder="Имя" required>
                    </label>

                    <label class="form-field">
                        <span class="form-label">Отчество</span>
                        <input class="form-control" name="customer_middle_name" value="{{ old('customer_middle_name') }}" placeholder="Отчество">
                    </label>
                </div>

                <div class="field-grid field-grid--two field-grid-spaced">
                    <div class="form-field">
                        <label class="form-label" for="customer_email">Электронная почта <span class="required">*</span></label>
                        <input class="form-control" id="customer_email" name="customer_email" type="email" value="{{ old('customer_email') }}" placeholder="Электронная почта" required>
                        <p class="field-help">Электронные билеты будут отправлены на указанный адрес электронной почты.</p>
                    </div>

                    <div class="form-field">
                        <label class="form-label" for="customer_phone">Телефон <span class="required">*</span></label>
                        <input
                            class="form-control"
                            id="customer_phone"
                            name="customer_phone"
                            type="tel"
                            inputmode="tel"
                            value="{{ old('customer_phone') }}"
                            x-init="$el.value = formatPhoneNumber($el.value)"
                            x-on:input="$event.target.value = formatPhoneNumber($event.target.value)"
                            placeholder="+7 912 345-67-89"
                            required
                        >
                        <p class="field-help">Укажите номер, чтобы мы могли связаться с вами при переносе или отмене экскурсии.</p>
                    </div>
                </div>
            </section>
        </main>

        <aside class="panel checkout-summary">
            <div class="checkout-summary__body">
                <h2>К оплате сейчас</h2>

                <div class="checkout-summary__primary">
                    <span>Предоплата 10%</span>
                    <strong x-text="money(prepaymentCents)">{{ number_format($initialPrice->prepaymentCents / 100, 0, ',', ' ') }} ₽</strong>
                </div>

                <dl class="checkout-summary__details">
                    <div>
                        <dt>Полная стоимость</dt>
                        <dd x-text="money(fullPriceCents)">{{ number_format($initialPrice->fullPriceCents / 100, 0, ',', ' ') }} ₽</dd>
                    </div>
                    <div>
                        <dt>Остаток к оплате</dt>
                        <dd x-text="money(remainingCents)">{{ number_format($initialPrice->remainingCents / 100, 0, ',', ' ') }} ₽</dd>
                    </div>
                </dl>

                <p class="checkout-summary__payment-note">
                    Оставшиеся 90% оплачиваются лично перед началом экскурсии.
                </p>
            </div>

            <div class="checkout-summary__footer">
                <button class="button button--success" type="submit">Оплатить предоплату</button>

                <label class="checkbox-row">
                    <input type="checkbox" name="terms_accepted" value="1" @checked(old('terms_accepted')) required>
                    <span>Согласен с условиями покупки и обработкой персональных данных.</span>
                </label>

                <p class="legal-copy">
                    Нажимая на кнопку, вы подтверждаете корректность данных покупателя и сопровождающих.
                </p>
            </div>
        </aside>
    </form>
</x-layouts.app>
