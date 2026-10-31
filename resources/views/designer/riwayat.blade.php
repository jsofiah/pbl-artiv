@extends('layouts.designer')

@section('title', 'Riwayat Pekerjaan')

@section('content')
<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-ink">Riwayat Pekerjaan</h1>
            <p class="mt-1 text-sm text-muted">Semua pekerjaan yang sudah kamu selesaikan beserta kompensasi dan rating dari customer.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('designer.riwayat.csv', $filters) }}"
               class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-artiv-bg">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
                Ekspor CSV
            </a>
            <a href="{{ route('designer.riwayat.cetak', $filters) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-artiv-bg">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V3h12v6M6 18H4a1 1 0 01-1-1v-6a1 1 0 011-1h16a1 1 0 011 1v6a1 1 0 01-1 1h-2M6 14h12v7H6z"/></svg>
                Cetak Laporan
            </a>
        </div>
    </div>

        {{-- Filter bar --}}
    <form method="GET" action="{{ route('designer.riwayat') }}" class="mt-6 mb-6">
        <input type="hidden" name="kategori" value="{{ $filters['kategori'] ?? '' }}">
        <input type="hidden" name="urut" value="{{ $filters['urut'] ?? 'terbaru' }}">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Search Bar --}}
            <div class="flex-1 min-w-[280px] bg-white rounded-full shadow-sm border border-slate-100 p-2 flex items-center gap-2 focus-within:border-[#6D28D9] transition-colors duration-200">
                <div class="pl-4 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari riwayat pekerjaan, tiket, atau customer..."
                    class="flex-1 bg-transparent border-0 ring-0 focus:ring-0 focus:outline-none focus:border-transparent focus:bg-transparent shadow-none focus:shadow-none text-slate-700 px-2 py-2 text-sm">
                <button type="submit" class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-5 py-2 rounded-full transition text-sm whitespace-nowrap">
                    Cari
                </button>
            </div>

            {{-- Filter Kategori --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open"
                        class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-5 pr-4 py-3.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[200px] justify-between">
                    <span>
                        @php
                            $katSelected = $kategori->firstWhere('id', $filters['kategori'] ?? '');
                        @endphp
                        {{ $katSelected ? 'Kategori: ' . $katSelected->name : 'Kategori: Semua' }}
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false" class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 max-h-72 overflow-y-auto" style="display: none;">
                    <a href="{{ route('designer.riwayat', array_merge(request()->except('kategori', 'page'), ['kategori' => ''])) }}" class="block w-full text-left px-5 py-2 text-sm transition {{ empty($filters['kategori']) ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">Semua</a>
                    @foreach ($kategori as $k)
                        <a href="{{ route('designer.riwayat', array_merge(request()->except('kategori', 'page'), ['kategori' => $k->id])) }}" class="block w-full text-left px-5 py-2 text-sm transition {{ ($filters['kategori'] ?? '') == $k->id ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">{{ $k->name }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Filter Urutkan --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open"
                        class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-5 pr-4 py-3.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[200px] justify-between">
                    <span>
                        @php
                            $sortLabels = [
                                'terbaru' => 'Urutkan: Terbaru',
                                'terlama' => 'Urutkan: Terlama',
                                'rating'  => 'Urutkan: Rating Tertinggi',
                            ];
                        @endphp
                        {{ $sortLabels[$filters['urut'] ?? 'terbaru'] ?? 'Urutkan: Terbaru' }}
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false" class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 max-h-72 overflow-y-auto" style="display: none;">
                    @foreach (['terbaru' => 'Terbaru Selesai', 'terlama' => 'Terlama Selesai', 'rating' => 'Rating Tertinggi'] as $val => $label)
                        <a href="{{ route('designer.riwayat', array_merge(request()->except('urut', 'page'), ['urut' => $val])) }}" class="block w-full text-left px-5 py-2 text-sm transition {{ ($filters['urut'] ?? 'terbaru') === $val ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Reset --}}
            @if (filled($filters['q'] ?? null) || filled($filters['kategori'] ?? null) || (($filters['urut'] ?? 'terbaru') !== 'terbaru'))
                <a href="{{ route('designer.riwayat') }}" title="Reset filter"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:text-[#6D28D9] hover:border-[#6D28D9] transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </a>
            @endif
        </div>
    </form>

    {{-- Tabel --}}
    <div class="mt-5 overflow-hidden rounded-xl border border-line bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider w-auto">Informasi Pekerjaan</th>
                        <th class="px-4 py-4 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider w-[180px]">Tanggal Selesai</th>
                        <th class="px-4 py-4 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider w-[150px]">Kompensasi</th>
                        <th class="px-4 py-4 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider w-[120px]">Rating</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider w-[200px]">Tindakan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line">
                @forelse ($orders as $order)
                    @php $tgl = $order->completed_at ?? $order->updated_at; @endphp
                    <tr class="hover:bg-artiv-bg/50">
                        <td class="px-6 py-5">
                            {{-- 1. Judul Utama: Layanan --}}
                            <h4 class="text-sm font-bold text-slate-900 mb-1">
                                {{ $order->product->name ?? 'Layanan' }}
                                @if ($order->productTier)
                                    <span class="font-normal text-slate-400">· {{ $order->productTier->name }}</span>
                                @endif
                            </h4>

                            {{-- 2. Sub-info: Customer & Kode Order --}}
                            <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $order->customer->full_name ?? '-' }}
                                </span>
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-400">#{{ $order->order_code }}</span>
                            </div>

                            {{-- 3. Brief (Deskripsi dibuat miring & abu-abu agar tidak mendominasi) --}}
                            @if($order->brief_note)
                                <p class="text-sm text-slate-500 italic line-clamp-2 leading-relaxed">
                                    "{{ $order->brief_note }}"
                                </p>
                            @else
                                <p class="text-sm text-slate-400 italic">Tidak ada brief tambahan.</p>
                            @endif
                        </td>

                        {{-- Kolom Tanggal --}}
                        <td class="whitespace-nowrap px-4 py-6 text-center">
                            <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                {{ $tgl?->translatedFormat('d M Y') }}
                            </span>
                        </td>

                        {{-- Kolom Kompensasi --}}
                        <td class="whitespace-nowrap px-4 py-6 text-center">
                            <span class="text-sm font-bold text-slate-900">
                                Rp {{ number_format($order->split->designer_earning ?? 0, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Kolom Rating --}}
                        <td class="px-4 py-6 text-center">
                            @if ($order->review)
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 border border-slate-200 px-3 py-1">
                                    <svg class="h-3.5 w-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.8 4.8 17.6l1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                                    <span class="text-sm font-bold text-slate-800">{{ number_format($order->review->rating, 1) }}</span>
                                </div>
                            @else
                                <span class="text-sm text-slate-400">Belum ada</span>
                            @endif
                        </td>

                        <td class="px-6 py-6 text-right">
                            <a href="{{ route('designer.riwayat.show', $order) }}"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-[#6D28D9] hover:text-[#6D28D9] hover:shadow-sm">
                                Lihat Review
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <p class="font-semibold text-ink">
                                {{ filled($filters['q'] ?? null) ? 'Tidak ada riwayat yang cocok' : 'Belum ada pekerjaan selesai' }}
                            </p>
                            <p class="mt-1 text-sm text-muted">
                                {{ filled($filters['q'] ?? null)
                                    ? 'Coba ubah kata kunci atau reset filter.'
                                    : 'Pekerjaan yang sudah selesai akan muncul di sini.' }}
                            </p>
                            <a href="{{ route('designer.job-pool') }}"
                               class="mt-4 inline-flex rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white hover:bg-primary-dark">
                                Cari pekerjaan di Job Pool
                            </a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->total() > 0)
            <nav class="flex flex-col gap-3 border-t border-line px-6 py-4 sm:flex-row sm:items-center sm:justify-between" aria-label="Pagination">
                
                <div class="text-sm text-slate-500">
                    Menampilkan {{ $orders->firstItem() }} &ndash; {{ $orders->lastItem() }} dari <span class="font-semibold text-slate-700">{{ $orders->total() }}</span> Riwayat
                </div>

                <div class="flex items-center gap-2">
                    @if ($orders->onFirstPage())
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-400 cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Sebelumnya
                        </span>
                    @else
                        <a href="{{ $orders->previousPageUrl() }}" class="inline-flex items-center gap-1 rounded-full bg-white border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Sebelumnya
                        </a>
                    @endif

                    <div class="flex items-center gap-1.5">
                        @foreach ($orders->getUrlRange(max(1, $orders->currentPage() - 1), min($orders->lastPage(), $orders->currentPage() + 1)) as $page => $url)
                            @if ($page == $orders->currentPage())
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#6D28D9] text-sm font-bold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-600 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($orders->currentPage() + 1 < $orders->lastPage())
                            <span class="px-1 text-slate-400">...</span>
                            <a href="{{ $orders->url($orders->lastPage()) }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-600 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">{{ $orders->lastPage() }}</a>
                        @endif
                    </div>

                    @if ($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}" class="inline-flex items-center gap-1 rounded-full bg-white border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">
                            Selanjutnya
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-400 cursor-not-allowed">
                            Selanjutnya
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                </div>
            </nav>
        @endif
    </div>
</div>
@endsection