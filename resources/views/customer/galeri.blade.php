@extends('layouts.customer')

@section('title', 'Galeri Karya')

@section('content')
@php
    $accents = ['#D5FC55', '#C4B5FD', '#F9A8D4', '#A5F3FC', '#FDE68A'];
    $n = count($featured);
@endphp

<div class="artiv-gallery">

    @if (empty($featured) && empty($works))
        <div class="min-h-[70vh] flex flex-col items-center justify-center px-4 text-center">
            <div class="w-20 h-20 rounded-full bg-violet-100 text-[#6D28D9] flex items-center justify-center mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Galeri masih kosong</h2>
            <p class="text-slate-500 mb-6 max-w-md">Karya desain terbaik dari kreator kami akan segera tampil di sini.</p>
            <a href="{{ route('customer.katalog') }}"
               class="inline-flex items-center gap-2 bg-[#6D28D9] hover:bg-[#5B21B6] text-white font-bold px-6 py-3 rounded-full transition">
                Jelajahi Katalog
            </a>
        </div>
    @else

        {{-- ==================== 1. TILT STACK ==================== --}}
        <section id="gt-root" class="gt-track artiv-bleed" style="height: calc(100svh * {{ max($n, 1) }});">
            <div class="gt-stage" style="height:100svh;">
                <header class="gt-head">
                    <div>
                        <p class="gt-eyebrow">Portofolio · ARTIV</p>
                        <h1 class="gt-h" id="gt-heading">
                            @foreach (explode(' ', 'Karya terbaik dari desainer kami') as $w)
                                <span class="gt-wrap"><span class="gt-w">{{ $w }}</span>&nbsp;</span>
                            @endforeach
                        </h1>
                    </div>
                    <div class="gt-count" aria-hidden="true">
                        <b id="gt-count">01</b> / {{ sprintf('%02d', $n) }}
                        <span class="gt-rail"><i id="gt-rail"></i></span>
                    </div>
                </header>

                <div class="gt-deck" id="gt-deck">
                    @foreach ($featured as $i => $c)
                        <article class="gt-card"
                                 style="z-index: {{ $i + 1 }}; --gt-accent: {{ $c['accent'] ?? '#D5FC55' }};"
                                 aria-label="{{ sprintf('%02d', $i + 1) }} dari {{ sprintf('%02d', $n) }}: {{ $c['title'] }}">
                            <img src="{{ $c['image'] }}" alt="{{ $c['title'] }}" draggable="false"
                                 loading="{{ $i < 2 ? 'eager' : 'lazy' }}">
                            <div class="gt-body">
                                <div class="gt-top">
                                    <span class="gt-num">{{ sprintf('%02d', $i + 1) }}</span>
                                    <span class="gt-kick">{{ $c['category'] }}</span>
                                </div>
                                <div class="gt-bottom">
                                    <div class="gt-copy">
                                        <h2 class="gt-title">{{ $c['title'] }}</h2>
                                        <p class="gt-desc">{{ $c['description'] }}</p>
                                        @if (!empty($c['tags']))
                                            <ul class="gt-tags">
                                                @foreach ($c['tags'] as $t)<li>{{ $t }}</li>@endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="gt-buy">
                                        <div>
                                            <div class="gt-designer">{{ $c['designer'] }}</div>
                                            <span class="gt-note">Desainer</span>
                                        </div>
                                        <a class="gt-cta" href="{{ $c['href'] ?? route('customer.katalog') }}">
                                            Beli sekarang
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                                <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="gt-shade"></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ==================== 2. FRAME GRID ==================== --}}
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="text-center mb-12">
                <span class="inline-block px-5 py-2 rounded-full bg-[#D5FC55] text-neutral-900 text-xs font-bold tracking-widest uppercase mb-5">
                    Semua Karya
                </span>
                <h2 class="text-3xl md:text-5xl font-black tracking-tight text-slate-900 mb-3">
                    Galeri Hasil Desain
                </h2>
                <p class="text-slate-500 max-w-2xl mx-auto">
                    Kumpulan hasil kerja desainer ARTIV untuk berbagai kebutuhan, mulai dari logo, poster, sampai branding.
                </p>
            </div>

            <div class="gt-frames-wrap">
                <div id="gt-frames" class="gt-frames" data-hover-size="2" aria-label="Galeri hasil desain">
                    @foreach ($works as $i => $w)
                        @php
                            $imgs = $w['images'] ?? [];
                            $len  = max(mb_strlen($w['title']), 1);
                            $step = max(12, min(55, intdiv(900, $len)));
                            $c    = 0;
                        @endphp
                        <button type="button"
                                class="gt-item gt-frame"
                                style="--acc: {{ $accents[$i % count($accents)] }}"
                                data-images='@json($imgs)'
                                data-title="{{ $w['title'] }}"
                                data-cat="{{ $w['category'] }}"
                                data-designer="{{ $w['designer'] }}">

                            @foreach ($imgs as $k => $src)
                                <img src="{{ $src }}" alt="{{ $w['title'] }}"
                                     loading="lazy" draggable="false"
                                     class="gt-frame-img"
                                     data-shown="{{ $k === 0 ? 'true' : 'false' }}">
                            @endforeach

                            <span class="gt-frame-dim" aria-hidden="true"></span>

                            <span class="gt-frame-num">{{ sprintf('%02d', $i + 1) }}</span>
                            <span class="gt-frame-badge">{{ $w['category'] }}</span>
                            <span class="gt-frame-shade"></span>

                            <span class="gt-frame-title" aria-label="{{ $w['title'] }}">
                                <span class="gt-frame-title-inner" aria-hidden="true">
                                    @foreach (explode(' ', $w['title']) as $word)
                                        <span class="gt-frame-title-word">
                                            @foreach (mb_str_split($word) as $ch)
                                                <span class="gt-frame-title-char" style="transition-delay: {{ $c++ * $step }}ms">{{ $ch }}</span>
                                            @endforeach
                                        </span>
                                    @endforeach
                                </span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- CTA --}}
            <div class="relative rounded-3xl overflow-hidden mt-20" style="background: linear-gradient(135deg, #6D28D9 0%, #745BB8 100%);">
                <div class="px-8 py-12 md:py-14 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="max-w-2xl text-center md:text-left">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-3">Suka dengan hasilnya? Giliran idemu.</h2>
                        <p class="text-white/80">Pilih jasa desain dan biarkan desainer ARTIV mewujudkannya.</p>
                    </div>
                    <a href="{{ route('customer.katalog') }}"
                       class="shrink-0 inline-flex items-center gap-2 bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-8 py-4 rounded-full transition shadow-lg">
                        Jelajahi Katalog
                    </a>
                </div>
            </div>
        </section>

        {{-- Lightbox --}}
        <div id="gt-lightbox" class="fixed inset-0 z-[900] hidden items-center justify-center bg-[#17122B]/90 backdrop-blur-sm p-4" role="dialog" aria-modal="true">
            <button type="button" id="gt-lb-close" class="absolute top-5 right-5 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white text-xl" aria-label="Tutup">✕</button>
            <figure class="max-w-4xl w-full">
                <img id="gt-lb-img" src="" alt="" class="w-full max-h-[78vh] object-contain rounded-2xl shadow-2xl">
                <figcaption class="mt-4 text-center text-white">
                    <p id="gt-lb-title" class="font-bold text-lg"></p>
                    <p id="gt-lb-meta" class="text-white/60 text-sm"></p>
                </figcaption>
            </figure>
        </div>

    @endif
</div>
@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,600;1,700&display=swap" rel="stylesheet">
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        import('{{ Vite::asset('resources/js/galeri.js') }}').then(m => m.initGaleri());
    });
</script>
@endpush