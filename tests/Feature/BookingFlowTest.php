<?php

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Notifications\Mail\BookingPaidMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

function bookingPayload(Excursion $excursion, ?string $date = null, int $adultTickets = 1, int $childTickets = 1): array
{
    $seatsCount = $adultTickets + $childTickets;
    $companions = [];

    for ($index = 1; $index < $seatsCount; $index++) {
        $companions[] = [
            'last_name' => 'Petrov',
            'first_name' => 'Guest '.$index,
        ];
    }

    return [
        'excursion_id' => $excursion->id,
        'excursion_date' => $date ?? today()->addDay()->toDateString(),
        'adult_tickets_count' => $adultTickets,
        'child_tickets_count' => $childTickets,
        'companions' => $companions,
        'customer_last_name' => 'Petrov',
        'customer_first_name' => 'Ivan',
        'customer_middle_name' => '',
        'customer_email' => 'ivan@example.com',
        'customer_phone' => '+79990000000',
        'terms_accepted' => '1',
    ];
}

it('creates a guest group booking and confirms it through fake payment gateway', function (): void {
    Mail::fake();
    Config::set('services.payments.default', 'fake');

    $excursion = Excursion::factory()->create([
        'status' => ExcursionStatus::Active,
        'adult_price_cents' => 200000,
        'child_price_cents' => 100000,
        'booking_starts_on' => today()->toDateString(),
        'booking_ends_on' => today()->addMonth()->toDateString(),
    ]);

    $response = $this->post(route('bookings.store'), bookingPayload($excursion));

    $booking = Booking::query()->firstOrFail();

    $response->assertRedirect(route('payments.fake.paid', $booking->latestPayment()));

    $this->get(route('payments.fake.paid', $booking->latestPayment()))
        ->assertRedirect(route('payment.success', ['booking' => $booking->uuid]));

    expect($booking->refresh()->status)->toBe(BookingStatus::Paid);
    expect($booking->amount_cents)->toBe(30000);
    expect($booking->latestPayment()->amount_cents)->toBe(30000);
    expect($booking->adult_tickets_count)->toBe(1);
    expect($booking->child_tickets_count)->toBe(1);
    expect($booking->seats_count)->toBe(2);
    expect($booking->companions)->toHaveCount(1);
    expect($booking->companions[0])->toMatchArray([
        'last_name' => 'Petrov',
        'first_name' => 'Guest 1',
    ]);

    Mail::assertSent(BookingPaidMail::class, function (BookingPaidMail $mail): bool {
        $body = $mail->render();

        expect($body)
            ->toContain('предоплата 10% прошла успешно')
            ->toContain('Полная стоимость')
            ->toContain('3 000 ₽')
            ->toContain('Внесённая предоплата 10%')
            ->toContain('300 ₽')
            ->toContain('Остаток к оплате')
            ->toContain('2 700 ₽')
            ->toContain('оплатить лично перед началом экскурсии');

        return true;
    });
});

it('opens checkout from selected excursion date and ticket counts', function (): void {
    $excursion = Excursion::factory()->create([
        'title' => 'Листвянка - Большие Коты',
        'status' => ExcursionStatus::Active,
        'adult_price_cents' => 110000,
        'child_price_cents' => 80000,
        'booking_starts_on' => today()->toDateString(),
        'booking_ends_on' => today()->addMonth()->toDateString(),
    ]);
    $date = today()->addDay()->toDateString();

    $this->get(route('excursions.show', $excursion))
        ->assertOk()
        ->assertSee('Купить билет')
        ->assertSee('aria-label="Хлебные крошки"', false)
        ->assertSee(route('home'), false)
        ->assertSee('aria-current="page"', false)
        ->assertDontSee('Назад к экскурсиям')
        ->assertSee('Дата экскурсии')
        ->assertSee('Полная стоимость взрослого билета')
        ->assertSee('Полная стоимость детского билета')
        ->assertSee('На сайте оплачивается предоплата 10%')
        ->assertSee('Оставшиеся 90% нужно оплатить лично перед началом экскурсии')
        ->assertSee('calendar-popover', false)
        ->assertDontSee('type="date"', false)
        ->assertDontSee('adult_tickets_count', false)
        ->assertDontSee('child_tickets_count', false)
        ->assertDontSee('Ближайшие отправки')
        ->assertDontSee('/slots/', false);

    $this->get(route('bookings.create', [
        'excursion_id' => $excursion->id,
        'excursion_date' => $date,
        'adult_tickets_count' => 2,
        'child_tickets_count' => 1,
    ]))
        ->assertOk()
        ->assertSee('Билеты')
        ->assertSee('adult_tickets_count', false)
        ->assertSee('child_tickets_count', false)
        ->assertSee('aria-label="Хлебные крошки"', false)
        ->assertSee(route('home'), false)
        ->assertSee(route('excursions.show', $excursion), false)
        ->assertSee('Оформление')
        ->assertDontSee('Назад к экскурсии')
        ->assertSee('Покупатель экскурсии')
        ->assertSee('К оплате сейчас')
        ->assertSee('Предоплата 10%')
        ->assertSee('Полная стоимость')
        ->assertSee('3 000 ₽')
        ->assertSee('300 ₽')
        ->assertSee('Остаток к оплате')
        ->assertSee('2 700 ₽')
        ->assertSee('Оставшиеся 90% оплачиваются лично перед началом экскурсии')
        ->assertDontSee('Сервисный сбор')
        ->assertDontSee('Предоплата за взрослых')
        ->assertDontSee('Предоплата за детей')
        ->assertSee('Оплатить предоплату');
});

it('shows purchase controls on catalog cards', function (): void {
    $excursion = Excursion::factory()->create([
        'title' => 'Кругобайкальская железная дорога',
        'status' => ExcursionStatus::Active,
        'adult_price_cents' => 302000,
        'child_price_cents' => 180000,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($excursion->title)
        ->assertSee('Дата экскурсии')
        ->assertDontSee('Полная стоимость взрослого билета')
        ->assertDontSee('На сайте оплачивается предоплата 10%')
        ->assertDontSee('Оставшиеся 90% нужно оплатить лично перед началом экскурсии')
        ->assertSee('calendar-popover', false)
        ->assertDontSee('type="date"', false)
        ->assertDontSee('adult_tickets_count', false)
        ->assertDontSee('child_tickets_count', false)
        ->assertSee(route('bookings.create'), false)
        ->assertDontSee('ближайшая');
});

it('rejects booking dates outside excursion bounds', function (): void {
    $excursion = Excursion::factory()->create([
        'status' => ExcursionStatus::Active,
        'booking_starts_on' => today()->addDays(5)->toDateString(),
        'booking_ends_on' => today()->addDays(10)->toDateString(),
    ]);

    $this->post(route('bookings.store'), bookingPayload($excursion, today()->addDay()->toDateString()))
        ->assertSessionHasErrors('excursion_date');

    expect(Booking::query()->count())->toBe(0);
});

it('requires companion names for every participant except the buyer', function (): void {
    $excursion = Excursion::factory()->create([
        'status' => ExcursionStatus::Active,
        'booking_starts_on' => today()->toDateString(),
        'booking_ends_on' => today()->addMonth()->toDateString(),
    ]);
    $payload = bookingPayload($excursion, adultTickets: 2, childTickets: 1);
    $payload['companions'] = [
        ['last_name' => 'Petrov', 'first_name' => 'Guest 1'],
    ];

    $this->post(route('bookings.store'), $payload)
        ->assertSessionHasErrors('companions');

    expect(Booking::query()->count())->toBe(0);
});
