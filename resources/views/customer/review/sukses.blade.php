@extends('layouts.customer')

@section('title', 'Terima Kasih')

@section('content')
<div class="min-h-[70vh] bg-slate-100 px-4 py-10">
    <div class="mx-auto w-full max-w-xl rounded-2xl bg-white p-6 text-center shadow-sm sm:p-8">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#D5FC55] shadow">
            <svg class="h-7 w-7 text-slate-900" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>

        <h1 class="mt-5 text-2xl font-extrabold text-slate-900">Terima kasih atas ulasan &amp; penilaian Anda!</h1>
        <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">
            @if ($review->is_public)
                Ulasan Anda telah berhasil dipublikasikan pada profil Kreator {{ $order->creator->name ?? '' }} dan tercatat pada riwayat pesanan Anda.
            @else
                Ulasan Anda telah tersimpan dan tercatat pada riwayat pesanan Anda.
            @endif
        </p>

        <div class="mt-6 rounded-xl border border-violet-100 bg-violet-50/60 p-4 text-left">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-300' }}" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <span class="text-xs font-bold text-slate-800">{{ number_format($review->rating, 1) }} / 5.0</span>
                </div>
                <span class="rounded-full border border-violet-200 bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-[#6D28D9]">
                    ✓ Ulasan Terverifikasi
                </span>
            </div>

            @if ($review->comment)
                <p class="mt-3 text-xs italic leading-relaxed text-slate-700">{{ $review->comment }}</p>
            @endif

            <div class="mt-3 border-t border-violet-100 pt-2 text-[11px] text-slate-500">
                <span class="font-semibold text-slate-800">Oleh {{ auth()->user()->name }}</span>
                <span class="mx-1">•</span>
                {{ $review->created_at->translatedFormat('d M Y') }}
                <span class="mx-1">•</span>
                <a href="{{ route('customer.pesanan') }}" class="text-[#6D28D9] hover:underline">Pesanan {{ auth()->user()->name }}</a>
            </div>
        </div>

        <a href="{{ route('customer.pesanan') }}"
           class="mt-6 inline-block rounded-full bg-[#D5FC55] px-6 py-2.5 text-xs font-bold text-slate-900 shadow hover:bg-[#C5EC45]">
            Kembali ke Pesanan Saya
        </a>
    </div>
</div>
@endsection