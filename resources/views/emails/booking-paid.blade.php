<h1>Бронирование подтверждено</h1>

<p>{{ $booking->customer_name }}, предоплата 10% прошла успешно.</p>

<p>
    Экскурсия: <strong>{{ $booking->excursion->title }}</strong><br>
    Дата: <strong>{{ $booking->excursion_date->format('d.m.Y') }}</strong><br>
    Количество билетов: <strong>{{ $booking->seats_count }}</strong><br>
    Взрослые: <strong>{{ $booking->adult_tickets_count }}</strong><br>
    Дети: <strong>{{ $booking->child_tickets_count }}</strong><br>
    Полная стоимость: <strong>{{ number_format($price->fullPriceCents / 100, 0, ',', ' ') }} ₽</strong><br>
    Внесённая предоплата 10%: <strong>{{ number_format($booking->amount_cents / 100, 0, ',', ' ') }} ₽</strong><br>
    Остаток к оплате: <strong>{{ number_format(($price->fullPriceCents - $booking->amount_cents) / 100, 0, ',', ' ') }} ₽</strong><br>
    Место встречи: <strong>{{ $booking->excursion->meeting_point }}</strong>
</p>

<p><strong>Оставшуюся сумму нужно оплатить лично перед началом экскурсии.</strong></p>

@if ($booking->excursion->rules)
    <p>{{ $booking->excursion->rules }}</p>
@endif

<p>
    Если возникнут вопросы, свяжитесь с нами:<br>
    Телефон: <a href="tel:+79397941808">+7 (939) 794-18-08</a><br>
    Дополнительный телефон: <a href="tel:+73952550464">+7 (3952) 550-464</a><br>
    Email: <a href="mailto:info@baikalvisa.ru">info@baikalvisa.ru</a>
</p>
