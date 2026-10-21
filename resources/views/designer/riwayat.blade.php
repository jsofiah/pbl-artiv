@extends('layouts.designer')

@section('title', 'Riwayat Pekerjaan')

@section('content')
<div class="px-6 py-8 lg:px-10">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-ink">Riwayat Pekerjaan</h1>
            <p class="mt-1 text-sm text-muted">Semua pekerjaan yang sudah kamu selesaikan beserta kompensasi dan rating dari customer.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('designer.riwayat.csv', $filters) }}"
               class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-4 py-2 text-xs font-semibold text-ink hover:bg-artiv-bg">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
                Ekspor CSV
            </a>
            <a href="{{ route('designer.riwayat.cetak', $filters) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-4 py-2 text-xs font-semibold text-ink hover:bg-artiv-bg">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V3h12v6M6 18H4a1 1 0 01-1-1v-6a1 1 0 011-1h16a1 1 0 011 1v6a1 1 0 01-1 1h-2M6 14h12v7H6z"/></svg>
                Cetak Laporan
            </a>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('designer.riwayat') }}"
          class="mt-6 flex flex-col gap-3 rounded-xl border border-line bg-white p-3 lg:flex-row lg:items-center">
        <label class="flex flex-1 items-center gap-3 px-2">
            <svg class="h-4 w-4 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                   placeholder="Cari riwayat pekerjaan, tiket, atau customer..."
                   class="w-full border-0 bg-transparent p-0 text-sm text-ink placeholder-slate-400 focus:ring-0">
        </label>

        <div class="flex flex-wrap gap-2">
            <label class="flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-xs text-muted">
                Kategori:
                <select name="kategori" onchange="this.form.submit()"
                        class="border-0 bg-transparent p-0 pr-6 text-xs font-semibold text-ink focus:ring-0">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategori as $k)
                        <option value="{{ $k->id }}" @selected(($filters['kategori'] ?? '') == $k->id)>{{ $k->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-xs text-muted">
                Urutkan:
                <select name="urut" onchange="this.form.submit()"
                        class="border-0 bg-transparent p-0 pr-6 text-xs font-semibold text-ink focus:ring-0">
                    <option value="terbaru" @selected(($filters['urut'] ?? 'terbaru') === 'terbaru')>Terbaru Selesai</option>
                    <option value="terlama" @selected(($filters['urut'] ?? '') === 'terlama')>Terlama Selesai</option>
                    <option value="rating"  @selected(($filters['urut'] ?? '') === 'rating')>Rating Tertinggi</option>
                </select>
            </label>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="mt-5 overflow-hidden rounded-xl border border-line bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-sm">
                <thead class="bg-artiv-bg text-[11px] font-bold uppercase tracking-wide text-ink">
                    <tr>
                        <th class="px-6 py-4 text-left">Informasi Pekerjaan &amp; Jenis Jasa</th>
                        <th class="px-4 py-4 text-center">Tanggal Selesai</th>
                        <th class="px-4 py-4 text-center">Kompensasi</th>
                        <th class="px-4 py-4 text-center">Rating</th>
                        <th class="px-6 py-4 text-right">Tindakan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line">
                @forelse ($orders as $order)
                    @php $tgl = $order->completed_at ?? $order->updated_at; @endphp
                    <tr class="hover:bg-artiv-bg/50">
                        <td class="px-6 py-5">
                            <p class="text-xs text-muted">#{{ $order->order_code }}</p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-primary/20 bg-primary/10 px-3 py-0.5 text-xs font-medium text-primary">
                                    {{ $order->product->name ?? 'Layanan' }}
                                </span>
                                <span class="text-xs text-muted">{{ $order->customer->full_name ?? '-' }}</span>
                            </div>
                            <p class="mt-1.5 font-semibold text-ink">{{ \Illuminate\Support\Str::limit($order->brief_note, 70) ?: 'Pesanan ' . $order->order_code }}</p>
                            @if ($order->productTier)
                                <p class="mt-1 text-xs text-muted">Paket {{ $order->productTier->name }}</p>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-5 text-center text-ink">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                {{ $tgl?->translatedFormat('d M Y') }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-4 py-5 text-center font-semibold text-ink">
                            Rp {{ number_format($order->split->designer_earning ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-5 text-center">
                            @if ($order->review)
                                <span class="inline-flex items-center gap-1 text-ink">
                                    <svg class="h-4 w-4 text-primary" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.8 4.8 17.6l1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                                    <span class="font-semibold">{{ number_format($order->review->rating, 1) }}</span>
                                </span>
                            @else
                                <span class="text-xs text-muted">Belum ada rating</span>
                            @endif
                        </td>

                        <td class="px-6 py-5 text-right">
                            <a href="{{ route('designer.riwayat.show', $order) }}"
                               class="inline-flex items-center gap-1.5 rounded-full border border-line px-3.5 py-1.5 text-xs font-semibold text-ink hover:bg-artiv-bg">
                                Lihat Review
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14m-6-6l6 6-6 6"/></svg>
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
                               class="mt-4 inline-flex rounded-full bg-primary px-5 py-2 text-xs font-semibold text-white hover:bg-primary-dark">
                                Cari pekerjaan di Job Pool
                            </a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer: info + pagination --}}
        @if ($orders->total() > 0)
            <div class="flex flex-col gap-3 border-t border-line px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <span class="w-fit rounded-full bg-artiv-bg px-4 py-1.5 text-xs text-muted">
                    Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} pekerjaan
                </span>
                {{ $orders->links('vendor.pagination.artiv') }}
            </div>
        @endif
    </div>
</div>
@endsection