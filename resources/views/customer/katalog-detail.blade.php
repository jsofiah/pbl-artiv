@extends('layouts.customer')

@section('title', $product->name)

@section('content')
<div class="max-w-6xl mx-auto py-8" x-data="{ activeImage: 0 }">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('customer.beranda') }}" class="hover:text-[#6D28D9]">Beranda</a>
        <span class="mx-2">/</span>
        <a href="{{ route('customer.katalog') }}" class="hover:text-[#6D28D9]">Katalog Jasa</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-semibold">{{ $product->name }}</span>
    </nav>

    @php
        $mainThumb = \App\Helpers\R2Helper::url($product->thumbnail_url);
        $minPrice = $product->tiers->min('price') ?? $product->price ?? 0;

        // ============ GALERI GAMBAR ============
        // Karena belum ada tabel product_images, kita pakai array manual.
        // Nanti kalau ada tabel galeri, tinggal ganti dengan:
        // $images = $product->images->pluck('url')->toArray();
        $images = array_filter([
            $mainThumb,   //
            // 'https://...',
            // 'https://...',
        ]);
        $images = array_values($images); // re-index
    @endphp

    {{-- Hero Product --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- Kiri: Gambar --}}
            <div>
                {{-- Gambar Utama --}}
                <div class="aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-violet-100 to-violet-200 mb-4 relative">
                    @if (count($images) > 0)
                        @foreach ($images as $index => $img)
                            <img x-show="activeImage === {{ $index }}" x-transition.opacity
                                 src="{{ $img }}" alt="{{ $product->name }}"
                                 class="w-full h-full object-cover absolute inset-0"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div x-show="activeImage === {{ $index }}" class="w-full h-full hidden items-center justify-center absolute inset-0 bg-gradient-to-br from-violet-100 to-violet-200">
                                <span class="text-7xl font-extrabold text-[#6D28D9]/40">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </span>
                            </div>
                        @endforeach
                    @else
                        {{-- Placeholder kalau tidak ada gambar --}}
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="text-7xl font-extrabold text-[#6D28D9]/40">
                                {{ strtoupper(substr($product->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif
                </div>

                @if (count($images) > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach ($images as $index => $img)
                            <button type="button"
                                    @click="activeImage = {{ $index }}"
                                    class="aspect-square rounded-xl overflow-hidden border-2 transition cursor-pointer bg-gradient-to-br from-violet-50 to-violet-100"
                                    :class="activeImage === {{ $index }} ? 'border-[#6D28D9] ring-2 ring-[#6D28D9]/20' : 'border-transparent hover:border-[#6D28D9]/40'">
                                <img src="{{ $img }}" alt="Preview {{ $index + 1 }}"
                                        class="w-full h-full object-cover"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full hidden items-center justify-center">
                                    <svg class="w-6 h-6 text-[#6D28D9]/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <span class="inline-block px-3 py-1 rounded-full bg-violet-100 text-[#6D28D9] text-xs font-bold mb-3">
                    {{ $product->name }}
                </span>

                <h1 class="text-3xl font-extrabold text-slate-900 leading-tight mb-3">
                    {{ $product->name }}
                </h1>

                <div class="flex items-baseline gap-3 mb-4 flex-wrap">
                    <span class="text-sm text-slate-500">Mulai dari</span>
                    <span class="text-3xl font-extrabold text-[#6D28D9]">
                        Rp {{ number_format($minPrice, 0, ',', '.') }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md bg-violet-50 text-[#6D28D9] text-xs font-semibold">
                        <!-- 🕐 Estimasi: 3 Hari -->
                    </span>
                </div>

                <p class="text-slate-600 leading-relaxed mb-6">
                    {{ $product->description ?? 'Dapatkan hasil desain orisinal yang berkarakter kuat, dibuat secara eksklusif oleh desainer senior terverifikasi. Kami merancang identitas visual yang tepat sasaran, berkesan abadi, serta siap diaplikasikan ke semua media digital dan cetak.' }}
                </p>

                <div class="bg-violet-50 border border-violet-100 rounded-2xl p-5">
                    <p class="text-xs font-bold text-[#6D28D9] uppercase tracking-wide mb-3">
                        KEUNGGULAN STANDAR LAYANAN ARTIV:
                    </p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-[#6D28D9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>100% Hak Cipta Penuh & Desain Orisinal Eksklusif</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-[#6D28D9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Master File Vektor Komplit (AI, EPS, SVG, PNG High-Res)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-[#6D28D9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Garansi Revisi Aktif & Konsultasi Pra-Cetak Gratis</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-10">
        <h2 class="flex items-center gap-3 text-2xl font-extrabold text-slate-900 mb-6">
            <span class="w-1.5 h-7 bg-[#6D28D9] rounded-full"></span>
            Paket yang Ditawarkan
        </h2>

        @if ($product->tiers->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($product->tiers as $tier)
                    @php
                        $isPopular = $loop->index === 1;
                    @endphp
                    <div class="relative bg-white rounded-2xl p-6 shadow-sm border-2 transition
                                {{ $isPopular ? 'border-[#6D28D9]' : 'border-slate-100 hover:border-[#6D28D9]/50' }}">

                        @if ($isPopular)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-[#D5FC55] text-neutral-900 text-[10px] font-bold">
                                PALING POPULER
                            </span>
                        @endif

                        <span class="inline-block px-2.5 py-1 rounded-md {{ $isPopular ? 'bg-[#6D28D9] text-white' : 'bg-violet-50 text-[#6D28D9]' }} text-[10px] font-bold uppercase tracking-wide mb-3">
                            {{ $isPopular ? 'REKOMENDASI' : ($loop->index === 2 ? 'SOLUSI LENGKAP' : 'PAKET DASAR') }}
                        </span>

                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">{{ $tier->name }}</p>
                        <p class="text-3xl font-extrabold text-[#6D28D9] mb-4">
                            Rp {{ number_format($tier->price, 0, ',', '.') }}
                            <span class="text-sm font-normal text-slate-500">/ paket</span>
                        </p>

                        @if ($tier->description)
                            <ul class="space-y-2 text-sm text-slate-600">
                                @foreach (explode("\n", $tier->description) as $feature)
                                    @if (trim($feature))
                                        <li class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-[#6D28D9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>{{ trim($feature) }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            {{-- EMPTY STATE: kalau tidak ada paket --}}
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-100">
                <div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900 mb-1">Belum Ada Paket Tersedia</p>
                <p class="text-sm text-slate-500">
                    Paket untuk jasa ini sedang dalam proses penambahan. Silakan cek kembali nanti atau hubungi admin.
                </p>
            </div>
        @endif
    </div>

    <div class="mb-10">
        <h2 class="flex items-center gap-3 text-2xl font-extrabold text-slate-900 mb-6">
            <span class="w-1.5 h-7 bg-[#6D28D9] rounded-full"></span>
            Informasi Pengerjaan & Ketentuan
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900 mb-2">Estimasi Pengerjaan</p>
                <p class="text-sm text-slate-500 leading-relaxed">
                    Pengerjaan dimulai setelah pembayaran dan pesanan diterima desainer, dengan estimasi 2–3 hari kerja.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900 mb-2">Revisi & Brief</p>
                <p class="text-sm text-slate-500 leading-relaxed">
                    Revisi dilakukan berdasarkan brief dan kebutuhan yang disampaikan saat pemesanan. Jumlah revisi mengikuti paket yang dipilih.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-900 mb-2">Deadline & Express</p>
                <p class="text-sm text-slate-500 leading-relaxed">
                    Deadline standar mengikuti estimasi paket. Pesanan dengan deadline lebih cepat dapat dikenakan biaya express sesuai ketentuan ARTIV.
                </p>
            </div>
        </div>
    </div>

    <div class="relative rounded-3xl overflow-hidden" style="background: linear-gradient(135deg, #6D28D9 0%, #745BB8 100%);">
        <div class="relative px-8 py-12 text-center">
            <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold uppercase tracking-wide mb-4">
                LAYANAN DESAIN TERPERCAYA
            </span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-3">
                Siap Memulai Proyek Desainmu?
            </h2>
            <p class="text-white/80 max-w-xl mx-auto mb-6">
                Konsultasikan konsepmu secara langsung dengan desainer profesional terverifikasi di ARTIV untuk hasil yang maksimal.
            </p>
            <a href="{{ route('customer.pemesanan.create', $product->id) }}"
               class="inline-flex items-center gap-2 bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-8 py-4 rounded-full transition shadow-lg">
                Pesan Jasa Sekarang →
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush