@php
    $startsOn = $excursion->booking_starts_on?->startOfDay();
    $endsOn = $excursion->booking_ends_on?->startOfDay();
    $canBook = $startsOn !== null && $endsOn !== null && ! $endsOn->isBefore(today()) && ! $endsOn->isBefore($startsOn);
    $initialDate = old('excursion_date', $startsOn?->isFuture() ? $startsOn->toDateString() : today()->toDateString());
    $minDate = $startsOn?->isFuture() ? $startsOn->toDateString() : today()->toDateString();
    $maxDate = $excursion->booking_ends_on?->toDateString();
@endphp

@if (! $canBook)
    <div class="alert alert--status">
        Бронирование этой экскурсии сейчас недоступно.
    </div>
@else
<form
    class="purchase-form"
    method="GET"
    action="{{ route('bookings.create') }}"
    x-data="excursionPurchaseForm({
        adultPriceCents: {{ $excursion->adult_price_cents }},
        childPriceCents: {{ $excursion->child_price_cents }},
        initialDate: @js($initialDate),
        minDate: @js($minDate),
        maxDate: @js($maxDate),
    })"
    x-on:click.outside="closeCalendar()"
>
    <input type="hidden" name="excursion_id" value="{{ $excursion->id }}">
    <input type="hidden" name="excursion_date" x-model="date">

    <div class="form-field purchase-date">
        <span class="form-label">Дата экскурсии</span>
        <button
            class="form-control purchase-date__button"
            type="button"
            x-on:click="toggleCalendar()"
            x-bind:aria-expanded="calendarOpen.toString()"
        >
            <span x-text="formattedDate"></span>
        </button>

        <div class="calendar-popover" x-cloak x-show="calendarOpen" x-transition.opacity>
            <div class="calendar-popover__head">
                <button type="button" x-on:click="previousMonth()" x-bind:disabled="! canGoPrevious()">Назад</button>
                <strong x-text="monthLabel"></strong>
                <button type="button" x-on:click="nextMonth()" x-bind:disabled="! canGoNext()">Вперед</button>
            </div>

            <div class="calendar-grid calendar-grid--weekdays">
                <span>Пн</span>
                <span>Вт</span>
                <span>Ср</span>
                <span>Чт</span>
                <span>Пт</span>
                <span>Сб</span>
                <span>Вс</span>
            </div>

            <div class="calendar-grid">
                <template x-for="day in calendarDays" :key="day.key">
                    <button
                        type="button"
                        class="calendar-day"
                        x-bind:class="{ 'is-empty': ! day.date, 'is-selected': day.date === date }"
                        x-bind:disabled="! day.date || ! day.enabled"
                        x-on:click="selectDate(day.date)"
                    >
                        <span x-text="day.label"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <div class="purchase-form__prices">
        <span>Взрослый: {{ number_format($excursion->adult_price_cents / 100, 0, ',', ' ') }} ₽</span>
        <span>Детский: {{ number_format($excursion->child_price_cents / 100, 0, ',', ' ') }} ₽</span>
    </div>

    <button class="button button--primary" type="submit">Купить билет</button>
</form>
@endif
