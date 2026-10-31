@extends('layouts.designer')

@section('title', 'Job Pool')

@section('content')
@php
    $cur   = $jobPool->currentPage();
    $last  = $jobPool->lastPage();
    $pages = collect([1, $last])->merge(range(max(1, $cur - 1), min($last, $cur + 1)))->unique()->sort()->values();

    $adaFilter = ($q ?? '') !== '' || ($kategori && $kategori !== 'semua');
@endphp

<div class="mx-auto max-w-6xl px-4 py-6">

    {{-- ============ ALERT ============ --}}
    @if (session('error'))
        <div role="alert" class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-8-4a1 1 0 00-1 1v3a1 1 0 002 0V7a1 1 0 00-1-1zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if (session('status'))
        <div role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- ============ FILTER BAR ============ --}}
    <form method="GET" action="{{ route('designer.job-pool') }}" id="form-job"
        class="mb-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">

        <div class="flex flex-wrap items-center gap-2">

            <div class="relative min-w-[220px] flex-1">
                <label for="cari-job" class="sr-only">Cari job</label>
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="cari-job" type="text" name="q" value="{{ $q }}"
                    placeholder="Cari berdasarkan kode order atau nama jasa..."
                    class="w-full rounded-full border border-slate-200 bg-slate-50 py-2.5 pl-12 pr-4 text-sm focus:border-[#6D28D9]/50 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30">
            </div>

            <label for="kategori" class="sr-only">Kategori</label>
            <select id="kategori" name="kategori"
                    onchange="this.form.submit()"
                    class="cursor-pointer rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30">
                <option value="semua">Semua Kategori</option>
                @foreach ($kategoris as $kat)
                    <option value="{{ $kat }}" @selected($kategori === $kat)>{{ $kat }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- ============ INFO & URUTAN ============ --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            <strong class="text-[#6D28D9]">{{ $totalTersedia }}</strong> Job Tersedia
        </span>
        <div class="flex items-center gap-2">
            <label for="urutkan" class="text-sm text-slate-500">Urutkan:</label>
            <select id="urutkan" name="urutkan" form="form-job" onchange="this.form.submit()"
                    class="cursor-pointer rounded-full border border-slate-200 bg-white px-4 py-1.5 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30">
                <option value="terbaru"      @selected($urutkan === 'terbaru')>Terbaru</option>
                <option value="deadline_asc" @selected($urutkan === 'deadline_asc')>Deadline terdekat</option>
                <option value="harga_desc"   @selected($urutkan === 'harga_desc')>Harga tertinggi</option>
                <option value="harga_asc"    @selected($urutkan === 'harga_asc')>Harga terendah</option>
            </select>
        </div>
    </div>

    {{-- ============ DAFTAR JOB ============ --}}
    <div class="space-y-3">
        @forelse ($jobPool as $job)
            @php
                $sisa     = $job->deadline ? (int) now()->startOfDay()->diffInDays($job->deadline->copy()->startOfDay(), false) : null;
                $mendesak = $sisa !== null && $sisa <= 2;
                $thumbPath = $job->product?->thumbnail_url;
                $thumbUrl  = $thumbPath ? \App\Helpers\R2Helper::url($thumbPath) : null;
                $inisial   = strtoupper(substr($job->product->name ?? 'J', 0, 1));
            @endphp

            <article class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:border-[#6D28D9]/40 hover:shadow-md">
                <span class="absolute inset-y-0 left-0 w-1 bg-[#6D28D9] opacity-0 transition group-hover:opacity-100"></span>

                <div class="flex flex-col gap-4 p-5 pl-6 md:flex-row md:items-center">

                    {{-- Thumbnail --}}
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-violet-100 to-violet-200 ring-1 ring-slate-200/50">
                        @if ($thumbUrl)
                            <img src="{{ $thumbUrl }}" alt="" class="h-full w-full object-cover"
                                 onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                        @endif
                        <span class="{{ $thumbUrl ? 'hidden' : '' }} text-4xl font-extrabold text-[#6D28D9]/50">{{ $inisial }}</span>
                    </div>

                    {{-- Info --}}
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-violet-100 bg-violet-50 px-2.5 py-0.5 text-xs font-bold text-[#6D28D9]">
                                {{ $job->product->name ?? 'Desain' }}
                            </span>
                            @if ($job->is_express)
                                <span class="rounded-full bg-[#D5FC55] px-2.5 py-0.5 text-xs font-bold text-[#1E1B39]">Express</span>
                            @endif
                            @if ($mendesak)
                                <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-600 ring-1 ring-red-200">
                                    {{ $sisa < 0 ? 'Lewat deadline' : 'Mendesak' }}
                                </span>
                            @endif
                            <span class="text-xs text-slate-400">{{ $job->order_code }}</span>
                        </div>

                        <h3 class="truncate text-lg font-bold text-[#1E1B39] transition group-hover:text-[#6D28D9]">
                            {{ $job->product->name ?? 'Jasa Desain' }}
                            @if ($job->productTier)
                                <span class="font-medium text-slate-400">&middot; {{ $job->productTier->name }}</span>
                            @endif
                        </h3>

                        <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5 {{ $mendesak ? 'font-semibold text-red-500' : '' }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $job->deadline?->translatedFormat('d M Y') ?? '-' }}
                                @if ($sisa !== null)
                                    <span class="font-normal">
                                        ({{ $sisa < 0 ? 'lewat ' . abs($sisa) . ' hari' : ($sisa === 0 ? 'hari ini' : $sisa . ' hari lagi') }})
                                    </span>
                                @endif
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                {{ $job->references->count() }} file referensi
                            </span>
                        </div>
                    </div>

                    {{-- Budget + Aksi --}}
                    <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-4 md:min-w-[180px] md:flex-col md:items-end md:border-0 md:pt-0">
                        <div class="md:text-right">
                            <p class="text-[10px] uppercase tracking-wide text-slate-400">Biaya</p>
                            <p class="text-xl font-extrabold text-[#6D28D9]">Rp {{ number_format($job->total_price ?? 0, 0, ',', '.') }}</p>
                        </div>
                        @php
                            $isExpired = $job->deadline && $job->deadline->isPast();
                        @endphp

                        @if ($isExpired)
                            <button type="button" disabled
                                    class="inline-flex shrink-0 items-center gap-2 rounded-full bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-400 cursor-not-allowed"
                                    title="Order sudah lewat deadline">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Lewat Deadline
                            </button>
                        @else
                            <a href="{{ route('designer.job-pool.show', $job->id) }}"
                            class="inline-flex shrink-0 items-center gap-2 rounded-full bg-[#6D28D9] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#5B21B6] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#6D28D9] focus-visible:ring-offset-2">
                                Lihat Detail
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-slate-100 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-violet-100 to-violet-200">
                    <svg class="h-10 w-10 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-bold text-[#1E1B39]">
                    {{ $adaFilter ? 'Job tidak ditemukan' : 'Belum ada job tersedia' }}
                </h3>
                <p class="mx-auto mb-6 max-w-md text-sm text-slate-500">
                    @if ($adaFilter)
                        Tidak ada job yang cocok dengan pencarian atau filter kamu. Ubah kata kunci atau reset filter untuk melihat semua job.
                    @else
                        Job baru akan muncul di sini setelah customer menyelesaikan pembayaran. Cek lagi sebentar lagi.
                    @endif
                </p>
                <a href="{{ route('designer.job-pool') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-[#D5FC55] px-5 py-2.5 text-sm font-semibold text-[#1E1B39] transition hover:bg-[#C5EC45]">
                    {{ $adaFilter ? 'Reset filter' : 'Muat ulang' }}
                </a>
            </div>
        @endforelse
    </div>

    {{-- ============ PAGINATION ============ --}}
    @if ($jobPool->hasPages())
        <nav class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5" aria-label="Pagination">
            @if ($jobPool->onFirstPage())
                <span class="cursor-not-allowed rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-400">&larr; Sebelumnya</span>
            @else
                <a href="{{ $jobPool->previousPageUrl() }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">&larr; Sebelumnya</a>
            @endif

            <div class="flex items-center gap-1.5">
                @foreach ($pages as $i => $page)
                    @if ($i > 0 && $page - $pages[$i - 1] > 1)
                        <span class="px-1 text-slate-400">&hellip;</span>
                    @endif

                    @if ($page === $cur)
                        <span aria-current="page" class="flex h-10 w-10 items-center justify-center rounded-full bg-[#6D28D9] text-sm font-bold text-white shadow-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $jobPool->url($page) }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-600 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">{{ $page }}</a>
                    @endif
                @endforeach
            </div>

            @if ($jobPool->hasMorePages())
                <a href="{{ $jobPool->nextPageUrl() }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">Selanjutnya &rarr;</a>
            @else
                <span class="cursor-not-allowed rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-400">Selanjutnya &rarr;</span>
            @endif
        </nav>
    @endif
</div>
@endsection