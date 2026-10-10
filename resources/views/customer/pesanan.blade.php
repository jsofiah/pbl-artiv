@extends('layouts.customer')

@section('title', 'Pesanan Saya')

@section('content')
<div class="max-w-7xl mx-auto py-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl font-extrabold text-slate-900 mb-3">Pesanan Saya</h1>
        <p class="text-slate-500 max-w-2xl mx-auto">
            Pantau perkembangan pengerjaan proyek desain aktif dan riwayat transaksi kreatif Anda.
        </p>
    </div>

    {{-- Search Bar --}}
    <form method="GET" action="{{ route('customer.pesanan') }}" class="mb-6">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="bg-white rounded-full shadow-sm border border-slate-100 p-2 flex items-center gap-2 max-w-3xl mx-auto">
            <div class="pl-4 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari nomor pesanan, nama jasa, atau desainer..."
                   class="flex-1 bg-transparent border-none focus:outline-none text-slate-700 placeholder:text-slate-400 px-2 py-2">
            <button type="submit"
                    class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-6 py-2.5 rounded-full transition">
                Cari Pesanan →
            </button>
        </div>
    </form>

    {{-- Tab --}}
    <div class="flex gap-6 border-b border-slate-200 mb-5">
        @foreach (['aktif' => ['Pesanan Aktif', $countAktif], 'riwayat' => ['Riwayat Selesai', $countRiwayat]] as $key => [$label, $count])
            <a href="{{ route('customer.pesanan', ['tab' => $key]) }}"
               class="pb-3 text-sm font-semibold flex items-center gap-2 transition
                      {{ $tab === $key ? 'text-[#6D28D9] border-b-2 border-[#6D28D9]' : 'text-slate-500 hover:text-[#6D28D9]' }}">
                {{ $label }}
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-[#6D28D9] font-bold bg-white">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    {{-- Filter Bar --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">

        {{-- Filter Status --}}
        <div x-data="{ open: false }" class="relative">
            <button type="button" @click="open = !open"
                    class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-5 pr-4 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[220px] justify-between">
                <span>
                    @php
                        $currentStatus = request('status');
                        $statusLabel = $currentStatus && isset($statusOptions[$currentStatus])
                            ? 'Status: ' . $statusOptions[$currentStatus]
                            : 'Status: Semua Status';
                    @endphp
                    {{ $statusLabel }}
                </span>
                <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" @click.outside="open = false"
                 class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 max-h-72 overflow-y-auto"
                 style="display: none;">
                <a href="{{ route('customer.pesanan', array_merge(request()->except('status', 'page'), ['tab' => $tab])) }}"
                   class="block w-full text-left px-5 py-2 text-sm transition
                          {{ !request('status') ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                    Semua Status
                </a>
                @foreach ($statusOptions as $value => $label)
                    <a href="{{ route('customer.pesanan', array_merge(request()->except('page'), ['tab' => $tab, 'status' => $value])) }}"
                       class="block w-full text-left px-5 py-2 text-sm transition
                              {{ request('status') === $value ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Filter Kategori --}}
        <div x-data="{ open: false }" class="relative">
            <button type="button" @click="open = !open"
                    class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-5 pr-4 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[220px] justify-between">
                <span>{{ request('kategori') ?: 'Kategori: Semua Kategori' }}</span>
                <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" @click.outside="open = false"
                 class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 max-h-72 overflow-y-auto"
                 style="display: none;">
                <a href="{{ route('customer.pesanan', array_merge(request()->except('kategori', 'page'), ['tab' => $tab])) }}"
                   class="block w-full text-left px-5 py-2 text-sm transition
                          {{ !request('kategori') ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                    Semua Kategori
                </a>
                @foreach ($categories as $cat)
                    <a href="{{ route('customer.pesanan', array_merge(request()->except('page'), ['tab' => $tab, 'kategori' => $cat])) }}"
                       class="block w-full text-left px-5 py-2 text-sm transition
                              {{ request('kategori') === $cat ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Sort --}}
        <div x-data="{ open: false }" class="relative ml-auto">
            <button type="button" @click="open = !open"
                    class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-5 pr-4 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[220px] justify-between">
                <span>
                    @php
                        $sortLabels = [
                            'deadline' => 'Urutkan: Deadline Terdekat',
                            'terbaru'  => 'Urutkan: Terbaru',
                            'terlama'  => 'Urutkan: Terlama',
                        ];
                    @endphp
                    {{ $sortLabels[request('urut', 'deadline')] ?? 'Urutkan: Deadline Terdekat' }}
                </span>
                <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" @click.outside="open = false"
                 class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 right-0"
                 style="display: none;">
                @foreach (['deadline' => 'Deadline Terdekat', 'terbaru' => 'Terbaru', 'terlama' => 'Terlama'] as $value => $label)
                    <a href="{{ route('customer.pesanan', array_merge(request()->except('page'), ['tab' => $tab, 'urut' => $value])) }}"
                       class="block w-full text-left px-5 py-2 text-sm transition
                              {{ request('urut', 'deadline') === $value ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Reset Filter --}}
        <a href="{{ route('customer.pesanan', ['tab' => $tab]) }}" title="Reset filter"
           class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:text-[#6D28D9] hover:border-[#6D28D9] transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </a>

    </div>

    {{-- Info Hasil --}}
    @if (request()->hasAny(['q', 'status', 'kategori', 'urut']))
        <div class="mb-4 text-sm text-slate-500">
            Menampilkan <strong>{{ $orders->total() }}</strong> pesanan
            @if (request('q')) untuk pencarian "<strong>{{ request('q') }}</strong>"@endif
            .
            <a href="{{ route('customer.pesanan', ['tab' => $tab]) }}"
               class="text-[#6D28D9] font-semibold hover:underline ml-2">
                Reset filter
            </a>
        </div>
    @endif

    {{-- Daftar pesanan --}}
    @forelse ($orders as $order)
        @php
            $p = $order->progress;
            $thumbUrl = \App\Helpers\R2Helper::url($order->product->thumbnail_url);
        @endphp

        <div class="bg-white rounded-2xl p-5 shadow-sm mb-4 border border-slate-100 hover:border-[#6D28D9] hover:shadow-md transition">
            <div class="flex items-center gap-5">
                {{-- Thumbnail --}}
                <div style="width: 144px; height: 192px;" 
                    class="shrink-0 rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center">
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}"
                            alt="{{ $order->product->name }}"
                            class="w-full h-full object-cover object-center"
                            style="width: 100%; height: 100%;">
                    @else
                        <span class="text-xs font-semibold text-slate-400 uppercase">Preview Jasa</span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    {{-- Badge status --}}
                    <span class="inline-block rounded-full bg-[#6D28D9] px-3 py-1 text-xs font-semibold text-white">
                        @if ($tab === 'aktif' && $p['step'] > 0)
                            Tahap {{ $p['step'] }}: {{ $p['label'] }}
                        @else
                            {{ $p['label'] }}
                        @endif
                    </span>

                    <h2 class="text-lg font-bold text-slate-900 mt-3 mb-2 truncate">{{ $order->product->name }}</h2>

                    <div class="flex flex-wrap items-center gap-x-4 text-sm text-slate-500 mb-2">
                        <span>Designer: <b class="text-slate-900">{{ $order->designer->full_name ?? 'Belum ditentukan' }}</b>
                            @if ($order->designer?->rating)
                                <span class="text-amber-500">★ {{ number_format($order->designer->rating, 1) }}</span>
                            @endif
                        </span>
                        <span>Estimasi:
                            <b class="text-slate-900">
                                {{ $order->isCompleted() ? 'Selesai' : ($order->deadline?->translatedFormat('d M Y') ?? '-') }}
                            </b>
                        </span>
                    </div>

                    <p class="text-sm text-slate-400">
                       {{ $order->productTier->name ?? '-' }} • {{ $order->order_code }}
                    </p>

                    {{-- Progress bar --}}
                    @if ($tab === 'aktif' && $p['step'] > 0)
                        <div class="mt-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-semibold text-slate-900">
                                    Progres Pengerjaan: Tahap {{ $p['step'] }} dari {{ $p['total'] }}
                                </span>
                                <span class="font-semibold text-[#6D28D9]">{{ $p['percent'] }}% Selesai</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#6D28D9] to-[#D5FC55]"
                                     style="width: {{ $p['percent'] }}%"></div>
                            </div>

                            <div class="flex justify-between text-xs mt-2">
                                @foreach (\App\Models\Order::STEP_LABELS as $n => $text)
                                    <span class="{{ $p['step'] > $n ? 'text-[#6D28D9]' : ($p['step'] === $n ? 'font-semibold text-slate-900' : 'text-slate-400') }}">
                                        {{ $p['step'] > $n ? '✓ ' : '' }}{{ $n }}. {{ $text }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <a href="{{ route('customer.pesanan.show', $order->id) }}"
                   class="shrink-0 rounded-full bg-[#6D28D9] px-5 py-2.5 text-sm font-semibold text-white
                          hover:bg-[#5B21B6] transition">
                    Lihat Pesanan →
                </a>
            </div>
        </div>
    @empty
        <div class="border-t border-slate-200 pt-6">
            <div class="rounded-2xl border-2 border-dashed border-violet-200 bg-white/50 px-6 py-16 text-center">

                {{-- Ikon --}}
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-violet-100 bg-violet-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#6D28D9]" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-slate-900">
                    {{ $tab === 'riwayat' ? 'Belum Ada Riwayat Pesanan' : 'Belum Ada Pesanan Aktif' }}
                </h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    @if ($tab === 'riwayat')
                        Pesanan yang sudah selesai atau dibatalkan akan muncul di sini.
                    @else
                        Anda belum memiliki pesanan desain yang sedang berjalan. Temukan kreator terbaik dan mulai proyek impian Anda sekarang!
                    @endif
                </p>

                @if ($tab === 'aktif')
                    <a href="{{ route('customer.katalog') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-[#D5FC55] px-6 py-3 text-sm font-bold text-neutral-900
                              shadow-md transition hover:bg-[#c5ec45]">
                        Jelajahi Katalog Jasa Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div class="mt-10">{{ $orders->links('vendor.pagination.artiv') }}</div>
</div>
@endsection

@push('scripts')
{{-- Alpine.js via CDN (khusus halaman ini) --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush