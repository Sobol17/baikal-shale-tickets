import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.bookingCheckout = ({ priceCents, initialSeats, initialPassengers }) => ({
    priceCents: Number(priceCents),
    seats: Number(initialSeats || 1),
    passengers: [],

    init() {
        this.passengers = Array.isArray(initialPassengers)
            ? initialPassengers.map((passenger) => this.normalizePassenger(passenger))
            : [];

        this.syncPassengers();
    },

    get totalCents() {
        return this.priceCents * this.seats;
    },

    syncPassengers() {
        this.seats = Math.max(1, Math.min(20, Number(this.seats || 1)));

        while (this.passengers.length < this.seats) {
            this.passengers.push(this.emptyPassenger());
        }

        if (this.passengers.length > this.seats) {
            this.passengers = this.passengers.slice(0, this.seats);
        }
    },

    emptyPassenger() {
        return this.normalizePassenger({});
    },

    normalizePassenger(passenger) {
        const noMiddleName = passenger.no_middle_name === true || passenger.no_middle_name === 1 || passenger.no_middle_name === '1';

        return {
            key: passenger.key || `${Date.now()}-${Math.random()}`,
            ticket_type: passenger.ticket_type || 'adult',
            last_name: passenger.last_name || '',
            first_name: passenger.first_name || '',
            middle_name: passenger.middle_name || '',
            no_middle_name: noMiddleName,
            citizenship: passenger.citizenship || 'Россия',
            phone: passenger.phone || '',
        };
    },

    money(cents) {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            maximumFractionDigits: 0,
        }).format(cents / 100);
    },
});

Alpine.start();
