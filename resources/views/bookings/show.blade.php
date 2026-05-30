<x-layouts.app title="Бронирование" robots="noindex,nofollow">
    <section class="app-container page-section">
        <div class="panel content-panel narrow-panel">
            @if (session('status'))
                <div class="alert alert--status">
                    {{ session('status') }}
                </div>
            @endif

            <p class="kicker">Бронирование {{ $booking->uuid }}</p>
            <h1 class="section-title">{{ $booking->slot->excursion->title }}</h1>
            <dl class="trip-facts">
                <div>
                    <dt class="fact-label">Статус</dt>
                    <dd class="fact-value">{{ $booking->status->label() }}</dd>
                </div>
                <div>
                    <dt class="fact-label">Дата</dt>
                    <dd class="fact-value">{{ $booking->slot->starts_at->format('d.m.Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="fact-label">Билеты</dt>
                    <dd class="fact-value">{{ $booking->seats_count }}</dd>
                </div>
                <div>
                    <dt class="fact-label">Сумма</dt>
                    <dd class="fact-value">{{ number_format($booking->amount_cents / 100, 0, ',', ' ') }} ₽</dd>
                </div>
            </dl>

            @if ($booking->passengers)
                <section class="passenger-list">
                    <h2>Пассажиры</h2>
                    <div class="slot-list">
                        @foreach ($booking->passengers as $passenger)
                            <div class="slot-card booking-passenger-card">
                                <div>
                                    <strong>{{ $passenger['last_name'] }} {{ $passenger['first_name'] }} {{ $passenger['middle_name'] ?? '' }}</strong>
                                    <div class="passenger-muted">{{ $passenger['citizenship'] ?? 'Россия' }}</div>
                                </div>
                                <span class="meta-pill">{{ ($passenger['ticket_type'] ?? 'adult') === 'child' ? 'детский' : 'взрослый' }}</span>
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
