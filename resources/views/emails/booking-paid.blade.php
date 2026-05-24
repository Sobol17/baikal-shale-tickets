<h1>Бронирование подтверждено</h1>

<p>{{ $booking->customer_name }}, оплата прошла успешно.</p>

<p>
    Экскурсия: <strong>{{ $booking->slot->excursion->title }}</strong><br>
    Дата и время: <strong>{{ $booking->slot->starts_at->format('d.m.Y H:i') }}</strong><br>
    Количество билетов: <strong>{{ $booking->seats_count }}</strong><br>
    Место встречи: <strong>{{ $booking->slot->excursion->meeting_point }}</strong>
</p>

@if ($booking->slot->excursion->rules)
    <p>{{ $booking->slot->excursion->rules }}</p>
@endif
