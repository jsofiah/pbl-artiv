@extends('layouts.customer')

@section('title', 'Pesanan Saya')

@section('content')
<div class="max-w-7xl mx-auto py-8">

    {{-- Judul --}}
    <h1 class="text-4xl font-bold text-ink">Pesanan Saya</h1>
    <p class="text-muted mt-1">
        Pantau perkembangan pengerjaan proyek desain aktif dan riwayat transaksi kreatif Anda.
    </p>
    <div class="mt-3 h-1 w-24 rounded-full bg-primary"></div>

    {{-- Tab --}}
    <div class="flex gap-6 border-b border-line mt-6 mb-5">
        @foreach (['aktif' => ['Pesanan Aktif', $countAktif], 'riwayat' => ['Riwayat Selesai', $countRiwayat]] as $key => [$label, $count])
            <a href="{{ route('customer.pesanan', ['tab' => $key]) }}"
               class="pb-3 text-sm font-semibold flex items-center gap-2
                      {{ $tab === $key ? 'text-primary border-b-2 border-primary' : 'text-muted' }}">
                {{ $label }}
                <span class="rounded-full bg-line px-2 text-xs text-primary">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('customer.pesanan') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Cari nomor pesanan, nama jasa, atau desainer..."
               class="flex-1 min-w-[240px] rounded-full border border-line bg-white px-4 py-2 text-sm
                      placeholder:text-neutral focus:outline-none focus:ring-2 focus:ring-primary">

        <select name="status" onchange="this.form.submit()"
                class="rounded-full border border-line bg-white pl-4 pr-10 py-2 text-xs font-medium text-ink">
            <option value="">Status: Semua Status</option>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>Status: {{ $label }}</option>
            @endforeach
        </select>

        <select name="kategori" onchange="this.form.submit()"
                class="rounded-full border border-line bg-white pl-4 pr-10 py-2 text-xs font-medium text-ink">
            <option value="">Kategori: Semua Kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected(request('kategori') === $cat)>Kategori: {{ $cat }}</option>
            @endforeach
        </select>

        <select name="urut" onchange="this.form.submit()"
                class="rounded-full border border-line bg-white pl-4 pr-10 py-2 text-xs font-medium text-ink">
            <option value="deadline" @selected(request('urut', 'deadline') === 'deadline')>Urutkan: Deadline Terdekat</option>
            <option value="terbaru" @selected(request('urut') === 'terbaru')>Urutkan: Terbaru</option>
            <option value="terlama" @selected(request('urut') === 'terlama')>Urutkan: Terlama</option>
        </select>

        <a href="{{ route('customer.pesanan', ['tab' => $tab]) }}" title="Reset filter"
           class="flex h-9 w-9 items-center justify-center rounded-full border border-line bg-white text-muted hover:text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 4v5h5M20 20v-5h-5M5.6 15A8 8 0 0018.4 9M18.4 9A8 8 0 005.6 15" />
            </svg>
        </a>
    </form>

    {{-- Daftar pesanan --}}
    @forelse ($orders as $order)
        @php
            $p = $order->progress;
            $thumbUrl = \App\Helpers\R2Helper::url($order->product->thumbnail_url);
        @endphp

        <div class="bg-white rounded-2xl p-5 shadow-sm mb-4 border border-transparent">
            <div class="flex items-center gap-5">

                {{-- Thumbnail --}}
                <div class="w-28 h-28 shrink-0 rounded-xl bg-line/60 overflow-hidden flex items-center justify-center
                            text-[10px] font-semibold text-muted uppercase">
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="{{ $order->product->name }}" class="w-full h-full object-cover">
                    @else
                        Preview Jasa
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    {{-- Badge status --}}
                    <span class="inline-block rounded-full bg-primary px-3 py-0.5 text-[11px] font-semibold text-white">
                        @if ($tab === 'aktif' && $p['step'] > 0)
                            Tahap {{ $p['step'] }}: {{ $p['label'] }}
                        @else
                            {{ $p['label'] }}
                        @endif
                    </span>

                    <h2 class="text-lg font-bold text-ink mt-1 truncate">{{ $order->product->name }}</h2>

                    <div class="flex flex-wrap items-center gap-x-4 text-xs text-muted mt-1">
                        <span>Kreator: <b class="text-ink">{{ $order->designer->full_name ?? 'Belum ditentukan' }}</b>
                            @if ($order->designer?->rating)
                                <span class="text-amber-500">★ {{ number_format($order->designer->rating, 1) }}</span>
                            @endif
                        </span>
                        <span>Estimasi:
                            <b class="text-ink">
                                {{ $order->isCompleted() ? 'Selesai' : ($order->deadline?->translatedFormat('d M Y') ?? '-') }}
                            </b>
                        </span>
                    </div>

                    <p class="text-xs text-neutral mt-1">
                        {{ $order->productTier->name }} • {{ $order->order_code }}
                    </p>

                    {{-- Progress bar (hanya pesanan aktif yang sudah masuk tahap pengerjaan) --}}
                    @if ($tab === 'aktif' && $p['step'] > 0)
                        <div class="mt-3">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-semibold text-ink">
                                    Progres Pengerjaan: Tahap {{ $p['step'] }} dari {{ $p['total'] }}
                                </span>
                                <span class="font-semibold text-primary">{{ $p['percent'] }}% Selesai</span>
                            </div>
                            <div class="h-2 rounded-full bg-line overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-primary to-lime"
                                     style="width: {{ $p['percent'] }}%"></div>
                            </div>

                            <div class="flex justify-between text-[11px] mt-2">
                                @foreach (\App\Models\Order::STEP_LABELS as $n => $text)
                                    <span class="{{ $p['step'] > $n ? 'text-primary' : ($p['step'] === $n ? 'font-semibold text-ink' : 'text-neutral') }}">
                                        {{ $p['step'] > $n ? '✓ ' : '' }}{{ $n }}. {{ $text }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <a href="{{ route('customer.pesanan.show', $order->id) }}"
                   class="shrink-0 rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white
                          hover:bg-primary-dark transition">
                    Lihat Pesanan →
                </a>
            </div>
        </div>
    @empty
        <div class="border-t border-line pt-6">
            <div class="rounded-2xl border-2 border-dashed border-primary/25 bg-white/50 px-6 py-16 text-center">

                {{-- Ikon --}}
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-primary/15 bg-primary/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-ink">
                    {{ $tab === 'riwayat' ? 'Belum Ada Riwayat Pesanan' : 'Belum Ada Pesanan Aktif' }}
                </h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-muted">
                    @if ($tab === 'riwayat')
                        Pesanan yang sudah selesai atau dibatalkan akan muncul di sini.
                    @else
                        Anda belum memiliki pesanan desain yang sedang berjalan. Temukan kreator terbaik dan mulai proyek impian Anda sekarang!
                    @endif
                </p>

                @if ($tab === 'aktif')
                    <a href="{{ route('customer.katalog') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-lime px-6 py-3 text-sm font-bold text-ink
                              shadow-md transition hover:bg-lime-hover">
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
    <div class="mt-6">{{ $orders->links('vendor.pagination.artiv') }}</div>
</div>
@endsection