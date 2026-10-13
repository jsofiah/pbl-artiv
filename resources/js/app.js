import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.Pusher = Pusher;
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

Alpine.data('pemesananForm', (tiersData = [], expressFeesData = [], initialData = {}) => ({
    tiers: [],
    expressFees: [],
    selectedTier: '',
    quantity: 1,
    deadlineOption: 'default',
    targetDeadline: '',
    briefNote: '',
    hasTiers: true,
    productPrice: 0,

    init() {
        this.setup({
            tiers: tiersData,
            expressFees: expressFeesData,
            selectedTier: initialData.selectedTier,
            quantity: initialData.quantity,
            deadlineOption: initialData.deadlineOption,
            targetDeadline: initialData.targetDeadline,
            briefNote: initialData.briefNote,
            hasTiers: tiersData.length > 0,
            productPrice: initialData.productPrice ?? 0,
        });
    },

    setup(data) {
        this.tiers = (data.tiers || []).map(t => ({
            ...t,
            id: String(t.id),
            price: Number(t.price),
        }));
        this.expressFees = (data.expressFees || []).map(f => ({
            ...f,
            days: Number(f.days),
            fee: Number(f.fee),
        }));
        this.selectedTier = data.selectedTier != null ? String(data.selectedTier) : '';
        this.quantity = Number(data.quantity) || 1;
        this.deadlineOption = data.deadlineOption || 'default';
        this.briefNote = data.briefNote || '';
        this.hasTiers = data.hasTiers ?? true;
        this.productPrice = Number(data.productPrice) || 0;

        // set targetDeadline setelah semua data siap
        const fallback = this.addDays(this.today, 2);
        this.targetDeadline = data.targetDeadline || fallback;

        // koreksi kalau di luar range
        if (this.durationDays < 2) {
            this.targetDeadline = fallback;
        } else if (this.maxDuration > 0 && this.durationDays > this.maxDuration) {
            this.targetDeadline = this.addDays(this.today, this.maxDuration);
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

Alpine.start();