<x-layouts.app title="Оплата принята" robots="noindex,nofollow">
    <section class="app-container page-section">
        <div class="panel content-panel result-panel">
            <p class="kicker">Оплата</p>
            <h1 class="section-title">Оплата принята</h1>
            <p>Бронирование прошло успешно! Спасибо за покупку! Письмо с организационной информацией отправлено на указанный вами email.</p>
            @if ($booking)
                <a href="{{ route('bookings.show', $booking) }}" class="button button--primary button-spaced">Подробнее</a>
            @else
                <a href="{{ route('home') }}" class="button button--primary button-spaced">На главную</a>
            @endif
        </div>
    </section>
</x-layouts.app>
