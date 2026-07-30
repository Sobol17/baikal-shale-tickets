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

window.excursionPurchaseForm = ({
    adultPriceCents,
    childPriceCents,
    initialDate,
    minDate,
    maxDate,
}) => ({
    adultPriceCents: Number(adultPriceCents),
    childPriceCents: Number(childPriceCents),
    date: initialDate || minDate || '',
    minDate: minDate || '',
    maxDate: maxDate || '',
    calendarOpen: false,
    calendarCursor: '',

    init() {
        this.calendarCursor = (this.date || this.minDate).slice(0, 7);
    },

    get formattedDate() {
        return this.formatDate(this.date);
    },

    get monthLabel() {
        const [year, month] = this.calendarCursor.split('-').map(Number);

        return new Intl.DateTimeFormat('ru-RU', {
            month: 'long',
            year: 'numeric',
        }).format(new Date(year, month - 1, 1));
    },

    get calendarDays() {
        const [year, month] = this.calendarCursor.split('-').map(Number);
        const firstDate = new Date(year, month - 1, 1);
        const daysInMonth = new Date(year, month, 0).getDate();
        const firstWeekday = (firstDate.getDay() + 6) % 7;
        const days = [];

        for (let index = 0; index < firstWeekday; index += 1) {
            days.push({
                key: `empty-${index}`,
                date: null,
                enabled: false,
                label: '',
            });
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = this.toDateString(new Date(year, month - 1, day));

            days.push({
                key: date,
                date,
                enabled: this.dateIsEnabled(date),
                label: String(day),
            });
        }

        return days;
    },

    toggleCalendar() {
        this.calendarOpen = !this.calendarOpen;
    },

    closeCalendar() {
        this.calendarOpen = false;
    },

    selectDate(date) {
        if (!this.dateIsEnabled(date)) {
            return;
        }

        this.date = date;
        this.closeCalendar();
    },

    previousMonth() {
        const [year, month] = this.calendarCursor.split('-').map(Number);
        this.calendarCursor = this.toMonthString(new Date(year, month - 2, 1));
    },

    nextMonth() {
        const [year, month] = this.calendarCursor.split('-').map(Number);
        this.calendarCursor = this.toMonthString(new Date(year, month, 1));
    },

    canGoPrevious() {
        return this.calendarCursor > this.minDate.slice(0, 7);
    },

    canGoNext() {
        return this.calendarCursor < this.maxDate.slice(0, 7);
    },

    dateIsEnabled(date) {
        return date >= this.minDate && date <= this.maxDate;
    },

    formatDate(date) {
        if (!date) {
            return '';
        }

        return new Intl.DateTimeFormat('ru-RU').format(this.parseDate(date));
    },

    parseDate(date) {
        const [year, month, day] = date.split('-').map(Number);

        return new Date(year, month - 1, day);
    },

    toDateString(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    },

    toMonthString(date) {
        return this.toDateString(date).slice(0, 7);
    },
});

window.bookingCheckout = ({
    adultPriceCents,
    childPriceCents,
    adultTicketsCount,
    childTicketsCount,
    initialCompanions = [],
}) => ({
    adultPriceCents: Number(adultPriceCents),
    childPriceCents: Number(childPriceCents),
    adultTicketsCount: Number(adultTicketsCount),
    childTicketsCount: Number(childTicketsCount),
    companions: [],

    init() {
        this.companions = Array.isArray(initialCompanions)
            ? initialCompanions.map((companion) => this.normalizeCompanion(companion))
            : [];

        this.normalizeTickets();
    },

    get seatsCount() {
        return this.adultTicketsCount + this.childTicketsCount;
    },

    get companionsCount() {
        return Math.max(0, this.seatsCount - 1);
    },

    get prepaymentCents() {
        return (this.prepaymentForTicket(this.adultPriceCents) * this.adultTicketsCount)
            + (this.prepaymentForTicket(this.childPriceCents) * this.childTicketsCount);
    },

    get fullPriceCents() {
        return (this.adultPriceCents * this.adultTicketsCount)
            + (this.childPriceCents * this.childTicketsCount);
    },

    get remainingCents() {
        return this.fullPriceCents - this.prepaymentCents;
    },

    prepaymentForTicket(fullPriceCents) {
        return Math.round(Number(fullPriceCents) / 1000) * 100;
    },

    normalizeTickets() {
        this.adultTicketsCount = Math.max(0, Math.min(20, Number(this.adultTicketsCount || 0)));
        this.childTicketsCount = Math.max(0, Math.min(20, Number(this.childTicketsCount || 0)));

        if (this.seatsCount < 1) {
            this.adultTicketsCount = 1;
        }

        if (this.seatsCount > 20) {
            const overflow = this.seatsCount - 20;
            this.childTicketsCount = Math.max(0, this.childTicketsCount - overflow);
        }

        this.syncCompanions();
    },

    syncCompanions() {
        while (this.companions.length < this.companionsCount) {
            this.companions.push(this.normalizeCompanion({}));
        }

        if (this.companions.length > this.companionsCount) {
            this.companions = this.companions.slice(0, this.companionsCount);
        }
    },

    normalizeCompanion(companion) {
        return {
            key: companion.key || `${Date.now()}-${Math.random()}`,
            last_name: companion.last_name || '',
            first_name: companion.first_name || '',
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
