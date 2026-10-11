@extends('layouts.customer')

@section('title', 'Beranda')

@section('content')

{{-- ==================== 1. LOADING SCREEN ==================== --}}
<div id="artiv-intro" class="artiv-intro" aria-hidden="true">
    <div class="artiv-intro-logo">ARTIV<span>.</span></div>
    <div class="artiv-intro-caption">IDEA IN MOTION</div>
</div>

<div class="artiv-homepage relative">

    {{-- Doodle dekoratif --}}
    <svg class="artiv-doodles pointer-events-none absolute inset-x-0 top-0 h-full w-full overflow-visible"
         viewBox="0 0 1200 2200" preserveAspectRatio="none" aria-hidden="true">
        <path class="artiv-doodle-path" fill="none" stroke="#7C3AED" stroke-width="2"
              d="M-80 180 C120 70 150 330 330 230 S520 90 620 210 S850 360 980 200 S1120 110 1280 240" />
        <path class="artiv-doodle-path artiv-doodle-path--two" fill="none" stroke="#D5FC55" stroke-width="2"
              d="M-80 760 C120 900 220 650 380 790 S650 930 790 770 S1040 650 1280 820" />
        <path class="artiv-doodle-path artiv-doodle-path--three" fill="none" stroke="#8B5CF6" stroke-width="2"
              d="M-80 1380 C120 1240 240 1530 420 1390 S650 1240 820 1410 S1070 1510 1280 1350" />
    </svg>

    {{-- ==================== 2. HERO ==================== --}}
    <section class="artiv-bleed relative overflow-hidden flex items-center pt-2 pb-12 lg:pt-4 lg:pb-16">

        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-[#F3F1FA] via-[#E9E3FB] to-[#F3F1FA]"></div>

        {{-- Blob blur latar --}}
        <div class="absolute -z-10 top-1/4 right-[8%] w-72 h-72 lg:w-[28rem] lg:h-[28rem] rounded-full bg-[#7C3AED]/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -z-10 bottom-[10%] left-[4%] w-56 h-56 rounded-full bg-[#D5FC55]/25 blur-3xl pointer-events-none"></div>

        {{-- Ornamen --}}
        <div class="artiv-ornament absolute top-[18%] left-[6%] text-2xl text-[#6D28D9]/40">✦</div>
        <div class="artiv-ornament absolute top-[14%] right-[10%] text-2xl text-[#a8cc2e]" style="animation-delay: -1s;">✳</div>
        <div class="artiv-ornament absolute bottom-[22%] left-[8%] text-xl text-[#EC4899]" style="animation-delay: -2s;">✦</div>
        <div class="artiv-ornament absolute bottom-[26%] right-[6%] text-2xl text-[#7C3AED]" style="animation-delay: -1.5s;">✳</div>

        <div class="relative mx-auto max-w-7xl px-5 sm:px-6 lg:px-8 w-full">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">

                {{-- Teks --}}
                <div class="text-center lg:text-left">

                    <span class="artiv-reveal inline-flex items-center gap-2.5 pl-3 pr-4 py-2 rounded-full bg-white/70 backdrop-blur border border-violet-200/80 text-[#6D28D9] text-xs font-semibold mb-7 shadow-sm shadow-violet-200/60" style="--i:0">
                        <span class="relative flex w-2.5 h-2.5">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-[#D5FC55] opacity-80 animate-ping"></span>
                            <span class="relative inline-flex w-2.5 h-2.5 rounded-full bg-[#7C3AED]"></span>
                        </span>
                        Platform Jasa Kreatif untuk Kebutuhanmu
                    </span>

                    <h1 class="artiv-reveal text-5xl sm:text-6xl md:text-7xl lg:text-[5.5rem] font-black leading-[1.02] tracking-[-0.035em] text-slate-900 mb-6" style="--i:1">
                        Hai, ini<br>
                        <span class="relative inline-block">
                            <svg class="absolute left-[-2%] bottom-[2%] w-[104%] h-[.32em] z-0" viewBox="0 0 300 20" preserveAspectRatio="none" fill="none" aria-hidden="true">
                                <path class="artiv-underline" d="M4 12 C50 4 110 17 170 9 S260 6 296 11" stroke="#D5FC55" stroke-width="12" stroke-linecap="round"/>
                            </svg>
                            <span class="relative z-10 bg-gradient-to-br from-[#6D28D9] via-[#8B5CF6] to-[#7C3AED] bg-clip-text text-transparent">ARTIV</span>
                        </span>
                    </h1>

                    <p class="artiv-reveal text-base sm:text-lg text-slate-600 leading-relaxed mb-9 max-w-[34rem] mx-auto lg:mx-0" style="--i:2">
                        Tempat ide kreatifmu bertemu desainer terpercaya dan semuanya dimulai dari sini.
                    </p>

                    <div class="artiv-reveal flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 sm:gap-5" style="--i:3">
                        <a href="{{ route('customer.katalog') }}"
                           class="group inline-flex items-center gap-4 bg-[#D5FC55] hover:bg-[#cbf546] text-neutral-900 font-bold pl-8 pr-2.5 py-2.5 rounded-full transition-all duration-300 shadow-lg shadow-[#D5FC55]/50 hover:shadow-xl hover:shadow-[#D5FC55]/60 hover:-translate-y-0.5 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#6D28D9]/40">
                            Pesan Jasa Sekarang
                            <span class="w-11 h-11 rounded-full bg-neutral-900 text-[#D5FC55] flex items-center justify-center transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-rotate-45">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                                </svg>
                            </span>
                        </a>
                        <a href="#artiv-how-scroll"
                           class="group inline-flex items-center gap-3 px-3 py-2 rounded-full font-semibold text-[#6D28D9] hover:text-[#5B21B6] transition focus:outline-none focus-visible:ring-4 focus-visible:ring-[#6D28D9]/30">
                            <span class="w-11 h-11 rounded-full border border-[#6D28D9]/30 bg-white/60 flex items-center justify-center transition group-hover:bg-white group-hover:border-[#6D28D9]/60">
                                <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-y-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M6 13l6 6 6-6"/>
                                </svg>
                            </span>
                            Lihat cara kerja
                        </a>
                    </div>
                </div>

                {{-- Visual: tablet desain + stylus + kartu melayang --}}
                <div class="relative h-[360px] sm:h-[440px] lg:h-[540px] w-full" aria-hidden="true">

                    {{-- Glow --}}
                    <div class="absolute inset-[8%] rounded-full bg-[#7C3AED]/20 blur-3xl pointer-events-none"></div>

                    {{-- Bentuk dekoratif --}}
                    <div class="artiv-hero-card absolute left-[1%] top-[26%] w-[13%] aspect-square bg-gradient-to-br from-[#8B5CF6] to-[#6D28D9] z-10"
                         style="clip-path:polygon(0 0,100% 38%,34% 100%);--d:.5s"></div>
                    <div class="artiv-hero-card absolute left-[34%] top-[6%] w-[9%] aspect-square bg-gradient-to-br from-[#A78BFA] to-[#7C3AED] z-0"
                         style="clip-path:polygon(0 0,100% 38%,34% 100%);--d:.6s"></div>
                    <div class="artiv-hero-card absolute right-[22%] bottom-[2%] w-[12%] aspect-square bg-gradient-to-br from-[#8B5CF6] to-[#5B21B6] z-10"
                         style="clip-path:polygon(0 0,100% 38%,34% 100%);--d:.7s"></div>

                    {{-- Pesawat kertas outline (lime) --}}
                    <svg class="artiv-hero-card absolute left-[16%] top-[12%] w-[12%] z-20" viewBox="0 0 60 60" fill="none" style="--d:.55s">
                        <path d="M6 30 L54 8 L40 52 L28 36 Z M28 36 L54 8" stroke="#a8cc2e" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                    </svg>

                    {{-- Bintang lime --}}
                    <svg class="artiv-hero-card absolute left-[24%] bottom-[2%] w-[9%] z-20" viewBox="0 0 50 50" fill="none" style="--d:.8s">
                        <path d="M25 4 L30 19 L46 20 L33 30 L38 46 L25 37 L12 46 L17 30 L4 20 L20 19 Z" stroke="#a8cc2e" stroke-width="2.5" stroke-linejoin="round"/>
                    </svg>

                    <div class="artiv-ornament absolute right-[4%] top-[28%] text-xl text-[#7C3AED]" style="animation-delay:-1s">✦</div>
                    <div class="artiv-ornament absolute left-[44%] bottom-[0%] text-lg text-[#a8cc2e]" style="animation-delay:-2s">✳</div>

                    @php
                        $heroProduct1 = $heroProducts[0] ?? null;
                        $heroProduct2 = $heroProducts[1] ?? null;
                        $heroImg1 = $heroProduct1 ? \App\Helpers\R2Helper::url($heroProduct1->thumbnail_url) : null;
                        $heroImg2 = $heroProduct2 ? \App\Helpers\R2Helper::url($heroProduct2->thumbnail_url) : null;
                    @endphp

                    {{-- Kartu melayang 1: Foto produk terbaru --}}
                    <div class="artiv-hero-card absolute left-[40%] top-0 w-[27%] -rotate-6 hover:rotate-0 hover:scale-105 transition-transform duration-500 z-30" style="--d:.45s">
                        <div class="rounded-2xl bg-white p-1.5 shadow-xl shadow-violet-900/20">
                            <div class="aspect-[4/3] rounded-xl overflow-hidden bg-gradient-to-br from-violet-200 to-violet-300">
                                @if($heroImg1)
                                    <img src="{{ $heroImg1 }}"
                                        alt="{{ $heroProduct1->name }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-violet-200 to-violet-300">
                                        <span class="text-3xl font-extrabold text-[#6D28D9]/40">
                                            {{ $heroProduct1 ? strtoupper(substr($heroProduct1->name, 0, 1)) : 'A' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-1.5 h-1.5 w-2/3 rounded-full bg-violet-100"></div>
                            <div class="mt-1 mb-0.5 h-1.5 w-1/3 rounded-full bg-violet-100"></div>
                        </div>
                    </div>

                    {{-- Kartu melayang 2: Foto produk terbaru ke-2 --}}
                    <div class="artiv-hero-card absolute right-[14%] top-[4%] w-[19%] rotate-6 hover:rotate-0 hover:scale-105 transition-transform duration-500 z-30" style="--d:.55s">
                        <div class="rounded-2xl bg-white p-1.5 shadow-xl shadow-violet-900/20">
                            <div class="aspect-[3/4] rounded-xl bg-gradient-to-br from-[#8B5CF6] via-[#7C3AED] to-[#D5FC55] flex items-end p-2 relative overflow-hidden">
                                @if($heroImg2)
                                    <img src="{{ $heroImg2 }}"
                                        alt="{{ $heroProduct2->name }}"
                                        class="absolute inset-0 w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                @endif
                                <span class="relative z-10 text-[8px] sm:text-[10px] font-black text-white leading-tight">
                                    {{ $heroProduct2 ? \Illuminate\Support\Str::limit($heroProduct2->name, 20) : 'Idea in motion' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tablet --}}
                    <div class="artiv-hero-card absolute left-[6%] right-[10%] top-[24%] bottom-[7%] -rotate-3 hover:rotate-0 transition-transform duration-500 z-20" style="--d:.2s">
                        <div class="w-full h-full rounded-[1.4rem] lg:rounded-[1.8rem] bg-[#17122b] p-[2%] shadow-2xl shadow-[#2b1670]/50 ring-1 ring-white/10">
                            <div class="relative w-full h-full rounded-[.9rem] lg:rounded-[1.2rem] overflow-hidden bg-gradient-to-br from-[#4527a0] to-[#2a1b6b]">

                                {{-- Toolbar kiri --}}
                                <div class="absolute left-0 inset-y-0 w-[8%] bg-black/25 flex flex-col items-center gap-[12%] pt-[8%]">
                                    <span class="w-[45%] aspect-square rounded-md bg-white/70"></span>
                                    <span class="w-[45%] aspect-square rounded-md bg-white/25"></span>
                                    <span class="w-[45%] aspect-square rounded-md bg-white/25"></span>
                                    <span class="w-[45%] aspect-square rounded-md bg-[#D5FC55]"></span>
                                </div>

                                {{-- Bar atas --}}
                                <div class="absolute left-[8%] right-0 top-0 h-[9%] bg-black/15 flex items-center gap-1.5 px-[3%]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                                </div>

                                {{-- Kanvas + coretan --}}
                                <svg class="absolute left-[8%] right-0 bottom-0 top-[9%] w-[92%] h-[91%]" viewBox="0 0 300 190" fill="none" preserveAspectRatio="xMidYMid meet">
                                    <path class="artiv-draw" d="M40 130 C55 50 140 40 150 95 C158 140 100 150 112 105 C125 55 215 30 245 68 C268 98 205 128 178 98 C165 84 205 70 232 82"
                                          stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="232" cy="82" r="4" fill="#D5FC55"/>
                                </svg>

                                {{-- Panel kanan --}}
                                <div class="absolute right-[3%] top-[14%] w-[16%] space-y-[8%]">
                                    <div class="h-2 rounded-full bg-white/20"></div>
                                    <div class="h-2 rounded-full bg-white/20 w-3/4"></div>
                                    <div class="h-2 rounded-full bg-[#D5FC55]/70 w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pena: titik awal. Pena dilepas jadi fixed oleh JS lalu mengikuti scroll ke lingkaran Cara Kerja --}}
                    <span id="artiv-pen-slot" class="absolute left-[80%] top-[74%] w-px h-px">
                        <span id="artiv-pen" class="artiv-pen" aria-hidden="true">
                            <span class="artiv-pen-body">
                                <span class="artiv-pen-shaft"></span>
                                <span class="artiv-pen-band"></span>
                                <span class="artiv-pen-tip"></span>
                            </span>
                        </span>
                    </span>

                    {{-- Gelas pena --}}
                    <div class="artiv-hero-card absolute right-[0%] bottom-[0%] w-[15%] aspect-[3/4] z-40" style="--d:.65s">
                        <span class="absolute left-[22%] bottom-[30%] w-[16%] h-[75%] rounded-full bg-gradient-to-b from-[#D5FC55] to-[#a8cc2e] rotate-[-8deg] origin-bottom"></span>
                        <span class="absolute left-[50%] bottom-[30%] w-[16%] h-[68%] rounded-full bg-gradient-to-b from-[#8B5CF6] to-[#5B21B6] rotate-[10deg] origin-bottom"></span>
                        <div class="absolute inset-x-0 bottom-0 h-[55%] rounded-b-2xl rounded-t-md bg-gradient-to-b from-[#2d2748] to-[#17122b] shadow-xl"></div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- ==================== 3. TICKER KATEGORI ==================== --}}
    <section class="artiv-bleed artiv-bleed--tilt relative bg-[#D5FC55] text-neutral-900 py-4 overflow-hidden -rotate-1 my-6 shadow-md" aria-hidden="true">
        <div class="artiv-marquee flex w-max">
            @for ($loop2 = 0; $loop2 < 2; $loop2++)
                <div class="flex shrink-0 items-center gap-8 pr-8 text-lg sm:text-xl font-black tracking-tight">
                    @foreach (['Banner', 'Poster', 'Logo', 'Postingan Sosial Media', 'Scrapbook', 'Presentasi', 'Branding'] as $word)
                        <span>{{ $word }}</span><span class="text-[#6D28D9]">✦</span>
                    @endforeach
                </div>
            @endfor
        </div>
    </section>

    {{-- ==================== 4. CARA KERJA ==================== --}}
    <section id="artiv-how-scroll" class="artiv-how-scroll artiv-bleed relative">

        {{-- Stage (di-pin oleh GSAP) --}}
        <div id="artiv-how-stage" class="artiv-how-stage relative h-screen overflow-hidden flex items-center justify-center text-white">

            {{-- Background ungu (fade in) --}}
            <div id="artiv-how-bg" class="absolute inset-0 bg-gradient-to-b from-[#6D28D9] via-[#5B21B6] to-[#4C1D95]"></div>

            {{-- Pola titik halus --}}
            <div class="absolute inset-0 opacity-[.07] pointer-events-none"
                 style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 28px 28px;"></div>

            {{-- Lingkaran ungu --}}
            <div class="artiv-scroll-circle absolute top-1/2 left-1/2 rounded-full pointer-events-none z-30"
                 style="background: radial-gradient(circle, #7C3AED 0%, #6D28D9 100%); width: 50px; height: 50px; will-change: transform;"></div>

            {{-- Intro: judul section --}}
            <div class="artiv-how-intro absolute inset-0 flex items-center justify-center px-6 z-10">
                <div class="text-center max-w-2xl">
                    <span class="inline-block px-5 py-2 rounded-full bg-[#D5FC55]/15 border border-[#D5FC55]/40 text-[#D5FC55] text-xs font-bold tracking-widest uppercase mb-6">
                        Semudah itu
                    </span>
                    <h2 class="text-5xl md:text-7xl font-black leading-none tracking-tight mb-6">
                        Cara Kerja<br><span class="text-[#D5FC55]">di ARTIV.</span>
                    </h2>
                    <p class="text-white/70 max-w-lg mx-auto">
                        Proses kreatif yang simpel dan transparan.
                    </p>
                    <p class="mt-10 text-xs text-white/50 flex items-center justify-center gap-2">
                        <span class="inline-block w-4 h-6 rounded-full border border-white/40 relative">
                            <span class="absolute left-1/2 top-1 -translate-x-1/2 w-1 h-1.5 rounded-full bg-[#D5FC55] artiv-wheel"></span>
                        </span>
                        Scroll untuk lanjut
                    </p>
                </div>
            </div>

            {{-- "Lift": daftar langkah yang naik bersama kartunya saat di-scroll --}}
            <div id="artiv-how-track" class="artiv-track">
                @foreach([
                    [
                        'n' => '1', 'title' => 'Pilih Jasa & Buat Pesanan',
                        'desc' => 'Jelajahi katalog, pilih layanan, isi brief, lalu kirim pesananmu.',
                        'bg' => 'bg-[#EAE2FF]',
                    ],
                    [
                        'n' => '2', 'title' => 'Dikerjakan Desainer',
                        'desc' => 'Desainer mulai berkarya berdasarkan brief dan kebutuhanmu.',
                        'bg' => 'bg-[#D5FC55]',
                    ],
                    [
                        'n' => '3', 'title' => 'Approval & Berikan Ulasan',
                        'desc' => 'Tinjau hasil desain, selesaikan pesanan, dan beri ulasan.',
                        'bg' => 'bg-[#17122B]',
                    ],
                ] as $i => $step)
                    <div class="artiv-row mx-auto max-w-6xl w-full grid grid-rows-[auto_minmax(0,1fr)] lg:grid-rows-1 lg:grid-cols-2 gap-4 lg:gap-16 items-center">

                        {{-- Teks --}}
                        <div class="text-center lg:text-left">
                            <div class="text-6xl md:text-8xl font-black text-[#D5FC55] leading-none mb-2 lg:mb-4">{{ $step['n'] }}.</div>
                            <h3 class="text-2xl md:text-4xl lg:text-5xl font-black leading-tight mb-3 lg:mb-5">{{ $step['title'] }}</h3>
                            <p class="text-white/70 max-w-md text-sm md:text-lg mx-auto lg:mx-0">{{ $step['desc'] }}</p>
                        </div>

                        {{-- Kartu --}}
                        <div class="artiv-card relative h-full min-h-0 rounded-3xl overflow-hidden {{ $step['bg'] }} flex items-center justify-center shadow-2xl shadow-black/20">

                            <div class="relative w-[84%] h-[76%] bg-white rounded-xl shadow-2xl overflow-hidden">
                                <div class="h-5 sm:h-6 bg-violet-100 flex items-center gap-1 px-3">
                                    <i class="w-1.5 h-1.5 rounded-full bg-violet-300"></i><i class="w-1.5 h-1.5 rounded-full bg-violet-300"></i><i class="w-1.5 h-1.5 rounded-full bg-violet-300"></i>
                                </div>

                                <div class="p-3 sm:p-4 text-neutral-900">
                                    @if ($i === 0)
                                        <div class="flex gap-3">
                                            <div class="w-1/4 space-y-1.5 pt-1">
                                                <div class="h-1.5 rounded-full bg-[#7C3AED]"></div>
                                                <div class="h-1.5 rounded-full bg-slate-200 w-3/4"></div>
                                                <div class="h-1.5 rounded-full bg-slate-200 w-1/2"></div>
                                            </div>
                                            <div class="flex-1 space-y-2">
                                                <div class="flex flex-wrap gap-1.5">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-[#7C3AED] text-white">Banner</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] bg-slate-100">Poster</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] bg-slate-100">Logo</span>
                                                </div>
                                                <div class="rounded-lg border border-slate-200 p-2 space-y-1.5">
                                                    <div class="h-1.5 rounded-full bg-slate-200"></div>
                                                    <div class="h-1.5 rounded-full bg-slate-200 w-4/5"></div>
                                                    <div class="h-1.5 rounded-full bg-slate-200 w-2/3"></div>
                                                </div>
                                                <span class="inline-block px-3 py-1 rounded-full bg-[#D5FC55] text-[9px] font-bold">Kirim Pesanan</span>
                                            </div>
                                        </div>
                                    @elseif ($i === 1)
                                        <div class="flex gap-3">
                                            <div class="flex-1 h-20 sm:h-28 rounded-lg bg-violet-50 relative overflow-hidden">
                                                <span class="absolute left-[12%] top-[18%] w-[26%] aspect-square rounded-full bg-[#7C3AED]"></span>
                                                <span class="absolute left-[44%] top-[34%] w-[34%] h-[34%] rounded-lg bg-[#D5FC55] rotate-6"></span>
                                                <span class="absolute right-[8%] bottom-[12%] w-[18%] aspect-square rounded-sm bg-[#EC4899] rotate-45"></span>
                                            </div>
                                            <div class="w-1/3 space-y-2 pt-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="w-5 h-5 rounded-full bg-[#7C3AED] text-white text-[9px] font-bold flex items-center justify-center">D</span>
                                                    <span class="text-[9px] font-semibold">Desainer</span>
                                                </div>
                                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full w-3/5 bg-[#7C3AED] rounded-full"></div></div>
                                                <p class="text-[9px] text-slate-500">Sedang dikerjakan</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="grid grid-cols-3 gap-1.5">
                                            @foreach (['from-violet-300 to-violet-500', 'from-[#D5FC55] to-lime-400', 'from-pink-300 to-pink-500', 'from-sky-300 to-indigo-400', 'from-amber-200 to-orange-400', 'from-violet-200 to-[#7C3AED]'] as $g)
                                                <div class="aspect-[4/3] rounded-md bg-gradient-to-br {{ $g }}"></div>
                                            @endforeach
                                        </div>
                                        <div class="mt-2.5 flex items-center justify-between">
                                            <span class="text-[11px] text-amber-400 tracking-wider">★★★★★</span>
                                            <span class="px-3 py-1 rounded-full bg-[#D5FC55] text-[9px] font-bold">Setujui</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Indikator progres --}}
            <div class="artiv-how-dots absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2" aria-hidden="true">
                @for ($d = 0; $d < 4; $d++)
                    <span class="artiv-dot"></span>
                @endfor
            </div>

        </div>
    </section>

    {{-- ==================== 5. LAYANAN KATEGORI ==================== --}}
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

    <div class="mb-16 mt-16">
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3">Layanan Kategori Jasa</h2>
            <p class="text-slate-500 max-w-2xl mx-auto">
                Jelajahi berbagai kategori jasa desain dari desainer pilihan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($categoryGroups as $label => $group)
                @php
                    $categoriesParam = implode(',', $group['items']);
                @endphp
                <a href="{{ route('customer.katalog', ['categories' => $categoriesParam]) }}"
                   aria-label="Lihat kategori {{ $label }}"
                   class="group block text-center rounded-2xl focus:outline-none focus-visible:ring-4 focus-visible:ring-[#6D28D9]/30">
                    <div class="transition-transform duration-300 ease-out group-hover:-translate-y-1.5">
                        <x-folder size="xs"
                            imgLeft="{{ $categoryThumbs[$label][0] ?? 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=400' }}"
                            imgCenter="{{ $categoryThumbs[$label][1] ?? 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=400' }}"
                            imgRight="{{ $categoryThumbs[$label][2] ?? 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=400' }}" />
                    </div>
                    <p class="font-bold text-slate-900 text-sm mt-8 transition-colors group-hover:text-[#6D28D9]">{{ $label }}</p>
                </a>
            @endforeach
        </div>
    </div>

    <div class="mb-16">
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 text-center mb-10">
            Katalog Jasa Desain Populer
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @forelse ($popularProducts ?? [] as $product)
                @php
                    $thumbUrl = \App\Helpers\R2Helper::url($product->thumbnail_url);
                    $minPrice = $product->tiers->min('price') ?? $product->price ?? 0;
                @endphp
                <a href="{{ route('customer.katalog.detail', $product->id) }}"
                   class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:border-[#6D28D9] hover:shadow-md transition overflow-hidden group">

                    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-violet-100 to-violet-200">
                        @if ($thumbUrl)
                            <img src="{{ $thumbUrl }}" alt="{{ $product->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full hidden items-center justify-center absolute inset-0">
                                <span class="text-5xl font-extrabold text-[#6D28D9]/40">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </span>
                            </div>
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="text-5xl font-extrabold text-[#6D28D9]/40">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif

                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-[#D5FC55] text-neutral-900 text-[10px] font-bold uppercase tracking-wide z-10">
                            {{ $product->name }}
                        </span>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-slate-900 leading-tight mb-2 line-clamp-2 min-h-[3rem]">
                            {{ $product->name }}
                        </h3>
                        <p class="text-sm text-slate-500 line-clamp-2 mb-3 min-h-[2.5rem]">
                            {{ $product->description ?? 'Jasa desain profesional dengan hasil berkualitas tinggi.' }}
                        </p>

                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Mulai dari</p>
                                <p class="font-extrabold text-[#6D28D9] text-lg">
                                    Rp {{ number_format($minPrice, 0, ',', '.') }}
                                </p>
                            </div>
                            <span class="px-4 py-1.5 rounded-full bg-[#6D28D9] text-white text-xs font-bold group-hover:bg-[#5B21B6] transition">
                                Detail
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                @for ($i = 1; $i <= 4; $i++)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="aspect-[4/3] bg-gradient-to-br from-violet-100 to-violet-200 flex items-center justify-center">
                            <svg class="w-12 h-12 text-[#6D28D9]/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="p-4">
                            <div class="h-4 bg-slate-100 rounded mb-2"></div>
                            <div class="h-3 bg-slate-100 rounded w-2/3 mb-4"></div>
                            <div class="flex justify-between items-center">
                                <div class="h-4 bg-slate-100 rounded w-1/3"></div>
                                <div class="h-6 w-16 bg-slate-100 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            @endforelse
        </div>
    </div>

    <div class="mb-16">
        <div class="text-center mb-10">

            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3">
                Dipercaya Kreator & Bisnis
            </h2>
            <p class="text-slate-500 max-w-2xl mx-auto">
                Ribuan klien sudah mewujudkan ide kreatifnya bersama desainer ARTIV.
            </p>
        </div>

        <x-animated-testimonials />
    </div>

    <div class="relative rounded-3xl overflow-hidden" style="background: linear-gradient(135deg, #6D28D9 0%, #745BB8 100%);">
        <div class="relative px-8 py-12 md:py-16">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="max-w-2xl text-center md:text-left">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-3">
                        Siap Memulai Proyek Desainmu Bersama Kreator Hebat?
                    </h2>
                    <p class="text-white/80">
                        Konsultasikan kebutuhan visual bisnis Anda secara gratis dan dapatkan penawaran terbaik hari ini dengan garansi proteksi penuh.
                    </p>
                </div>
                <a href="{{ route('customer.katalog') }}"
                   class="shrink-0 inline-flex items-center gap-2 bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-8 py-4 rounded-full transition shadow-lg">
                    Jelajahi Katalog Sekarang
                </a>
            </div>
        </div>
    </div>

    </div>{{-- /max-w-7xl --}}

</div>
@endsection

@push('styles')
<style>
    /* ============ GLOBAL ============ */
    .artiv-homepage{isolation:isolate;color:#171326}
    /* Full-bleed: lepas dari container layout. html di-clip (bukan hidden) agar tidak muncul scroll horizontal
       dan tidak merusak pin GSAP. JANGAN beri overflow pada .artiv-homepage, nanti bleed ikut terpotong. */
    html{overflow-x:clip}
    .artiv-bleed{width:100vw;margin-left:calc(50% - 50vw);max-width:none}
    .artiv-bleed--tilt{width:104vw;margin-left:calc(50% - 52vw)}
    .artiv-doodles{z-index:-1;opacity:.3}
    .artiv-doodle-path{fill:none!important;stroke:#7C3AED!important;stroke-width:2!important;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:8 12;vector-effect:non-scaling-stroke;animation:artiv-doodle-drift 18s ease-in-out infinite alternate}
    .artiv-doodle-path--two{stroke:#D5FC55!important;animation-duration:22s;animation-direction:alternate-reverse}
    .artiv-doodle-path--three{stroke:#8B5CF6!important;animation-duration:26s}
    @keyframes artiv-doodle-drift{from{transform:translateX(-24px)}to{transform:translateX(24px)}}

    /* ============ INTRO ============ */
    .artiv-intro{position:fixed;inset:0;z-index:1000;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#6d28d9;animation:artiv-intro-out .8s cubic-bezier(.7,0,.2,1) 1.15s forwards;pointer-events:none}
    .artiv-intro-logo{font-size:clamp(4rem,12vw,8rem);font-weight:900;letter-spacing:-.09em;color:white;animation:artiv-logo-pop .8s cubic-bezier(.2,1.5,.5,1) both}
    .artiv-intro-logo span{color:#d5fc55}
    .artiv-intro-caption{margin-top:-.5rem;font-size:.65rem;letter-spacing:.35em;font-weight:800;color:#e9ddff;opacity:0;animation:artiv-caption-in .4s ease .45s forwards}
    @keyframes artiv-logo-pop{from{transform:scale(.45) rotate(-8deg);opacity:0}to{transform:scale(1) rotate(0);opacity:1}}
    @keyframes artiv-caption-in{to{opacity:1}}
    @keyframes artiv-intro-out{to{opacity:0;visibility:hidden}}

    /* ============ HERO ============ */
    .artiv-homepage{--ease-out:cubic-bezier(.22,1,.36,1)}

    /* Ornamen: melayang + berkedip halus, tempo tiap ornamen dibedakan */
    .artiv-ornament{font-weight:900;pointer-events:none;will-change:transform,opacity;
        animation:artiv-ornament-float 5s ease-in-out infinite}
    .artiv-ornament:nth-of-type(even){animation-duration:6.5s}
    .artiv-ornament:nth-of-type(3n){animation-duration:7.5s}
    @keyframes artiv-ornament-float{
        0%,100%{transform:translateY(0) rotate(0deg) scale(1);opacity:.45}
        50%{transform:translateY(-14px) rotate(18deg) scale(1.15);opacity:.9}
    }

    /* Glow latar bernafas pelan */
    .artiv-homepage > section:first-of-type .blur-3xl{animation:artiv-breathe 9s ease-in-out infinite alternate;will-change:scale,opacity}
    @keyframes artiv-breathe{from{scale:1;opacity:.75}to{scale:1.12;opacity:1}}

    /* Kartu hero: masuk (blur + scale + naik) lalu melayang.
    --mx/--my/--depth dipakai parallax mouse (diisi JS), jadi gerakannya menyatu dengan float. */
    .artiv-hero-card{
        --dur:6.5s;--amp:-10px;
        will-change:translate,scale,opacity;
        transition-timing-function:var(--ease-out);
        animation:
            artiv-card-in 1.1s var(--ease-out) calc(var(--d,0s) + var(--artiv-base,0s)) backwards,
            artiv-card-float var(--dur) ease-in-out calc(var(--d,0s) + var(--artiv-base,0s) + 1.1s) infinite;
    }
    .artiv-hero-card:nth-child(even){--dur:7.5s;--amp:-14px}
    .artiv-hero-card:nth-child(3n){--dur:5.8s;--amp:-8px}
    @keyframes artiv-card-in{
        from{opacity:0;translate:0 44px;scale:.86;filter:blur(8px)}
        60%{filter:blur(0)}
        to{opacity:1;translate:0 0;scale:1;filter:blur(0)}
    }
    @keyframes artiv-card-float{
        0%,100%{translate:calc(var(--mx,0) * var(--depth,0) * 1px) calc(var(--my,0) * var(--depth,0) * 1px)}
        50%{translate:calc(var(--mx,0) * var(--depth,0) * 1px) calc(var(--my,0) * var(--depth,0) * 1px + var(--amp))}
    }

    /* Coretan di tablet: tergambar -> tahan -> memudar (tidak lagi "terhapus" kasar) */
    .artiv-draw{stroke-dasharray:700;stroke-dashoffset:700;animation:artiv-draw 7s ease-in-out 1.6s infinite}
    @keyframes artiv-draw{
        0%{stroke-dashoffset:700;opacity:1}
        50%{stroke-dashoffset:0;opacity:1}
        82%{stroke-dashoffset:0;opacity:1}
        96%{stroke-dashoffset:0;opacity:0}
        100%{stroke-dashoffset:700;opacity:0}
    }
    /* Titik lime di ujung coretan muncul tepat saat garis selesai */
    .artiv-hero-card circle[r="4"]{transform-box:fill-box;transform-origin:center;animation:artiv-dot 7s ease-in-out 1.6s infinite}
    @keyframes artiv-dot{
        0%,46%{opacity:0;transform:scale(0)}
        54%{opacity:1;transform:scale(1.8)}
        62%,82%{opacity:1;transform:scale(1)}
        96%,100%{opacity:0;transform:scale(0)}
    }

    /* Reveal teks kiri: lebih lembut (expo-out) + blur halus */
    .artiv-reveal{animation:artiv-reveal 1.1s var(--ease-out) calc(var(--artiv-base,0s) + var(--i,0) * .12s) backwards}
    @keyframes artiv-reveal{
        from{opacity:0;translate:0 32px;filter:blur(10px)}
        to{opacity:1;translate:0 0;filter:blur(0)}
    }
    .artiv-underline{stroke-dasharray:320;stroke-dashoffset:320;animation:artiv-underline 1.1s cubic-bezier(.65,0,.25,1) calc(var(--artiv-base,0s) + .9s) forwards}
    @keyframes artiv-underline{to{stroke-dashoffset:0}}

    /* Kata "ARTIV" berkilau gradien pelan (tanpa ubah HTML) */
    .artiv-homepage h1 .bg-clip-text{background-size:220% 220%;animation:artiv-shimmer 7s ease-in-out infinite}
    @keyframes artiv-shimmer{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}

    /* Pena (stylus) */
    .artiv-pen{position:absolute;left:0;top:0;display:block;width:16px;height:clamp(170px,24vw,290px);transform:translate(-50%,-100%) rotate(20deg);transform-origin:50% 100%;z-index:40;pointer-events:none;will-change:transform}
    .artiv-pen.is-fixed{position:fixed}
    .artiv-pen-body{position:relative;display:block;width:100%;height:100%;filter:drop-shadow(0 14px 12px rgba(30,16,80,.35));
        animation:artiv-card-in .8s cubic-bezier(.2,1,.5,1) calc(var(--artiv-base,0s) + .4s) backwards}
    .artiv-pen-shaft{position:absolute;left:0;right:0;top:0;bottom:9%;border-radius:9px 9px 3px 3px;background:linear-gradient(90deg,#3d3660 0%,#17122b 48%,#0d0a1a 100%)}
    .artiv-pen-shaft::after{content:"";position:absolute;left:24%;top:5%;bottom:8%;width:12%;border-radius:9999px;background:rgba(255,255,255,.2)}
    .artiv-pen-band{position:absolute;left:0;right:0;top:16%;height:4.5%;background:#D5FC55}
    .artiv-pen-tip{position:absolute;left:0;right:0;bottom:0;height:11%;background:linear-gradient(90deg,#d9d5ea,#8f89ac);clip-path:polygon(0 0,100% 0,62% 100%,38% 100%)}

    /* ============ TICKER ============ */
    .artiv-marquee{animation:artiv-marquee 28s linear infinite}
    @keyframes artiv-marquee{to{transform:translateX(-50%)}}

    /* ============ CARA KERJA ============ */
    #artiv-how-bg{opacity:0}
    .artiv-how-stage{--row-h:min(78svh,580px);--row-gap:20px}
    @media(min-width:1024px){.artiv-how-stage{--row-h:min(62vh,440px);--row-gap:24px}}
    .artiv-track{position:absolute;left:0;right:0;top:50%;margin-top:calc(var(--row-h) / -2);z-index:10;padding:0 1.5rem;opacity:0;will-change:transform}
    .artiv-row{height:var(--row-h);margin-bottom:var(--row-gap);will-change:transform,opacity}
    .artiv-card{min-height:14rem}
    .artiv-how-dots{opacity:0}
    .artiv-dot{width:8px;height:8px;border-radius:9999px;background:rgba(255,255,255,.3);transition:all .3s ease}
    .artiv-dot.is-active{width:28px;background:#D5FC55}
    .artiv-wheel{animation:artiv-wheel 1.6s ease-in-out infinite}
    @keyframes artiv-wheel{0%{transform:translate(-50%,0);opacity:1}100%{transform:translate(-50%,10px);opacity:0}}

    /* Fallback tanpa GSAP / reduced motion: daftar statis berurutan */
    .artiv-how--static .artiv-how-stage{height:auto;display:block;padding:4rem 0}
    .artiv-how--static #artiv-how-bg{opacity:1}
    .artiv-how--static .artiv-scroll-circle,
    .artiv-how--static .artiv-how-dots{display:none}
    .artiv-how--static .artiv-how-intro{position:relative!important;inset:auto!important;opacity:1!important;transform:none!important;padding:3rem 1.5rem}
    .artiv-how--static .artiv-track{position:relative!important;top:auto!important;margin-top:0!important;opacity:1!important;visibility:visible!important;transform:none!important}
    .artiv-how--static .artiv-row{opacity:1!important;transform:none!important;height:auto;margin-bottom:3rem}
    .artiv-how--static .artiv-card{min-height:18rem}

    /* ============ RESPONSIVE ============ */
    @media(prefers-reduced-motion:reduce){
        .artiv-doodle-path,.artiv-ornament,.artiv-intro-logo,.artiv-intro-caption,.artiv-hero-card,.artiv-marquee,.artiv-wheel,.artiv-draw,.artiv-reveal,.artiv-underline,.artiv-pen-body,
        .artiv-homepage > section:first-of-type .blur-3xl,
        .artiv-homepage h1 .bg-clip-text,
        .artiv-hero-card circle[r="4"]{animation:none!important}
        .artiv-draw,.artiv-underline{stroke-dashoffset:0}
        .artiv-intro{display:none}
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // ==================== INTRO ====================
        // Return true bila intro ditampilkan (animasi hero ditunda sampai intro selesai)
        const initIntro = () => {
            const intro = document.getElementById('artiv-intro');
            if (!intro) return false;
            const removeOnOut = (e) => { if (e.target === intro) intro.remove(); };
            try {
                if (sessionStorage.getItem('artiv-intro-seen') === '1' || reduceMotion) {
                    intro.remove();
                    return false;
                }
                sessionStorage.setItem('artiv-intro-seen', '1');
                intro.addEventListener('animationend', removeOnOut);
                return true;
            } catch (e) {
                intro.addEventListener('animationend', removeOnOut);
                return true;
            }
        };

        // ==================== CARA KERJA — GSAP ====================
        const buildHowScroll = () => {
            const section = document.getElementById('artiv-how-scroll');
            const stage   = document.getElementById('artiv-how-stage');
            const bg      = document.getElementById('artiv-how-bg');
            if (!section || !stage) return;

            const intro  = section.querySelector('.artiv-how-intro');
            const track  = document.getElementById('artiv-how-track');
            const rows   = gsap.utils.toArray('.artiv-row', section);
            const circle = section.querySelector('.artiv-scroll-circle');
            const dotsEl = section.querySelector('.artiv-how-dots');
            const dots   = gsap.utils.toArray('.artiv-dot', section);

            gsap.registerPlugin(ScrollTrigger);

            // ----- PENA: lepas dari hero, ikut scroll sampai ke lingkaran -----
            const pen  = document.getElementById('artiv-pen');
            const slot = document.getElementById('artiv-pen-slot');
            const PEN_TILT = 20;

            if (pen && slot) {
                const r0 = slot.getBoundingClientRect();
                pen.style.transform = 'none';
                pen.classList.add('is-fixed');
                gsap.set(pen, {
                    xPercent: -50, yPercent: -100, rotation: PEN_TILT,
                    transformOrigin: '50% 100%', x: r0.left, y: r0.top
                });

                // Posisi awal dihitung dalam koordinat dokumen (aman walau halaman sedang ter-scroll)
                const slotPoint = () => {
                    const r = slot.getBoundingClientRect();
                    return { x: r.left + window.scrollX, y: r.top + window.scrollY };
                };

                gsap.fromTo(pen,
                    { x: () => slotPoint().x, y: () => slotPoint().y, rotation: PEN_TILT },
                    {
                        x: () => document.documentElement.clientWidth / 2,
                        y: () => stage.offsetHeight / 2,
                        rotation: 0,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: section,
                            start: 0,
                            end: 'top top',
                            scrub: 0.6,
                            invalidateOnRefresh: true,
                        }
                    }
                );
            }

            // ----- LIFT: daftar langkah naik bersama kartunya -----
            const DIM = { opacity: 0.35, scale: 0.96 };
            const pitch  = () => rows.length > 1 ? rows[1].offsetTop - rows[0].offsetTop : stage.offsetHeight;
            const enterY = () => stage.offsetHeight / 2 + rows[0].offsetHeight / 2 + 24;

            gsap.set(bg, { opacity: 0 });
            gsap.set(circle, { xPercent: -50, yPercent: -50, scale: 1, opacity: 1, transformOrigin: '50% 50%' });
            gsap.set(dotsEl, { opacity: 0 });
            gsap.set(intro, { opacity: 0, y: 30 });
            gsap.set(track, { autoAlpha: 1 });
            gsap.set(rows, { ...DIM });

            // Dot aktif: intro, langkah 1, 2, 3
            const TOTAL = 108;
            const activeAt = [40, 57, 75, 93];
            const setDots = (progress) => {
                const pos = progress * TOTAL;
                let active = -1;
                activeAt.forEach((t, i) => { if (pos >= t) active = i; });
                dots.forEach((d, i) => d.classList.toggle('is-active', i === active));
            };

            const tl = gsap.timeline({
                defaults: { ease: 'none' },
                scrollTrigger: {
                    trigger: section,
                    start: 'top top',
                    end: () => '+=' + (window.innerHeight * 6),
                    pin: stage,
                    scrub: 1.2,
                    anticipatePin: 1,
                    invalidateOnRefresh: true,
                    onUpdate: (self) => setDots(self.progress),
                }
            });

            // Lingkaran membesar menutup layar
            tl.to(circle, {
                scale: () => (Math.hypot(window.innerWidth, window.innerHeight) / 50) * 1.1,
                duration: 32,
                ease: 'power2.inOut'
            }, 0);
            tl.to(bg, { opacity: 1, duration: 16 }, 24);
            tl.to(circle, { opacity: 0, duration: 10, ease: 'power2.out' }, 32);
            tl.to(dotsEl, { opacity: 1, duration: 6 }, 40);

            // Pena sudah di lingkaran: mengecil & memudar saat lingkaran terus membesar
            if (pen && slot) {
                tl.to(pen, { autoAlpha: 0, scale: 0.35, duration: 10, ease: 'power2.in' }, 2);
            }

            // Judul section muncul
            tl.to(intro, { opacity: 1, y: 0, duration: 8, ease: 'power2.out' }, 40);

            // Lift 0: judul naik keluar, daftar langkah naik masuk dari bawah
            const LIFT = { duration: 14, ease: 'power1.inOut' };
            tl.to(intro, { y: () => -stage.offsetHeight * 0.6, opacity: 0, ...LIFT }, 50);
            tl.fromTo(track, { y: enterY }, { y: 0, ...LIFT }, 50);
            tl.to(rows[0], { opacity: 1, scale: 1, ...LIFT }, 50);

            // Lift 1 & 2: seluruh daftar naik satu langkah, kartu aktif menyala
            [[1, 68], [2, 86]].forEach(([i, t]) => {
                tl.fromTo(track,
                    { y: () => -pitch() * (i - 1) },
                    { y: () => -pitch() * i, ...LIFT, immediateRender: false },
                    t);
                tl.to(rows[i - 1], { ...DIM, ...LIFT }, t);
                tl.to(rows[i], { opacity: 1, scale: 1, ...LIFT }, t);
            });

            // Tahan langkah terakhir sampai akhir scroll
            tl.to({}, { duration: 8 }, 100);
        };

        const initHowScroll = () => {
            const section = document.getElementById('artiv-how-scroll');
            if (!section) return;

            const fallback = () => section.classList.add('artiv-how--static');

            if (reduceMotion) return fallback();

            // Tunggu GSAP (bisa dimuat belakangan oleh layout), maksimal ±4 detik
            let tries = 0;
            const tick = () => {
                if (window.gsap && window.ScrollTrigger) {
                    buildHowScroll();
                } else if (tries++ < 40) {
                    setTimeout(tick, 100);
                } else {
                    console.warn('GSAP / ScrollTrigger tidak ter-load, memakai tampilan statis.');
                    fallback();
                }
            };
            tick();
        };

        // ==================== HERO PARALLAX (mouse) ====================
        const initHeroParallax = () => {
            if (reduceMotion) return;
            if (!window.matchMedia('(hover:hover) and (pointer:fine)').matches) return; // hanya desktop/mouse

            const hero = document.querySelector('.artiv-homepage section'); // section hero (pertama)
            const visual = hero?.querySelector('.artiv-hero-card')?.parentElement;
            if (!hero || !visual) return;

            // Kedalaman berdasarkan z-index: makin depan, makin banyak bergeser
            visual.querySelectorAll('.artiv-hero-card').forEach((el) => {
                const z = parseInt(getComputedStyle(el).zIndex, 10) || 0;
                el.style.setProperty('--depth', (3 + z * 0.45).toFixed(1));
            });

            let tx = 0, ty = 0, cx = 0, cy = 0, raf = null;
            const loop = () => {
                cx += (tx - cx) * 0.08;   // lerp = gerakan halus, tidak patah
                cy += (ty - cy) * 0.08;
                visual.style.setProperty('--mx', cx.toFixed(3));
                visual.style.setProperty('--my', cy.toFixed(3));
                raf = (Math.abs(tx - cx) > 0.001 || Math.abs(ty - cy) > 0.001)
                    ? requestAnimationFrame(loop) : null;
            };
            const kick = () => { if (!raf) raf = requestAnimationFrame(loop); };

            hero.addEventListener('pointermove', (e) => {
                if (e.pointerType !== 'mouse') return;
                const r = hero.getBoundingClientRect();
                tx = ((e.clientX - r.left) / r.width - 0.5) * 2;   // -1..1
                ty = ((e.clientY - r.top) / r.height - 0.5) * 2;
                kick();
            }, { passive: true });
            hero.addEventListener('pointerleave', () => { tx = 0; ty = 0; kick(); });
        };

        // ==================== INIT ====================
        const introShown = initIntro();
        document.documentElement.style.setProperty('--artiv-base', introShown ? '1.3s' : '0s');
        initHeroParallax();

        if (document.readyState === 'complete') {
            initHowScroll();
        } else {
            window.addEventListener('load', initHowScroll, { once: true });
        }
    })();
</script>
@endpush