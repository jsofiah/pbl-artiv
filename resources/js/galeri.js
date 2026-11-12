const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const clamp = (v, a, b) => (v < a ? a : v > b ? b : v);
const easeOut = (t) => 1 - Math.pow(1 - t, 3);
const pad2 = (n) => (n < 10 ? '0' : '') + n;

function pose(p, i) {
    const enter = i === 0 ? 1 : clamp(p - (i - 1), 0, 1);
    const depth = clamp(p - i, 0, 3);
    const e = easeOut(enter);
    return {
        y: (1 - e) * 130 - depth * 3.2,
        scale: (1 - depth * 0.07) * (0.9 + 0.1 * e),
        rx: (1 - e) * 22 - depth * 9,
        rz: (1 - e) * (i % 2 ? 5 : -5),
        shade: Math.min(depth, 2) * 0.34,
        opacity: depth > 2.6 ? clamp((3 - depth) / 0.4, 0, 1) : 1,
    };
}

function initTilt() {
    const root = document.getElementById('gt-root');
    if (!root) return;

    const cards = Array.from(root.querySelectorAll('.gt-card'));
    const n = cards.length;
    const count = document.getElementById('gt-count');
    const rail = document.getElementById('gt-rail');
    let last = -1, p = 0;

    const paint = () => {
        cards.forEach((el, i) => {
            const q = pose(p, i);
            el.style.transform =
                `translate3d(0,${q.y.toFixed(3)}%,0) perspective(1400px) rotateX(${q.rx.toFixed(3)}deg) rotateZ(${q.rz.toFixed(3)}deg) scale(${q.scale.toFixed(4)})`;
            el.style.opacity = q.opacity;
            el.querySelector('.gt-shade').style.opacity = q.shade;
        });
        const idx = Math.round(p);
        if (idx !== last) {
            last = idx;
            count.textContent = pad2(idx + 1);
        }
        rail.style.transform = `scaleX(${(n > 1 ? p / (n - 1) : 1).toFixed(4)})`;
    };

    ScrollTrigger.create({
        trigger: root,
        start: 'top top',
        end: 'bottom bottom',
        onUpdate: (self) => {
            p = self.progress * Math.max(n - 1, 0);
            paint();
        },
        snap: n > 1 ? { snapTo: 1 / (n - 1), duration: { min: 0.25, max: 0.7 }, delay: 0.08, ease: 'power2.inOut' } : undefined,
    });
    paint();

    gsap.from('#gt-heading .gt-w', { yPercent: 110, opacity: 0, rotate: 4, duration: 1.1, stagger: 0.06, ease: 'expo.out', delay: 0.1 });
    gsap.from('#gt-deck', { y: 50, opacity: 0, duration: 1.2, ease: 'expo.out', delay: 0.25 });
}

function initFrames() {
    const wrap = document.getElementById('gt-frames');
    if (!wrap) return;

    const items = Array.from(wrap.querySelectorAll('.gt-frame'));
    const total = items.length;
    if (!total) return;

    const hoverSize = parseFloat(wrap.dataset.hoverSize) || 2;
    let cols = 3, rows = 1, active = null;

    const timers = new Map();
    const inViewport = new Set();
    const revealTimers = new Map();
    const REVEAL_DELAY = 900;

    const startSlideshow = (frameEl) => {
        if (frameEl.dataset.revealed !== 'true') return;
        if (timers.has(frameEl)) return;
        if (!inViewport.has(frameEl)) return;

        const imgs = Array.from(frameEl.querySelectorAll('.gt-frame-img'));
        if (imgs.length < 2) return;

        let idx = imgs.findIndex((el) => el.dataset.shown === 'true');
        if (idx < 0) idx = 0;

        timers.set(frameEl, setInterval(() => {
            imgs[idx].dataset.shown = 'false';
            idx = (idx + 1) % imgs.length;
            imgs[idx].dataset.shown = 'true';
        }, 1200));
    };

    const stopSlideshow = (frameEl) => {
        if (timers.has(frameEl)) {
            clearInterval(timers.get(frameEl));
            timers.delete(frameEl);
        }
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const el = entry.target;
            if (entry.isIntersecting) {
                inViewport.add(el);
                startSlideshow(el);
            } else {
                inViewport.delete(el);
                stopSlideshow(el);
            }
        });
    }, { rootMargin: '100px' });
    items.forEach((el) => observer.observe(el));

    const track = (count, hoveredIdx) =>
        Array.from({ length: count }, (_, k) =>
            `minmax(0, ${hoveredIdx === k ? hoverSize : 1}fr)`).join(' ');

    const applyTracks = () => {
        const hc = active === null ? null : active % cols;
        const hr = active === null ? null : Math.floor(active / cols);
        wrap.style.gridTemplateColumns = track(cols, hc);
        wrap.style.gridTemplateRows = track(rows, hr);
        items.forEach((el, i) => { el.dataset.active = i === active ? 'true' : 'false'; });
    };

    const layout = () => {
        const w = window.innerWidth;
        cols = w >= 768 ? 3 : 2;
        rows = Math.ceil(total / cols);
        const rowH = w >= 1024 ? 230 : w >= 768 ? 200 : 170;
        wrap.style.gridAutoRows = '';
        const gap = parseFloat(getComputedStyle(wrap).rowGap) || 4;
        wrap.style.height = (rows * rowH + (rows - 1) * gap) + 'px';

        items.forEach((el, i) => {
            const rest = i === total - 1 ? cols - (i % cols) : 1;
            el.style.gridColumn = rest > 1 ? `span ${rest}` : '';
        });
        applyTracks();
    };

    const reveal = (el) => {
        el.dataset.revealed = 'true';
        startSlideshow(el);
    };

    const set = (i) => {
        revealTimers.forEach((t, el) => {
            if (i === null || el !== items[i]) {
                clearTimeout(t);
                revealTimers.delete(el);
            }
        });

        if (i !== null) {
            const el = items[i];
            if (el.dataset.revealed === 'true') {
                startSlideshow(el);
            } else if (!revealTimers.has(el)) {
                revealTimers.set(el, setTimeout(() => {
                    revealTimers.delete(el);
                    reveal(el);
                }, REVEAL_DELAY));
            }
        }

        if (active !== i) {
            active = i;
            applyTracks();
        }
    };

    if (window.matchMedia('(hover: none)').matches) {
        items.forEach((el) => { el.dataset.revealed = 'true'; });
    }

    items.forEach((el, i) => {
        el.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') set(i); });
        el.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') set(null); });
        el.addEventListener('focus', () => set(i));
        el.addEventListener('blur', () => set(null));
    });
    wrap.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') set(null); });

    let rt;
    window.addEventListener('resize', () => {
        clearTimeout(rt);
        rt = setTimeout(layout, 120);
    });
    layout();
}

function initFrameReveal() {
    const items = gsap.utils.toArray('.gt-item');
    if (!items.length) return;

    gsap.set(items, { opacity: 0, y: 60, scale: 0.94 });
    ScrollTrigger.batch(items, {
        start: 'top 90%',
        once: true,
        onEnter: (batch) => gsap.to(batch, {
            opacity: 1, y: 0, scale: 1,
            duration: 0.9, ease: 'expo.out', stagger: 0.09,
            overwrite: true, clearProps: 'transform',
        }),
    });
}

function initLightbox() {
    const box = document.getElementById('gt-lightbox');
    if (!box) return;

    const img = document.getElementById('gt-lb-img');
    const ttl = document.getElementById('gt-lb-title');
    const meta = document.getElementById('gt-lb-meta');

    const close = () => {
        box.classList.add('hidden');
        box.classList.remove('flex');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.gt-item').forEach((btn) => {
        btn.addEventListener('click', () => {
            let images = [];
            try { images = JSON.parse(btn.dataset.images || '[]'); } catch (e) { images = []; }
            if (!images.length && btn.dataset.img) images = [btn.dataset.img];

            const shown = btn.querySelector('.gt-frame-img[data-shown="true"]');
            const startSrc = shown?.src || images[0] || '';

            img.src = startSrc;
            img.alt = btn.dataset.title;
            ttl.textContent = btn.dataset.title;
            meta.textContent = btn.dataset.cat + ' · oleh ' + btn.dataset.designer;

            box.classList.remove('hidden');
            box.classList.add('flex');
            document.body.style.overflow = 'hidden';

            if (window.gsap) {
                gsap.fromTo(img, { scale: 0.92, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.45, ease: 'expo.out' });
            }
        });
    });

    box.addEventListener('click', (e) => { if (e.target === box) close(); });
    document.getElementById('gt-lb-close')?.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
}

function waitForGSAP(cb, tries = 0) {
    if (window.gsap && window.ScrollTrigger) return cb();
    if (tries < 40) return setTimeout(() => waitForGSAP(cb, tries + 1), 100);
    console.warn('GSAP / ScrollTrigger tidak ter-load, memakai tampilan statis.');
    document.getElementById('gt-root')?.classList.add('gt--static');
}

function initGaleri() {
    initLightbox();
    initFrames();

    if (reduceMotion) {
        document.getElementById('gt-root')?.classList.add('gt--static');
        return;
    }

    waitForGSAP(() => {
        gsap.registerPlugin(ScrollTrigger);
        initTilt();
        initFrameReveal();
    });
}

window.initGaleri = initGaleri;