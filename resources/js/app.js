import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.siteSearch = ({ endpoint, initialQuery = '' }) => ({
    isOpen: false,
    query: String(initialQuery || ''),
    endpoint,
    results: [],
    isLoading: false,
    hasSearched: false,
    searchTimer: null,
    abortController: null,

    search() {
        window.clearTimeout(this.searchTimer);

        const query = this.query.trim();

        if (query.length < 2) {
            this.reset();

            return;
        }

        this.isLoading = true;
        this.hasSearched = false;
        this.close();

        this.searchTimer = window.setTimeout(() => {
            this.fetchResults(query);
        }, 250);
    },

    async fetchResults(query) {
        if (this.abortController) {
            this.abortController.abort();
        }

        this.abortController = new AbortController();

        try {
            const url = new URL(this.endpoint, window.location.origin);

            url.searchParams.set('search', query);

            const response = await window.fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                },
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                throw new Error('Search request failed');
            }

            const data = await response.json();

            this.results = Array.isArray(data.items) ? data.items : [];
            this.hasSearched = true;
            this.isLoading = false;
            this.open();
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            this.results = [];
            this.hasSearched = true;
            this.isLoading = false;
            this.open();
        }
    },

    open() {
        if (this.hasSearched) {
            this.isOpen = true;
        }
    },

    close() {
        this.isOpen = false;
    },

    reset() {
        window.clearTimeout(this.searchTimer);

        if (this.abortController) {
            this.abortController.abort();
        }

        this.results = [];
        this.isLoading = false;
        this.hasSearched = false;
        this.close();
    },

    goToFirstResult() {
        const [firstResult] = this.results;

        if (firstResult?.url) {
            window.location.href = firstResult.url;
        }
    },
});

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
            phone: this.formatPhoneNumber(passenger.phone || ''),
        };
    },

    formatPhoneNumber(value) {
        const digits = String(value || '').replace(/\D/g, '');

        if (!digits) {
            return '';
        }

        let normalized = digits;

        if (normalized.startsWith('8')) {
            normalized = `7${normalized.slice(1)}`;
        } else if (!normalized.startsWith('7')) {
            normalized = `7${normalized}`;
        }

        normalized = normalized.slice(0, 11);

        const parts = [
            '+7',
            normalized.slice(1, 4),
            normalized.slice(4, 7),
            normalized.slice(7, 9),
            normalized.slice(9, 11),
        ].filter(Boolean);

        let formatted = parts[0];

        if (parts[1]) {
            formatted += ` ${parts[1]}`;
        }

        if (parts[2]) {
            formatted += ` ${parts[2]}`;
        }

        if (parts[3]) {
            formatted += `-${parts[3]}`;
        }

        if (parts[4]) {
            formatted += `-${parts[4]}`;
        }

        return formatted;
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
