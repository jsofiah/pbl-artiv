{{--
    resources/views/components/footer.blade.php
    Pemakaian: <x-footer />
--}}
@props([
    'brand'   => 'Artiv',
    'email'   => 'hello@artiv.com',
    'tagline' => 'Wujudkan Ide Kreatifmu Bersama Desainer Terpercaya',
    'cta'     => 'Siap Memulai Proyek?',
    'ctaUrl'  => null, // default ke route katalog
    'links'   => [
        ['label' => 'Beranda',      'url' => null, 'route' => 'customer.beranda', 'accent' => 'bg-[#D5FC55]'],
        ['label' => 'Katalog Jasa', 'url' => null, 'route' => 'customer.katalog', 'accent' => 'bg-[#745BB8]'],
        ['label' => 'Pesanan Saya', 'url' => null, 'route' => null,              'accent' => 'bg-[#6D28D9]'],
        ['label' => 'Galeri',       'url' => null, 'route' => null,              'accent' => 'bg-[#D5FC55]'],
    ],
])

@php
    // Resolve URL navigasi
    $resolveUrl = function ($link) {
        if (!empty($link['url'])) return $link['url'];
        if (!empty($link['route']) && Route::has($link['route'])) return route($link['route']);

        // Fallback khusus
        if (($link['label'] ?? '') === 'Pesanan Saya') {
            return auth()->check() && Route::has('customer.pesanan')
                ? route('customer.pesanan')
                : (Route::has('login') ? route('login') : '#');
        }
        if (($link['label'] ?? '') === 'Galeri' && Route::has('customer.galeri')) {
            return route('customer.galeri');
        }
        return '#';
    };

    $ctaUrl = $ctaUrl ?? (Route::has('customer.katalog') ? route('customer.katalog') : '#');

    $sosmed = [
        ['label' => 'Instagram', 'href' => 'https://instagram.com/artiv', 'icon' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>'],
        ['label' => 'Facebook',  'href' => 'https://facebook.com/artiv',  'icon' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>'],
        ['label' => 'Twitter',   'href' => 'https://twitter.com/artiv',   'icon' => '<path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/>'],
        ['label' => 'YouTube',   'href' => 'https://youtube.com/@artiv',  'icon' => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>'],
    ];

    // Huruf wordmark: genap = solid, ganjil = outline (saling bergantian)
    $letters = mb_str_split(mb_strtoupper($brand));
    $palette = ['#8B5CF6', '#D5FC55', '#F3F1FA', '#8B5CF6', '#D5FC55'];

    $tickerWords = ['Ruang Kreatifmu', 'Kreatif Bareng ARTIV', 'Desainer Terpercaya', 'Brief Jelas, Hasil Tuntas'];
@endphp

<style>
    /* ===== FOOTER ARTIV ===== */
    .af{--af-ease:cubic-bezier(.22,1,.36,1)}

    /* Ticker */
    .af-marquee{animation:af-marquee 32s linear infinite}
    .af-ticker:hover .af-marquee{animation-play-state:paused}
    @keyframes af-marquee{to{transform:translateX(-50%)}}

    /* Piringan hitam */
    .af-disc{background:repeating-radial-gradient(circle at center,#0a0a0a 0,#0a0a0a 3px,#1d1d22 4px);animation:af-spin 10s linear infinite}
    .af-sheen{background:conic-gradient(from 0deg,transparent 0deg,rgba(255,255,255,.14) 35deg,transparent 80deg,transparent 180deg,rgba(255,255,255,.1) 215deg,transparent 260deg)}
    @keyframes af-spin{to{transform:rotate(360deg)}}
    .af-arm{transform-origin:50% 6px;transition:transform .9s var(--af-ease)}
    .group:hover .af-arm,.group:focus-within .af-arm{transform:rotate(26deg)}

    /* Bintang berputar di kartu CTA */
    .af-star{animation:af-spin 9s linear infinite}

    /* Wordmark raksasa */
    .af-word{display:flex;justify-content:center;align-items:flex-end;white-space:nowrap;line-height:.9;
        font-size:clamp(4rem,min(25vw,30vh),24rem);font-weight:900;letter-spacing:-.05em;user-select:none}
    .af-rv{display:inline-block;transform:translateY(112%);opacity:0}
    .af-in .af-rv{transform:none;opacity:1;
        transition:transform 1.1s var(--af-ease) calc(var(--i,0) * .09s),opacity .5s ease calc(var(--i,0) * .09s)}
    .af-l{--r:-4deg;color:var(--c);-webkit-text-stroke:clamp(2px,.4vw,6px) var(--c);cursor:default}
    .af-l:nth-child(even){--r:4deg}
    .af-l--out{color:transparent}
    .af-in .af-l{transition:transform 1.1s var(--af-ease) calc(var(--i,0) * .09s),opacity .5s ease calc(var(--i,0) * .09s),
        color .45s ease,translate .6s cubic-bezier(.34,1.56,.64,1),rotate .6s cubic-bezier(.34,1.56,.64,1)}
    .af-l:hover{translate:0 -6%;rotate:var(--r)}
    .af-l--fill:hover{color:transparent}
    .af-l--out:hover{color:var(--c)}
    .af-dot{display:inline-block;width:.15em;height:.15em;margin-left:.05em;border-radius:9999px;background:#D5FC55;
        animation:af-bounce 2.2s cubic-bezier(.45,0,.55,1) 1.8s infinite}
    @keyframes af-bounce{0%,100%{translate:0 0;scale:1 1}45%{translate:0 -.22em;scale:.92 1.08}55%{translate:0 -.22em}90%{scale:1.15 .85}}

    @media (prefers-reduced-motion:reduce){
        .af-marquee,.af-disc,.af-star,.af-dot{animation:none!important}
        .af-rv{transform:none!important;opacity:1!important}
        .af-arm{transition:none}
    }
</style>

<footer class="af relative mt-10 flex flex-col overflow-hidden rounded-t-[2.5rem] bg-[#17122B] text-white sm:rounded-t-[3.5rem] lg:min-h-[100svh]">

    {{-- Latar: glow + pola titik --}}
    <div class="pointer-events-none absolute -top-40 left-[18%] size-[30rem] rounded-full bg-[#7C3AED]/25 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute right-[-6rem] bottom-1/3 size-80 rounded-full bg-[#D5FC55]/10 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-0 opacity-[.06]" aria-hidden="true"
         style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:28px 28px"></div>

    {{-- 1. Ticker --}}
    <div class="af-ticker relative overflow-hidden border-b border-white/10 bg-[#745BB8] py-2.5" aria-hidden="true">
        <div class="af-marquee flex w-max">
            @for ($t = 0; $t < 2; $t++)
                <div class="flex shrink-0 items-center gap-8 pr-8 text-sm font-black tracking-widest uppercase sm:text-base">
                    @foreach ($tickerWords as $word)
                        <span>{{ $word }}</span><span class="text-[#D5FC55]">✦</span>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>

    {{-- 2. Wordmark raksasa (solid/outline bergantian, hover membalik, muncul saat di-scroll) --}}
    <div x-data="{ shown: false }"
         x-init="if (!('IntersectionObserver' in window)) { shown = true } else { const io = new IntersectionObserver(([e]) => { if (e.isIntersecting) { shown = true; io.disconnect() } }, { threshold: .2 }); io.observe($el) }"
         :class="shown ? 'af-in' : ''"
         class="relative flex flex-1 items-center justify-center overflow-hidden px-2 py-4"
         role="img" aria-label="{{ $brand }}">
        <div class="af-word" aria-hidden="true">
            @foreach ($letters as $i => $ch)
                <span class="af-rv af-l {{ $i % 2 === 0 ? 'af-l--fill' : 'af-l--out' }}"
                      style="--i:{{ $i }};--c:{{ $palette[$i % count($palette)] }}">{{ $ch }}</span>
            @endforeach
            <span class="af-rv" style="--i:{{ count($letters) }}"><span class="af-dot"></span></span>
        </div>
    </div>

    {{-- 3. Baris tengah: piringan | navigasi | kartu CTA --}}
    <div class="relative mx-auto grid max-w-[88rem] items-center gap-6 px-6 py-6 lg:grid-cols-[minmax(0,210px)_1fr_minmax(0,340px)] lg:gap-12 lg:px-12 lg:py-8">

        {{-- Piringan hitam + tonearm --}}
        <div class="group relative mx-auto hidden aspect-square w-40 lg:block lg:w-full" aria-hidden="true">
            <div class="af-disc relative size-full rounded-full shadow-[0_18px_40px_-12px_rgb(0_0_0/0.8)] ring-1 ring-white/10">
                <div class="af-sheen absolute inset-0 rounded-full"></div>
                <div class="absolute inset-[32%] rounded-full bg-gradient-to-br from-[#D5FC55] via-[#D5FC55] to-[#745BB8]">
                    <div class="absolute top-1/2 left-1/2 size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#17122B]"></div>
                </div>
            </div>
            {{-- Tonearm --}}
            <div class="absolute -top-3 -right-3 size-9 rounded-full bg-gradient-to-br from-zinc-300 to-zinc-500 shadow-lg ring-2 ring-[#17122B]"></div>
            <div class="af-arm absolute -top-1 -right-1 h-[68%] w-2 rounded-full bg-gradient-to-b from-zinc-200 to-zinc-400 shadow-md">
                <span class="absolute -bottom-1 left-1/2 h-3.5 w-3 -translate-x-1/2 rounded-sm bg-[#D5FC55]"></span>
            </div>
        </div>

        {{-- Navigasi --}}
        <nav aria-label="Footer" class="flex flex-col gap-2">
            @foreach ($links as $i => $link)
                @php $right = $i % 2 !== 0; @endphp
                <a href="{{ $resolveUrl($link) }}"
                   class="group relative flex h-12 items-center overflow-hidden border border-white/10 bg-white/5 px-5 transition-colors duration-300 sm:h-14
                          focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D5FC55]">
                    {{-- blok aksen --}}
                    <span class="absolute inset-y-0 {{ $right ? 'right-0' : 'left-0' }} w-12 sm:w-20 {{ $link['accent'] ?? 'bg-neutral-300' }}"></span>
                    {{-- isi lime saat hover --}}
                    <span class="absolute inset-0 bg-[#D5FC55] transition-transform duration-500 ease-out scale-x-0 group-hover:scale-x-100 group-focus-visible:scale-x-100 motion-reduce:transition-none {{ $right ? 'origin-right' : 'origin-left' }}"></span>

                    <span class="relative z-10 w-8 text-sm font-bold tabular-nums text-white/40 transition-colors duration-300 group-hover:text-neutral-900/60 {{ $right ? '' : 'sm:ml-[4.5rem]' }}">
                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="relative z-10 flex-1 text-xl font-semibold italic text-white transition-all duration-300 group-hover:translate-x-1 group-hover:text-neutral-900 sm:text-2xl">
                        {{ $link['label'] }}
                    </span>
                    <svg class="relative z-10 size-5 shrink-0 text-neutral-900 opacity-0 -translate-x-3 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100 {{ $right ? 'mr-14 sm:mr-20' : '' }}"
                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                    </svg>
                </a>
            @endforeach
        </nav>

        {{-- Kartu CTA --}}
        <div class="relative flex min-h-48 flex-col justify-between gap-4 border-2 border-neutral-900 bg-[#D5FC55] p-6 text-neutral-900
                    shadow-[6px_6px_0_0_#745BB8] transition-all duration-300 hover:-translate-x-0.5 hover:-translate-y-1 hover:shadow-[10px_10px_0_0_#745BB8] lg:min-h-60">
            <span class="af-star absolute top-4 right-4 text-2xl text-[#6D28D9]" aria-hidden="true">✳</span>
            <p class="max-w-[12ch] text-2xl leading-tight font-black italic sm:text-3xl">
                {{ $cta }}
            </p>
            <div class="space-y-3">
                <p class="text-sm leading-relaxed text-neutral-700">
                    Tulis ke kami di<br>
                    <a href="mailto:{{ $email }}" class="font-semibold break-all underline decoration-1 underline-offset-4 hover:bg-neutral-900 hover:text-[#D5FC55]">{{ $email }}</a>
                </p>
                <a href="{{ $ctaUrl }}"
                   class="group inline-flex items-center gap-2 rounded-full bg-neutral-900 py-2 pr-2 pl-5 text-sm font-bold text-[#D5FC55] transition hover:bg-[#6D28D9] hover:text-white
                          focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-900">
                    Jelajahi Katalog
                    <span class="flex size-6 items-center justify-center rounded-full bg-[#D5FC55] text-neutral-900 transition-transform duration-300 group-hover:-rotate-45">
                        <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </div>

    {{-- 4. Sosmed + Copyright + Back to top --}}
    <div class="relative border-t border-white/10">
        <div class="mx-auto flex max-w-[88rem] flex-col items-center justify-between gap-3 px-6 py-3 sm:flex-row lg:px-12">

            <div class="flex items-center gap-3">
                @foreach ($sosmed as $s)
                    <a href="{{ $s['href'] }}" target="_blank" rel="noopener" aria-label="{{ $s['label'] }}"
                       class="flex size-9 items-center justify-center rounded-full border border-white/20 text-white/70 transition duration-300
                              hover:-translate-y-1 hover:border-[#D5FC55] hover:bg-[#D5FC55] hover:text-neutral-900">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            {!! $s['icon'] !!}
                        </svg>
                    </a>
                @endforeach
            </div>

            <p class="text-xs text-white/50">
                &copy; {{ date('Y') }} {{ $brand }}. All Rights Reserved.
            </p>

            <button type="button" aria-label="Kembali ke atas"
                    onclick="window.scrollTo({top:0,behavior:'smooth'})"
                    class="group flex items-center gap-2 rounded-full border border-white/20 py-1.5 pr-1.5 pl-4 text-xs font-semibold text-white/80 transition hover:border-[#D5FC55] hover:text-[#D5FC55]">
                Ke atas
                <span class="flex size-6 items-center justify-center rounded-full bg-[#D5FC55] text-neutral-900 transition-transform duration-300 group-hover:-translate-y-0.5">
                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M6 11l6-6 6 6"/>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</footer>