@extends('layouts.designer')

@section('title', 'Detail Riwayat Pekerjaan')

@php
    $review   = $order->review;
    $customer = $order->customer;
    $inisial  = collect(explode(' ', $customer->full_name ?? 'C'))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $hasil    = $order->deliverables->firstWhere('status', \App\Models\Deliverable::STATUS_APPROVED)
                ?? $order->deliverables->sortByDesc('created_at')->first();
    // File hasil = lampiran pada pesan chat yang terhubung ke deliverable
    $filesHasil = $hasil?->message?->attachments ?? collect();

    // Linimasa dari timestamp yang ada di tabel orders & deliverables
    $timeline = collect([
        ['judul' => 'Pesanan dibuat',           'waktu' => $order->created_at],
        ['judul' => 'Kamu mengambil pekerjaan', 'waktu' => $order->assigned_at],
    ]);
    foreach ($order->deliverables->sortBy('created_at')->values() as $i => $d) {
        $timeline->push(['judul' => $i === 0 ? 'Hasil pertama dikirim' : 'Hasil revisi dikirim', 'waktu' => $d->created_at]);
        if ($d->approved_at) {
            $timeline->push(['judul' => 'Customer menyetujui hasil', 'waktu' => $d->approved_at]);
        }
    }
    $timeline->push(['judul' => 'Pesanan selesai', 'waktu' => $order->completed_at]);
    $timeline = $timeline->filter(fn ($t) => $t['waktu'])->sortBy('waktu')->values();

    // Kalau order punya log, pakai log asli (lebih lengkap dari timestamp di atas)
    if ($order->logs->isNotEmpty()) {
        $timeline = $order->logs->sortBy('created_at')->values()->map(fn ($l) => [
            'judul'   => \App\Models\Order::STATUS_LABELS[$l->status] ?? ucfirst(str_replace('_', ' ', $l->action)),
            'catatan' => $l->note,
            'waktu'   => $l->created_at,
        ]);
    }

    $star = fn ($cls) => '<svg class="'.$cls.'" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.8 4.8 17.6l1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>';
@endphp

@section('content')
<div class="space-y-5">

    {{-- Tombol Kembali & Judul --}}
    <div>
        <a href="{{ route('designer.riwayat') }}"
           class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#6D28D9] mb-4 transition rounded-full px-3 py-2 bg-white border border-slate-200 hover:bg-[#D5FC55] hover:text-[#1A1A1A] hover:border-[#D5FC55]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Riwayat
        </a>
        
        <h1 class="text-3xl font-extrabold text-slate-900">Detail Riwayat Pekerjaan</h1>
        <p class="text-slate-500 mt-1.5">
            Informasi lengkap mengenai hasil pekerjaan yang telah diselesaikan.
        </p>
    </div>

    {{-- Header pekerjaan --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-line bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $order->product->name ?? 'Pekerjaan' }}</h1>
                <span class="rounded-md bg-lime px-2 py-0.5 text-[10px] font-bold uppercase text-ink">Selesai</span>
            </div>
            <p class="mt-1 text-sm text-muted">#{{ $order->order_code }} · selesai {{ $order->completed_at?->translatedFormat('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-3 rounded-full bg-artiv-bg px-4 py-2.5">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            </span>
            <div>
                <p class="text-[10px] font-semibold uppercase text-muted">Biaya</p>
                <p class="text-sm font-bold text-ink">Rp {{ number_format($order->split->designer_earning ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Review & Rating Customer --}}
    <section class="rounded-2xl border border-line bg-white p-5">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            </span>
            <div>
                <h2 class="font-bold text-ink">Review &amp; Rating Customer</h2>
                <p class="text-xs text-muted">Penilaian dari customer untuk pekerjaan ini</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 rounded-2xl bg-artiv-bg p-4 lg:grid-cols-[2fr_3fr]">
            {{-- Rating rata-rata --}}
            <div class="flex flex-col items-center justify-center rounded-xl bg-white p-5">
                <p class="text-center text-5xl font-extrabold text-primary">{{ number_format($rataRata, 1) }}</p>
                <div class="mt-2 flex justify-center">
                    @for ($i = 1; $i <= 5; $i++) {!! $star('h-5 w-5 ' . ($i <= round($rataRata) ? 'text-primary' : 'text-line')) !!} @endfor
                </div>
            </div>

            {{-- Review customer ini --}}
            <div class="rounded-xl bg-white p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if ($customer?->avatar_url)
                            <img src="{{ $customer->avatar_url }}" alt="" class="h-10 w-10 rounded-full object-cover">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">{{ $inisial }}</span>
                        @endif
                        <div>
                            <p class="font-semibold text-ink">{{ $customer->full_name ?? 'Customer' }}</p>
                            <p class="text-xs text-muted">{{ $review?->created_at?->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex">
                        @for ($i = 1; $i <= 5; $i++) {!! $star('h-4 w-4 ' . ($i <= ($review->rating ?? 0) ? 'text-primary' : 'text-line')) !!} @endfor
                    </div>
                </div>

                @if (!empty($review?->aspects))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ((array) $review->aspects as $key)
                            @php $aspek = \App\Models\Review::ASPECTS[$key] ?? null; @endphp
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-[11px] font-medium text-primary">
                                {{ $aspek['icon'] ?? '✓' }} {{ $aspek['label'] ?? $key }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <p class="mt-4 whitespace-pre-line break-words text-sm leading-relaxed text-ink">
                    {{ $review->comment ?: 'Customer tidak menulis komentar.' }}
                </p>
            </div>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[3fr_2fr]">
        <div class="space-y-5">
            {{-- Informasi pekerjaan --}}
            <section class="rounded-2xl border border-line bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5h6M9 3h6a2 2 0 012 2v16H7V5a2 2 0 012-2z"/></svg>
                        </span>
                        <h2 class="font-bold text-ink">Informasi Pekerjaan</h2>
                    </div>
                    <span class="rounded-md bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">Read-Only View</span>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-4 rounded-xl bg-artiv-bg p-4 text-sm">
                    <div><dt class="text-xs text-muted">Kode pesanan</dt><dd class="font-semibold text-ink">{{ $order->order_code }}</dd></div>
                    <div><dt class="text-xs text-muted">Layanan</dt><dd class="font-semibold text-ink">{{ $order->product->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-muted">Paket</dt><dd class="font-semibold text-ink">{{ $order->productTier->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-muted">Customer</dt><dd class="font-semibold text-ink">{{ $customer->full_name ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-muted">Deadline</dt><dd class="font-semibold text-ink">{{ $order->deadline?->translatedFormat('d M Y') ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-muted">Jumlah revisi</dt><dd class="font-semibold text-ink">{{ $order->revision_count }}×</dd></div>
                </dl>

                <p class="mt-5 text-xs font-semibold text-muted">Brief customer</p>
                <div class="mt-2 whitespace-pre-line rounded-xl bg-artiv-bg p-4 text-sm leading-relaxed text-ink">{{ $order->brief_note ?: 'Customer tidak menulis brief.' }}</div>

                <p class="mt-5 text-xs font-semibold text-muted">Lampiran &amp; referensi customer</p>
                <div class="mt-2 grid gap-3 sm:grid-cols-2">
                    @forelse ($order->references as $ref)
                        <div class="flex items-center gap-3 rounded-xl bg-artiv-bg p-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-line bg-white text-muted">
                                @if ($ref->isLink())
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 14a4 4 0 005.7 0l3-3a4 4 0 00-5.7-5.7l-1 1M14 10a4 4 0 00-5.7 0l-3 3a4 4 0 005.7 5.7l1-1"/></svg>
                                @else
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/></svg>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-semibold text-ink">
                                    {{ $ref->isLink() ? $ref->external_url : ($ref->file_name ?: 'Referensi ' . $loop->iteration) }}
                                </p>
                                @if ($ref->note)
                                    <p class="truncate text-[11px] text-muted">{{ $ref->note }}</p>
                                @elseif ($ref->isFile() && $ref->file_size)
                                    <p class="text-[11px] text-muted">{{ number_format($ref->file_size / 1024, 0) }} KB</p>
                                @endif
                            </div>
                            @if ($ref->isLink())
                                <a href="{{ $ref->external_url }}" target="_blank" rel="noopener" class="text-muted hover:text-ink" title="Buka tautan">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 4h6v6M10 14L20 4M20 14v6H4V4h6"/></svg>
                                </a>
                            @else
                                <a href="{{ route('designer.pekerjaan.reference.download', [$order, $ref]) }}" class="text-muted hover:text-ink" title="Unduh">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-muted sm:col-span-2">Tidak ada lampiran.</p>
                    @endforelse
                </div>
            </section>

            {{-- Hasil akhir --}}
            <section class="rounded-2xl border border-line bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <h2 class="font-bold text-ink">Hasil Akhir Pekerjaan</h2>
                    </div>
                    <span class="rounded-md bg-lime px-2 py-0.5 text-[10px] font-bold text-ink">Final Approved</span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($filesHasil as $file)
                        <div class="flex items-center gap-3 rounded-xl bg-artiv-bg p-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-line bg-white text-muted">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink">{{ $file->file_name }}</p>
                                <p class="text-xs text-muted">
                                    {{ $file->humanSize() }}
                                    @if ($hasil?->approved_at) · disetujui {{ $hasil->approved_at->translatedFormat('d M Y, H:i') }} @endif
                                </p>
                            </div>
                            <a href="{{ route('designer.riwayat.hasil.download', [$order, $file]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-lime px-3 py-2 text-xs font-bold text-ink hover:bg-lime-hover">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
                                Unduh Hasil
                            </a>
                        </div>
                    @empty
                        <p class="rounded-xl bg-artiv-bg p-4 text-sm text-muted">Belum ada file hasil yang tercatat untuk pekerjaan ini.</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Riwayat status --}}
        <section class="h-fit rounded-2xl border border-line bg-white p-5">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8"/></svg>
                </span>
                <h2 class="font-bold text-ink">Riwayat Status Pesanan</h2>
            </div>

            <ol class="relative mt-5 space-y-4 border-l-2 border-line pl-6">
                @foreach ($timeline as $t)
                    <li class="relative">
                        <span class="absolute -left-[33px] top-1 h-4 w-4 rounded-full border-4 border-white {{ $loop->last ? 'bg-lime' : 'bg-primary' }} ring-1 ring-line"></span>
                        <p class="text-sm font-semibold text-ink">{{ $t['judul'] }}</p>
                        @if (!empty($t['catatan']))
                            <p class="mt-1 text-xs text-muted">{{ $t['catatan'] }}</p>
                        @endif
                        <p class="mt-1 rounded-lg bg-artiv-bg px-3 py-1.5 text-xs text-muted">{{ $t['waktu']->translatedFormat('d M Y, H:i') }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>

    <div class="flex justify-center rounded-2xl border border-line bg-white p-5">
        <a href="{{ route('designer.riwayat') }}"
           class="inline-flex items-center gap-2 rounded-full bg-lime px-8 py-3 text-sm font-bold text-ink hover:bg-lime-hover">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5m6-6l-6 6 6 6"/></svg>
            Kembali ke Riwayat Pekerjaan
        </a>
    </div>
</div>
@endsection