import './bootstrap';

import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import gsap from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';

window.Alpine = Alpine;
window.Pusher = Pusher;
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

Alpine.data('pemesananForm', () => ({
    tiers: [],
    expressFees: [],
    selectedTier: '',
    quantity: 1,
    deadlineOption: 'default',
    targetDeadline: '',
    briefNote: '',
    hasTiers: true,
    productPrice: 0,

    setup(data) {
        this.tiers = data.tiers || [];
        this.expressFees = data.expressFees || [];
        this.selectedTier = data.selectedTier || '';
        this.quantity = data.quantity || 1;
        this.deadlineOption = data.deadlineOption || 'default';
        this.targetDeadline = data.targetDeadline || '';
        this.briefNote = data.briefNote || '';
        this.hasTiers = data.hasTiers ?? true;
        this.productPrice = Number(data.productPrice) || 0;

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

    get tierPrice() {
        if (this.hasTiers) {
            const tier = this.tiers.find(t => t.id == this.selectedTier);
            return tier ? (Number(tier.price) || 0) : 0;
        }
        return this.productPrice;
    },

    get basePrice() {
        return this.tierPrice * (Number(this.quantity) || 0);
    },

    get totalPrice() {
        const express = this.deadlineOption === 'express' ? this.expressFee : 0;
        return this.basePrice + express;
    },

    get selectedTierName() {
        if (!this.hasTiers) return 'Standar';
        if (!Array.isArray(this.tiers)) return '-';
        const tier = this.tiers.find(t => t.id == this.selectedTier);
        return tier ? (tier.name || 'Paket') : '-';
    },

    get isFormValid() {
        if (this.hasTiers && !this.selectedTier) return false;
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

gsap.registerPlugin(ScrollTrigger);

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