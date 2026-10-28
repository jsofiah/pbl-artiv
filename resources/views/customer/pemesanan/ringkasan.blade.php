@extends('layouts.customer')

@section('title', 'Ringkasan Pesanan')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <span class="inline-block px-3 py-1 rounded-full bg-violet-100 text-[#6D28D9] text-xs font-bold mb-3">
            TAHAP 2 DARI 3 • KONFIRMASI RINGKASAN
        </span>
        <h1 class="text-4xl font-extrabold text-slate-900">Ringkasan Pesanan</h1>
        <div class="w-14 h-1.5 bg-[#6D28D9] rounded-full mt-2 mb-3"></div>
        <p class="text-slate-500 max-w-2xl">
            Periksa kembali detail pesanan, brief kerja, dan kalkulasi pembayaran Anda sebelum melanjutkan.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="flex items-center gap-2 font-bold text-lg text-slate-900">
                        <span class="w-7 h-7 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-sm font-bold">1</span>
                        Detail Pesanan
                    </h2>
                    <a href="{{ route('customer.pemesanan.create', $product->id) }}"
                       class="text-sm font-semibold text-[#6D28D9] hover:underline">
                        [ Ubah di Form ]
                    </a>
                </div>

                @php
                    $thumbUrl = \App\Helpers\R2Helper::url($product->thumbnail_url);
                @endphp

                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-xl bg-[#6D28D9] text-white font-bold flex items-center justify-center shrink-0 overflow-hidden">
                        @if ($thumbUrl)
                            <img src="{{ $thumbUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="hidden w-full h-full items-center justify-center">
                                {{ strtoupper(substr($product->name, 0, 1)) }}
                            </span>
                        @else
                            {{ strtoupper(substr($product->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <span class="inline-block px-2.5 py-0.5 rounded-md bg-violet-50 text-[#6D28D9] text-xs font-semibold mb-1">
                            {{ $product->name }}
                        </span>
                        <h3 class="font-bold text-lg text-slate-900">{{ $product->name }}</h3>
                    </div>
                </div>

                @php
                    \Carbon\Carbon::setLocale('id');
                    $deadline = \Carbon\Carbon::parse($pemesananData['deadline']);
                @endphp

                <div class="bg-violet-50 border border-violet-100 rounded-xl p-4">
                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-[#6D28D9] text-white text-[10px] font-bold mb-2">
                        PAKET TERPILIH
                    </span>
                    <p class="font-bold text-slate-900">
                        {{ $pemesananData['product_tier_name'] }}
                        @if ($pemesananData['is_express'])
                            + Express (Prioritas Cepat)
                        @endif
                    </p>
                    <p class="text-sm text-slate-500 mt-1">
                        Estimasi Penyelesaian: {{ $deadline->translatedFormat('l, d F Y') }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="flex items-center gap-2 font-bold text-lg text-slate-900">
                        <span class="w-7 h-7 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-sm font-bold">2</span>
                        Referensi & Catatan Brief
                    </h2>
                    @php
                        $fileCount = collect($references)->where('type', 'file')->count();
                        $linkCount = collect($references)->where('type', 'link')->count();
                    @endphp

                    <span class="text-sm font-semibold text-[#6D28D9]">
                        [ {{ $fileCount }} Berkas
                        @if ($linkCount > 0)
                            , {{ $linkCount }} Tautan
                        @endif
                        ]
                    </span>
                </div>

                @php
                    $files = collect($references)->where('type', 'file');
                    $links = collect($references)->where('type', 'link');
                @endphp

                @if ($files->count() > 0)
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">BERKAS REFERENSI TERUNGGAH:</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                        @foreach ($files as $ref)
                            @php
                                $mime = $ref['mime_type'] ?? '';
                                $isImage = str_starts_with($mime, 'image/');
                                $ext = strtoupper(pathinfo($ref['file_name'] ?? '', PATHINFO_EXTENSION));
                            @endphp

                            <a href="{{ \Storage::disk('r2')->temporaryUrl($ref['file_url'], now()->addHour()) }}" target="_blank" rel="noopener"
                                class="flex items-center gap-3 bg-slate-50 hover:bg-slate-100 rounded-xl p-3 border border-slate-100 transition cursor-pointer">
                                <div class="w-10 h-10 rounded-lg {{ $isImage ? 'bg-blue-100' : 'bg-violet-100' }} flex items-center justify-center shrink-0">
                                    @if ($isImage)
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $ref['file_name'] }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ number_format(($ref['file_size'] ?? 0) / 1024 / 1024, 1) }} MB
                                        • {{ $ext ?: 'FILE' }}
                                        {{ $isImage ? 'Gambar' : 'Dokumen' }}
                                    </p>
                                </div>

                                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($links->count() > 0)
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3 mt-4">TAUTAN REFERENSI EKSTERNAL:</p>
                    <div class="space-y-2 mb-4">
                        @foreach ($links as $ref)
                            <a href="{{ $ref['external_url'] }}" target="_blank"
                            class="flex items-center gap-2 bg-violet-50 border border-violet-100 rounded-xl px-3 py-2.5 hover:bg-violet-100 transition">
                                <svg class="w-4 h-4 text-[#6D28D9] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                </svg>
                                <span class="text-sm text-[#6D28D9] truncate hover:underline">{{ $ref['external_url'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($pemesananData['brief_note'])
                    <div class="mt-4 bg-slate-50 border border-slate-100 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="font-semibold text-slate-700 text-sm">Catatan Brief Desain</span>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $pemesananData['brief_note'] }}</p>
                    </div>
                @endif
            </div>


            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-[#6D28D9] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-slate-900 mb-1">Proteksi Pembayaran ARTIV</p>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Dana Anda disimpan dengan aman dalam rekening bersama ARTIV dan <strong>hanya akan diteruskan ke kreator setelah Anda menyetujui hasil akhir desain</strong>.
                    </p>
                </div>
            </div>

        </div>

        <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-24">

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4 gap-2">
                    <h2 class="font-bold text-lg text-slate-900">Rincian Biaya</h2>
                    <span class="px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold shrink-0">IDR • RUPIAH</span>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-600 break-words">
                            Paket {{ $pemesananData['product_tier_name'] }}
                        </span>
                        <span class="font-semibold text-slate-900 whitespace-nowrap">
                            Rp {{ number_format($pemesananData['unit_price'], 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-600">Jumlah</span>
                        <span class="font-semibold text-slate-900 whitespace-nowrap">
                            × {{ $pemesananData['quantity'] }}
                        </span>
                    </div>

                    @if ($pemesananData['is_express'] && $pemesananData['express_fee'] > 0)
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-slate-600 break-words">
                                Express {{ $pemesananData['express_fee_name'] }}
                            </span>
                            <span class="font-semibold text-[#6D28D9] whitespace-nowrap">
                                + Rp {{ number_format($pemesananData['express_fee'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-600">Biaya Layanan</span>
                        <span class="font-semibold text-emerald-600 whitespace-nowrap">Rp 0</span>
                    </div>
                </div>

                <div class="border-t border-dashed border-slate-200 my-4"></div>

                <div class="bg-violet-50 rounded-xl p-4 flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-700">TOTAL TAGIHAN</span>
                    <span class="text-xl font-extrabold text-slate-900 whitespace-nowrap">
                        Rp {{ number_format($pemesananData['total_price'], 0, ',', '.') }}
                    </span>
                </div>

                <form action="{{ route('customer.pemesanan.konfirmasi', $product->id) }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-4 py-3.5 rounded-xl transition shadow-sm">
                        Lanjut ke Pembayaran
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </form>

                <a href="{{ route('customer.pemesanan.create', $product->id) }}"
                    class="block text-center text-sm font-semibold text-[#6D28D9] hover:underline mt-3">
                    Kembali ke Form Pemesanan
                </a>
            </div>
            
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-4">ALUR LANGKAH SELANJUTNYA:</p>
                <ol class="space-y-3">
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span class="text-sm text-slate-500 line-through">Pengisian Formulir</span>
                    </li>
                    <li class="flex items-center gap-3 bg-violet-50 rounded-lg px-3 py-2">
                        <span class="w-6 h-6 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-xs font-bold shrink-0">2</span>
                        <span class="text-sm font-bold text-[#6D28D9]">Konfirmasi Ringkasan</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xs font-bold shrink-0">3</span>
                        <span class="text-sm text-slate-400">Pembayaran Pesanan</span>
                    </li>
                </ol>
            </div>

        </div>
    </div>
</div>
@endsection