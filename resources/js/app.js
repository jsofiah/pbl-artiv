import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

Alpine.data('pemesananForm', (tiers, expressFees, defaults) => ({
    selectedTier: defaults.selectedTier,
    quantity: defaults.quantity,
    deadlineOption: defaults.deadlineOption,
    targetDeadline: defaults.targetDeadline,
    briefNote: defaults.briefNote,
    tiers: tiers,
    expressFees: expressFees,

    init() {
        if (this.durationDays < 2 || this.durationDays > this.maxDuration) {
            this.targetDeadline = this.addDays(this.today, 2);
        }
    },

    get today() {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        return d;
    },

    addDays(date, days) {
        const d = new Date(date);
        d.setDate(d.getDate() + days);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    },

    get durationDays() {
        if (!this.targetDeadline) return 0;
        const target = new Date(this.targetDeadline);
        target.setHours(0, 0, 0, 0);
        return Math.round((target - this.today) / (1000 * 60 * 60 * 24));
    },

    get minDate() {
        return this.addDays(this.today, 2);
    },
    get maxDate() {
        return this.addDays(this.today, this.maxDuration);
    },
    get maxDuration() {
        if (!Array.isArray(this.expressFees) || !this.expressFees.length) return 0;
        return Math.max(...this.expressFees.map(f => Number(f.days)));
   },

    parseDuration(name) {
        const match = String(name).match(/\d+/);
        return match ? parseInt(match[0], 10) : 0;
    },

    get selectedFee() {
        if (this.deadlineOption !== 'express') return null;
        if (!Array.isArray(this.expressFees)) return null;
        const days = this.durationDays;
        return this.expressFees.find(f => Number(f.days) === days) || null;
    },

    get expressFee() {
        if (!this.selectedFee) return 0;
        return Number(this.selectedFee.fee) || 0;
    },

    get basePrice() {
        const tier = this.tiers.find(t => t.id == this.selectedTier);
        if (!tier) return 0;
        const price = Number(tier.price) || 0;
        const qty = Number(this.quantity) || 0;
        return price * qty;
    },

    get totalPrice() {
        const base = this.basePrice;
        const express = this.deadlineOption === 'express' ? this.expressFee : 0;
        return base + express;
    },

    get selectedTierName() {
        if (!Array.isArray(this.tiers)) return '-';
        const tier = this.tiers.find(t => t.id == this.selectedTier);
        return tier ? (tier.name || 'Paket') : '-';
    },

    get isFormValid() {
        if (!this.selectedTier) return false;
        if (this.deadlineOption === 'express') {
            if (this.durationDays < 2) return false;
            if (!this.selectedFee) return false;
        }
        if ((this.briefNote || '').trim().length < 10) return false;
        if (!this.quantity || this.quantity < 1) return false;
        return true;
    },

    incrementQty() {
        if (this.quantity < 99) this.quantity++;
    },
    decrementQty() {
        if (this.quantity > 1) this.quantity--;
    },

    formatNumber(n) {
      const num = Number(n);
      if (isNaN(num)) return '0';
      return new Intl.NumberFormat('id-ID').format(num);
  },
}));

window.Alpine = Alpine;
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

Alpine.start();

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */