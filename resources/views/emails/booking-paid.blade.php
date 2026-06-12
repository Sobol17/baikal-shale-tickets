<h1>Бронирование подтверждено</h1>

<p>{{ $booking->customer_name }}, оплата прошла успешно.</p>

<p>
    Экскурсия: <strong>{{ $booking->excursion->title }}</strong><br>
    Дата: <strong>{{ $booking->excursion_date->format('d.m.Y') }}</strong><br>
    Количество билетов: <strong>{{ $booking->seats_count }}</strong><br>
    Взрослые: <strong>{{ $booking->adult_tickets_count }}</strong><br>
    Дети: <strong>{{ $booking->child_tickets_count }}</strong><br>
    Место встречи: <strong>{{ $booking->excursion->meeting_point }}</strong>
</p>

@if ($booking->excursion->rules)
    <p>{{ $booking->excursion->rules }}</p>
@endif
