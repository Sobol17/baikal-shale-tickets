<x-layouts.app title="Оплата не прошла">
    <section class="app-container page-section">
        <div class="panel content-panel result-panel">
            <p class="kicker">Оплата</p>
            <h1 class="section-title">Оплата не прошла</h1>
            <p>Попробуйте оплатить еще раз или выберите другую дату экскурсии.</p>
            @if ($booking)
                <a href="{{ route('bookings.show', $booking) }}" class="button button--primary button-spaced">Открыть бронирование</a>
            @else
                <a href="{{ route('home') }}" class="button button--primary button-spaced">На главную</a>
            @endif
        </div>
    </section>
</x-layouts.app>
