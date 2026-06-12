<x-layouts.app title="Бронирование" robots="noindex,nofollow">
    <section class="app-container page-section">
        <div class="panel content-panel narrow-panel">
            @if (session('status'))
                <div class="alert alert--status">
                    {{ session('status') }}
                </div>
            @endif

            <p class="kicker">Бронирование {{ $booking->uuid }}</p>
            <h1 class="section-title">{{ $booking->excursion->title }}</h1>
            <dl class="trip-facts">
                <div>
                    <dt class="fact-label">Статус</dt>
                    <dd class="fact-value">{{ $booking->status->label() }}</dd>
                </div>
                <div>
                    <dt class="fact-label">Дата</dt>
                    <dd class="fact-value">{{ $booking->excursion_date->format('d.m.Y') }}</dd>
                </div>
                <div>
                    <dt class="fact-label">Взрослые</dt>
                    <dd class="fact-value">{{ $booking->adult_tickets_count }}</dd>
                </div>
                <div>
                    <dt class="fact-label">Дети</dt>
                    <dd class="fact-value">{{ $booking->child_tickets_count }}</dd>
                </div>
                <div>
                    <dt class="fact-label">Всего билетов</dt>
                    <dd class="fact-value">{{ $booking->seats_count }}</dd>
                </div>
                <div>
                    <dt class="fact-label">Сумма</dt>
                    <dd class="fact-value">{{ number_format($booking->amount_cents / 100, 0, ',', ' ') }} ₽</dd>
                </div>
            </dl>

            @if ($booking->companions)
                <section class="passenger-list">
                    <h2>Сопровождающие</h2>
                    <div class="booking-person-list">
                        @foreach ($booking->companions as $companion)
                            <div class="booking-person-card">
                                <div>
                                    <strong>{{ $companion['last_name'] }} {{ $companion['first_name'] }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($booking->latestPayment()?->payment_url && $booking->status->value === 'pending_payment')
                <a href="{{ $booking->latestPayment()->payment_url }}" class="button button--primary button-spaced-lg">Продолжить оплату</a>
            @endif
        </div>
    </section>
</x-layouts.app>
