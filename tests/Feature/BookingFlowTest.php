<?php

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionSlotStatus;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Excursions\Models\ExcursionSlot;
use App\Modules\Notifications\Mail\BookingPaidMail;
use Illuminate\Support\Facades\Mail;

function bookingPayload(ExcursionSlot $slot, int $seatsCount = 2): array
{
    $passengers = [];

    for ($index = 1; $index <= $seatsCount; $index++) {
        $passengers[] = [
            'ticket_type' => 'adult',
            'last_name' => 'Petrov',
            'first_name' => 'Ivan '.$index,
            'middle_name' => '',
            'citizenship' => 'Россия',
            'phone' => '+79990000000',
        ];
    }

    return [
        'excursion_slot_id' => $slot->id,
        'passengers' => $passengers,
        'customer_last_name' => 'Petrov',
        'customer_first_name' => 'Ivan',
        'customer_middle_name' => '',
        'customer_email' => 'ivan@example.com',
        'customer_phone' => '+79990000000',
        'seats_count' => $seatsCount,
        'terms_accepted' => '1',
    ];
}

it('creates a guest booking and confirms it through fake payment gateway', function (): void {
    Mail::fake();

    $excursion = Excursion::factory()->create([
        'status' => ExcursionStatus::Active,
        'base_price_cents' => 200000,
    ]);
    $slot = $excursion->slots()->create([
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHours(2),
        'capacity' => 10,
        'price_cents' => 200000,
        'status' => ExcursionSlotStatus::Active,
    ]);

    $response = $this->post(route('bookings.store'), bookingPayload($slot));

    $booking = Booking::query()->firstOrFail();

    $response->assertRedirect(route('payments.fake.paid', $booking->latestPayment()));

    $this->get(route('payments.fake.paid', $booking->latestPayment()))
        ->assertRedirect(route('payment.success', ['booking' => $booking->uuid]));

    expect($booking->refresh()->status)->toBe(BookingStatus::Paid);
    expect($booking->amount_cents)->toBe(400000);
    expect($booking->passengers)->toHaveCount(2);
    expect($booking->passengers[0]['middle_name'])->toBeNull();
    expect($booking->passengers[0])->not->toHaveKey('no_middle_name');

    Mail::assertSent(BookingPaidMail::class);
});

it('does not limit booking by slot capacity', function (): void {
    $excursion = Excursion::factory()->create([
        'status' => ExcursionStatus::Active,
        'base_price_cents' => 200000,
    ]);
    $slot = $excursion->slots()->create([
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHours(2),
        'capacity' => 1,
        'price_cents' => 200000,
        'status' => ExcursionSlotStatus::Active,
    ]);

    $this->post(route('bookings.store'), bookingPayload($slot, seatsCount: 2))
        ->assertRedirect();

    $booking = Booking::query()->firstOrFail();

    expect($booking->seats_count)->toBe(2);
    expect($booking->amount_cents)->toBe(400000);
});

it('shows schedule without seat counts and opens checkout for a slot', function (): void {
    $excursion = Excursion::factory()->create([
        'title' => 'Листвянка - Большие Коты',
        'status' => ExcursionStatus::Active,
    ]);
    $slot = $excursion->slots()->create([
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(10, 30),
        'capacity' => 1,
        'price_cents' => 110000,
        'status' => ExcursionSlotStatus::Active,
    ]);

    $this->get(route('excursions.show', $excursion))
        ->assertOk()
        ->assertSee('Ближайшие отправки')
        ->assertSee(route('bookings.create', $slot), false)
        ->assertDontSee('Осталось')
        ->assertDontSee('мест:');

    $this->get(route('bookings.create', $slot))
        ->assertOk()
        ->assertSee('Пассажиры')
        ->assertSee('Покупатель билетов')
        ->assertSee('Заказ');
});

it('shows only active future slots on excursion pages', function (): void {
    $excursion = Excursion::factory()->create([
        'status' => ExcursionStatus::Active,
    ]);

    $activeFutureSlot = $excursion->slots()->create([
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(12, 0),
        'capacity' => 10,
        'price_cents' => 120000,
        'status' => ExcursionSlotStatus::Active,
    ]);

    $draftFutureSlot = $excursion->slots()->create([
        'starts_at' => now()->addDays(2)->setTime(10, 0),
        'ends_at' => now()->addDays(2)->setTime(12, 0),
        'capacity' => 10,
        'price_cents' => 120000,
        'status' => ExcursionSlotStatus::Draft,
    ]);

    $closedFutureSlot = $excursion->slots()->create([
        'starts_at' => now()->addDays(3)->setTime(10, 0),
        'ends_at' => now()->addDays(3)->setTime(12, 0),
        'capacity' => 10,
        'price_cents' => 120000,
        'status' => ExcursionSlotStatus::Closed,
    ]);

    $pastActiveSlot = $excursion->slots()->create([
        'starts_at' => now()->subDay()->setTime(10, 0),
        'ends_at' => now()->subDay()->setTime(12, 0),
        'capacity' => 10,
        'price_cents' => 120000,
        'status' => ExcursionSlotStatus::Active,
    ]);

    $this->get(route('excursions.show', $excursion))
        ->assertOk()
        ->assertSee(route('bookings.create', $activeFutureSlot), false)
        ->assertDontSee(route('bookings.create', $draftFutureSlot), false)
        ->assertDontSee(route('bookings.create', $closedFutureSlot), false)
        ->assertDontSee(route('bookings.create', $pastActiveSlot), false);
});
