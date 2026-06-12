<?php

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Notifications\Mail\BookingPaidMail;
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
    expect($booking->amount_cents)->toBe(300000);
    expect($booking->adult_tickets_count)->toBe(1);
    expect($booking->child_tickets_count)->toBe(1);
    expect($booking->seats_count)->toBe(2);
    expect($booking->companions)->toHaveCount(1);
    expect($booking->companions[0])->toMatchArray([
        'last_name' => 'Petrov',
        'first_name' => 'Guest 1',
    ]);

    Mail::assertSent(BookingPaidMail::class);
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
        ->assertSee('Дата экскурсии')
        ->assertSee('calendar-popover', false)
        ->assertDontSee('type="date"', false)
        ->assertDontSee('adult_tickets_count', false)
        ->assertDontSee('child_tickets_count', false)
        ->assertDontSee('Ближайшие отправки')
        ->assertDontSee('/slots/', false);

    $this->get(route('bookings.create', [
        'excursion_id' => $excursion->id,
        'excursion_date' => $date,
    ]))
        ->assertOk()
        ->assertSee('Билеты')
        ->assertSee('adult_tickets_count', false)
        ->assertSee('child_tickets_count', false)
        ->assertSee('Покупатель билетов')
        ->assertSee('Взрослые билеты')
        ->assertSee('Детские билеты');
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
