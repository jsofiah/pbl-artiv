@extends('layouts.designer')

@section('title', 'Order Berhasil Diambil')

@section('content')
@php
    $thumbPath = $order->product?->thumbnail_url;
    $thumbUrl  = $thumbPath ? \App\Helpers\R2Helper::url($thumbPath) : null;
    $inisial   = strtoupper(substr($order->product->name ?? 'J', 0, 1));
    $deadline  = $order->deadline;
    $estimasi  = $order->productTier->estimated_days ?? null;

    $sisa = $deadline ? now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false) : null;

    // Status sisa waktu
    $isLate  = $sisa !== null && $sisa < 0;
    $isToday = $sisa === 0;
@endphp

<style>
    @keyframes sukses-ring  { 0% { transform: scale(.5); opacity: 0 } 60% { opacity: 1 } 100% { transform: scale(1); opacity: 1 } }
    @keyframes sukses-pulse { 0% { transform: scale(1); opacity: .5 } 100% { transform: scale(1.8); opacity: 0 } }
    @keyframes sukses-check { to { stroke-dashoffset: 0 } }

    .sukses-ring  { animation: sukses-ring .5s cubic-bezier(.2,.8,.2,1) both }
    .sukses-pulse { animation: sukses-pulse 1.6s ease-out .5s infinite }
    .sukses-check { stroke-dasharray: 26; stroke-dashoffset: 26; animation: sukses-check .5s ease-out .4s forwards }

    @media (prefers-reduced-motion: reduce) {
        .sukses-ring, .sukses-pulse, .sukses-check { animation: none; stroke-dashoffset: 0; opacity: 1 }
        .sukses-pulse { display: none }
    }
</style>

<div class="mx-auto py-8">
    <div class="mb-10 text-center">
        <div class="relative mx-auto mb-6 flex h-28 w-28 items-center justify-center">
            <span class="sukses-pulse absolute inset-0 rounded-full bg-[#D5FC55]/60"></span>
            <span class="sukses-ring relative flex h-24 w-24 items-center justify-center rounded-full bg-[#6D28D9] shadow-xl shadow-[#6D28D9]/30 ring-8 ring-[#6D28D9]/10">
                <svg class="h-12 w-12 text-[#D5FC55]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path class="sukses-check" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
        </div>

        <span class="inline-block px-3 py-1 rounded-full bg-[#D5FC55] text-neutral-900 text-xs font-bold uppercase tracking-wide mb-3">
            Berhasil
        </span>

        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-[#1E1B39] mb-3">
            Order Berhasil Diambil!
        </h1>
        <p class="mx-auto max-w-md text-slate-500 leading-relaxed">
            Order ini sekarang ada di daftar <strong class="text-[#1E1B39]">Pekerjaan Saya</strong> kamu dan tidak bisa diambil designer lain.
        </p>
    </div>

    <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">

        <div class="relative flex items-center gap-5 p-6 border-b border-slate-100 bg-gradient-to-br from-[#F3F1FA] to-white">
            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-[#6D28D9]/10">
                @if ($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="Thumbnail {{ $order->product->name ?? '' }}"
                         class="h-full w-full object-cover"
                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                @endif
                <span class="{{ $thumbUrl ? 'hidden' : '' }} text-4xl font-extrabold text-[#6D28D9]/50">{{ $inisial }}</span>
            </div>

            <div class="min-w-0 flex-1">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-white px-2.5 py-0.5 text-xs font-bold text-[#6D28D9] ring-1 ring-[#6D28D9]/15">
                        {{ $order->order_code }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Sedang Dikerjakan
                    </span>
                </div>
                <h2 class="truncate text-2xl font-bold text-[#1E1B39]">{{ $order->product->name ?? 'Jasa' }}</h2>
                <p class="text-sm text-slate-500">{{ $order->productTier->name ?? '-' }}</p>
            </div>
        </div>

        <div class="grid gap-3 p-6 sm:grid-cols-3">
            <div class="flex items-center gap-3 rounded-2xl border border-slate-100 p-4 transition hover:border-[#6D28D9]/30 hover:bg-[#F3F1FA]/50">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#F3F1FA] text-[#6D28D9]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-3.5 3.6-6 8-6s8 2.5 8 6"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Customer</p>
                    <p class="truncate font-semibold text-[#1E1B39]">{{ $order->customer->full_name ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-slate-100 p-4 transition hover:border-[#6D28D9]/30 hover:bg-[#F3F1FA]/50">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#F3F1FA] text-[#6D28D9]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Deadline</p>
                    <p class="truncate font-semibold text-[#1E1B39]">{{ $deadline?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border p-4 transition
                        {{ $isLate ? 'border-red-100 bg-red-50/50 hover:border-red-200' : 'border-slate-100 hover:border-[#6D28D9]/30 hover:bg-[#F3F1FA]/50' }}">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                             {{ $isLate ? 'bg-red-100 text-red-500' : 'bg-[#F3F1FA] text-[#6D28D9]' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        @if ($isLate)
                            <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        @else
                            <circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>
                        @endif
                    </svg>
                </span>
                <div class="min-w-0">
                    @if ($sisa !== null)
                        <p class="text-xs {{ $isLate ? 'text-red-500' : 'text-slate-500' }}">
                            {{ $isLate ? 'Terlambat' : 'Sisa waktu' }}
                        </p>
                        <p class="truncate font-semibold {{ $isLate ? 'text-red-600' : 'text-[#1E1B39]' }}">
                            @if ($isLate) Lewat {{ abs($sisa) }} hari
                            @elseif ($isToday) Hari ini
                            @else {{ $sisa }} hari lagi
                            @endif
                        </p>
                    @else
                        <p class="text-xs text-slate-500">Estimasi</p>
                        <p class="truncate font-semibold text-[#1E1B39]">{{ $estimasi ? $estimasi . ' hari' : '-' }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="relative mx-6 mb-6 overflow-hidden rounded-2xl bg-[#6D28D9] px-6 py-5">
            <span class="absolute inset-y-0 left-0 w-1.5 bg-[#D5FC55]"></span>
            <div class="flex items-center justify-between gap-4 pl-2">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-medium text-white/70">Total Budget</p>
                </div>

                <p class="text-3xl font-extrabold tracking-tight text-[#D5FC55]">
                    Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 pb-7 pt-5">
            <h3 class="mb-4 font-bold text-[#1E1B39]">Langkah Berikutnya</h3>
            <ol class="relative space-y-5">
                <span class="absolute left-3 top-3 bottom-3 w-px bg-[#6D28D9]/15" aria-hidden="true"></span>

                <li class="relative flex gap-4">
                    <span class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#6D28D9] text-xs font-bold text-white">1</span>
                    <div>
                        <p class="text-sm font-semibold text-[#1E1B39]">Pelajari brief</p>
                        <p class="text-sm text-slate-500">Baca ulang brief dan unduh file referensi di halaman pekerjaan.</p>
                    </div>
                </li>
                <li class="relative flex gap-4">
                    <span class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#F3F1FA] text-xs font-bold text-[#6D28D9] ring-1 ring-[#6D28D9]/25">2</span>
                    <div>
                        <p class="text-sm font-semibold text-[#1E1B39]">Konfirmasi ke customer</p>
                        <p class="text-sm text-slate-500">Tanyakan lewat chat kalau ada detail yang belum jelas.</p>
                    </div>
                </li>
                <li class="relative flex gap-4">
                    <span class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#F3F1FA] text-xs font-bold text-[#6D28D9] ring-1 ring-[#6D28D9]/25">3</span>
                    <div>
                        <p class="text-sm font-semibold text-[#1E1B39]">Kirim hasil desain</p>
                        <p class="text-sm text-slate-500">Kirim sebelum deadline supaya customer bisa langsung mereview.</p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row">
        <a href="{{ route('designer.job-pool') }}"
        class="flex-1 rounded-full border-2 border-slate-200 bg-white px-6 py-3.5 text-center font-semibold text-slate-600 transition hover:border-[#6D28D9] hover:text-[#6D28D9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#6D28D9] focus-visible:ring-offset-2">
            &larr; Kembali ke Job Pool
        </a>
        <a href="{{ route('designer.pekerjaan.index') }}"
        class="flex-1 rounded-full bg-[#D5FC55] px-6 py-3.5 text-center font-bold text-neutral-900 transition hover:bg-[#C5EC45] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#6D28D9] focus-visible:ring-offset-2">
            Lihat Pekerjaan &rarr;
        </a>
    </div>
</div>
@endsection