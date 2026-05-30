@php
    $defaultPassenger = [
        'ticket_type' => 'adult',
        'last_name' => '',
        'first_name' => '',
        'middle_name' => '',
        'no_middle_name' => false,
        'citizenship' => 'Россия',
        'phone' => '',
    ];
    $initialPassengers = old('passengers', [$defaultPassenger]);
    $initialSeats = (int) old('seats_count', max(1, count($initialPassengers)));
@endphp

<x-layouts.app title="Оформление заказа" robots="noindex,nofollow">
    <form
        class="app-container checkout-grid"
        method="POST"
        action="{{ route('bookings.store') }}"
        x-data="bookingCheckout({
            priceCents: {{ $slot->price_cents }},
            initialSeats: {{ $initialSeats }},
            initialPassengers: @js($initialPassengers)
        })"
    >
        @csrf
        <input type="hidden" name="excursion_slot_id" value="{{ $slot->id }}">

        <main class="checkout-main">
            <section class="panel checkout-brief">
                <a href="{{ route('excursions.show', $slot->excursion) }}" class="button button--ghost">Назад к расписанию</a>
                <h1>{{ $slot->excursion->title }}</h1>

                <div class="trip-facts">
                    <div>
                        <div class="fact-label">Отправление</div>
                        <div class="fact-value">{{ $slot->starts_at->format('d.m.Y H:i') }}</div>
                        <p class="field-help">{{ $slot->excursion->meeting_point }}</p>
                    </div>
                    <div>
                        <div class="fact-label">Окончание</div>
                        <div class="fact-value">{{ $slot->ends_at->format('d.m.Y H:i') }}</div>
                        <p class="field-help">{{ $slot->excursion->duration_minutes }} мин.</p>
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
                        <h2>Пассажиры</h2>
                    </div>

                    <label class="form-field ticket-count">
                        <span class="form-label">Билетов</span>
                        <input
                            class="form-control"
                            type="number"
                            name="seats_count"
                            min="1"
                            max="20"
                            required
                            x-model.number="seats"
                            x-on:input="syncPassengers()"
                            x-on:change="syncPassengers()"
                        >
                    </label>
                </div>

                <template x-for="(passenger, index) in passengers" :key="passenger.key">
                    <article class="ticket-card">
                        <div class="ticket-card__head">
                            <div class="ticket-card__title" x-text="'Билет ' + (index + 1)"></div>
                            <div class="ticket-card__price" x-text="money(priceCents)"></div>
                        </div>

                        <div class="form-field">
                            <label class="form-label" x-bind:for="'ticket-type-' + index">Категория билета <span class="required">*</span></label>
                            <select class="form-control" x-bind:id="'ticket-type-' + index" x-bind:name="'passengers[' + index + '][ticket_type]'" x-model="passenger.ticket_type" required>
                                <option value="adult">взрослый</option>
                                <option value="child">детский</option>
                            </select>
                        </div>

                        <div class="field-grid">
                            <label class="form-field">
                                <span class="form-label">Фамилия <span class="required">*</span></span>
                                <input class="form-control" x-bind:name="'passengers[' + index + '][last_name]'" x-model="passenger.last_name" placeholder="Фамилия" required>
                            </label>

                            <label class="form-field">
                                <span class="form-label">Имя <span class="required">*</span></span>
                                <input class="form-control" x-bind:name="'passengers[' + index + '][first_name]'" x-model="passenger.first_name" placeholder="Имя" required>
                            </label>

                            <div class="form-field">
                                <label class="form-label" x-bind:for="'middle-name-' + index">Отчество</label>
                                <input
                                    class="form-control"
                                    x-bind:id="'middle-name-' + index"
                                    x-bind:name="'passengers[' + index + '][middle_name]'"
                                    x-model="passenger.middle_name"
                                    x-bind:disabled="passenger.no_middle_name"
                                    placeholder="Отчество"
                                >
                                <label class="checkbox-row">
                                    <input type="hidden" x-bind:name="'passengers[' + index + '][no_middle_name]'" value="0">
                                    <input type="checkbox" value="1" x-bind:name="'passengers[' + index + '][no_middle_name]'" x-model="passenger.no_middle_name">
                                    Нет отчества
                                </label>
                            </div>
                        </div>

                        <div class="field-grid field-grid--two">
                            <label class="form-field">
                                <span class="form-label">Гражданство <span class="required">*</span></span>
                                <select class="form-control" x-bind:name="'passengers[' + index + '][citizenship]'" x-model="passenger.citizenship" required>
                                    <option value="Россия">Россия</option>
                                    <option value="Казахстан">Казахстан</option>
                                    <option value="Беларусь">Беларусь</option>
                                    <option value="Монголия">Монголия</option>
                                    <option value="Другое">Другое</option>
                                </select>
                            </label>

                            <label class="form-field">
                                <span class="form-label">Телефон</span>
                                <input
                                    class="form-control"
                                    type="tel"
                                    inputmode="tel"
                                    x-bind:name="'passengers[' + index + '][phone]'"
                                    x-model="passenger.phone"
                                    x-on:input="passenger.phone = formatPhoneNumber($event.target.value); $event.target.value = passenger.phone"
                                    placeholder="+7 912 345-67-89"
                                >
                            </label>
                        </div>
                    </article>
                </template>
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
                        <p class="field-help">Укажите номер, чтобы мы могли связаться с вами при переносе или отмене рейса.</p>
                    </div>
                </div>
            </section>
        </main>

        <aside class="panel checkout-summary">
            <div class="checkout-summary__body">
                <h2>Заказ</h2>
                <div class="summary-line">
                    <span>Билеты, <span x-text="seats"></span> шт.</span>
                    <strong x-text="money(totalCents)"></strong>
                </div>
                <div class="summary-line summary-line--muted">
                    <span>Сервисный сбор</span>
                    <strong>0 ₽</strong>
                </div>
                <div class="summary-line summary-line--muted">
                    <span>Скидка</span>
                    <strong>- 0 ₽</strong>
                </div>
                <div class="summary-line summary-total">
                    <span>Итого</span>
                    <strong x-text="money(totalCents)"></strong>
                </div>
            </div>

            <div class="checkout-summary__footer">
                <button class="button button--success" type="submit">Оформить заказ</button>

                <label class="checkbox-row">
                    <input type="checkbox" name="terms_accepted" value="1" @checked(old('terms_accepted')) required>
                    <span>Согласен с условиями покупки и обработкой персональных данных.</span>
                </label>

                <p class="legal-copy">
                    Нажимая на кнопку, вы подтверждаете корректность данных пассажиров и покупателя.
                </p>
            </div>
        </aside>
    </form>
</x-layouts.app>
