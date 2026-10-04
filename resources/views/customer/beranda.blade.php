@extends('layouts.customer')

@section('title', 'Beranda')

@section('content')
<div class="max-w-7xl mx-auto py-8">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center mb-12">
        {{-- Kiri: Text --}}
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-violet-100 text-[#6D28D9] text-xs font-semibold mb-4">
                Platform Jasa Kreatif untuk Kebutuhanmu
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight mb-4">
                Wujudkan Ide Kreatifmu Bersama <span class="text-[#6D28D9]">Desainer</span> Terpercaya
            </h1>
            <p class="text-slate-600 leading-relaxed mb-6 max-w-lg">
                Dari desain visual hingga kebutuhan kreatif lainnya, ARTIV membantu kamu menemukan desainer yang sesuai, memesan jasa dengan mudah, pantau proses pengerjaan, dan lakukan transaksi dengan aman melalui sistem ARTIV.
            </p>
            <a href="{{ route('customer.katalog') }}"
               class="inline-flex items-center gap-2 bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-6 py-3 rounded-full transition shadow-sm">
                Pesan Jasa Sekarang
            </a>
        </div>

        <div class="relative">
            <div class="rounded-3xl overflow-hidden shadow-lg bg-gradient-to-br from-violet-100 to-violet-200 aspect-[4/3] flex items-center justify-center">
                {{-- Placeholder gambar --}}
                <div class="text-center">
                    <svg class="w-20 h-20 text-[#6D28D9]/40 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                    <p class="text-sm text-[#6D28D9]/60 font-medium">Preview Platform ARTIV</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-[#6D28D9] rounded-3xl p-6 md:p-8 mb-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            {{-- Statistik 1 --}}
            <div>
                <p class="text-3xl md:text-4xl font-extrabold text-white mb-1">
                    <!-- ISI ANGKA DI SINI -->
                </p>
                <p class="text-sm text-white/80">
                    <!-- ISI LABEL DI SINI -->
                </p>
            </div>

            <div>
                <p class="text-3xl md:text-4xl font-extrabold text-white mb-1">
                </p>
                <p class="text-sm text-white/80">
                </p>
            </div>

            <div>
                <p class="text-3xl md:text-4xl font-extrabold text-white mb-1">
                </p>
                <p class="text-sm text-white/80">
                </p>
            </div>

            <div>
                <p class="text-3xl md:text-4xl font-extrabold text-white mb-1">
                </p>
                <p class="text-sm text-white/80">
                </p>
            </div>
        </div>
    </div>

    <div class="mb-16">
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3">Cara Kerja di ARTIV</h2>
            <p class="text-slate-500 max-w-2xl mx-auto">
                Proses pemesanan jasa desain yang mudah dan terstruktur dan pemantauan dalam satu platform.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-violet-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900 mb-2">01. Pilih Jasa & Buat Pesanan</p>
                <p class="text-sm text-slate-500 leading-relaxed max-w-xs mx-auto">
                    Jelajahi katalog jasa desain, pilih layanan yang sesuai kebutuhanmu, lalu buat pesanan dengan memberikan detail dan brief proyek.
                </p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-[#D5FC55] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-neutral-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900 mb-2">02. Dikerjakan Desainer</p>
                <p class="text-sm text-slate-500 leading-relaxed max-w-xs mx-auto">
                    Desainer menerima pesanan dan mulai mengerjakan proyek sesuai brief. Komunikasikan kebutuhan, berikan masukan, dan pantau perkembangan pengerjaan melalui ARTIV.
                </p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-violet-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900 mb-2">03. Approval & Berikan Ulasan</p>
                <p class="text-sm text-slate-500 leading-relaxed max-w-xs mx-auto">
                    Tinjau hasil desain setelah pengerjaan selesai. Setelah pesanan selesai, kamu dapat memberikan rating dan ulasan sebagai bentuk feedback untuk desainer.
                </p>
            </div>
        </div>
    </div>

    <div class="mb-16">
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3">Layanan Kategori Jasa</h2>
            <p class="text-slate-500 max-w-2xl mx-auto">
                Jelajahi berbagai kategori jasa desain dari desainer pilihan untuk membantu mewujudkan kebutuhan personal maupun bisnis dalam satu platform.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Kategori 1 --}}
            <a href="{{ route('customer.katalog', ['category' => 'Logo']) }}"
               class="group bg-white rounded-3xl p-8 border border-slate-100 hover:border-[#6D28D9] hover:shadow-md transition text-center">
                <div class="aspect-[4/3] rounded-2xl bg-gradient-to-br from-violet-50 to-violet-100 flex items-center justify-center mb-4 overflow-hidden">
                    <svg class="w-20 h-20 text-[#6D28D9]/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900">Desain Logo</p>
            </a>

            <a href="{{ route('customer.katalog', ['category' => 'Poster']) }}"
               class="group bg-white rounded-3xl p-8 border border-slate-100 hover:border-[#6D28D9] hover:shadow-md transition text-center">
                <div class="aspect-[4/3] rounded-2xl bg-gradient-to-br from-violet-50 to-violet-100 flex items-center justify-center mb-4 overflow-hidden">
                    <svg class="w-20 h-20 text-[#6D28D9]/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900">Desain Poster</p>
            </a>

            <a href="{{ route('customer.katalog', ['category' => 'Postingan Sosial Media']) }}"
               class="group bg-white rounded-3xl p-8 border border-slate-100 hover:border-[#6D28D9] hover:shadow-md transition text-center">
                <div class="aspect-[4/3] rounded-2xl bg-gradient-to-br from-violet-50 to-violet-100 flex items-center justify-center mb-4 overflow-hidden">
                    <svg class="w-20 h-20 text-[#6D28D9]/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900">Branding & Media Sosial</p>
            </a>
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
                    Jelajahi Katalog Sekarang →
                </a>
            </div>
        </div>
    </div>

</div>
@endsection