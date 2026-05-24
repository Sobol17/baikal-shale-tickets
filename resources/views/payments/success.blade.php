<x-layouts.app title="Оплата принята">
    <section class="app-container page-section">
        <div class="panel content-panel result-panel">
            <p class="kicker">Оплата</p>
            <h1 class="section-title">Оплата принята</h1>
            <p>Если банк уже прислал подтверждение, письмо с организационной информацией отправлено на email.</p>
            @if ($booking)
                <a href="{{ route('bookings.show', $booking) }}" class="button button--primary button-spaced">Открыть бронирование</a>
            @else
                <a href="{{ route('home') }}" class="button button--primary button-spaced">На главную</a>
            @endif
        </div>
    </section>
</x-layouts.app>
