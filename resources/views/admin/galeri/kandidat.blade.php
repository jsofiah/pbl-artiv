@extends('layouts.admin')
@section('title', 'Pilih dari Order Selesai')

@section('content')
<div class="space-y-6" x-data="{ selected: [] }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Pilih File dari Order Selesai</h1>
            <p class="text-slate-500 mt-1.5">Centang file hasil desain yang ingin ditampilkan di galeri publik.</p>
        </div>
        <a href="{{ route('admin.galeri.index') }}"
           class="inline-flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-5 py-2.5 rounded-full hover:border-[#6D28D9] hover:text-[#6D28D9] transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    @if (session('success'))
        <x-shared.alert type="success" :message="session('success')" :auto-hide="true" :duration="3000" />
    @endif
    @if (session('error'))
        <x-shared.alert type="error" :message="session('error')" :auto-hide="false" />
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="text-sm text-slate-500">
            @if ($q)
                Hasil pencarian untuk "<strong class="text-slate-700">{{ $q }}</strong>"
                · <a href="{{ route('admin.galeri.kandidat') }}" class="text-[#6D28D9] font-semibold hover:underline">Reset</a>
            @else
                Menampilkan <strong>{{ $kandidat->total() }}</strong> file kandidat
            @endif
        </div>

        <form method="GET" action="{{ route('admin.galeri.kandidat') }}" class="flex items-center">
            <div class="bg-white rounded-full shadow-sm border border-slate-200 p-1.5 flex items-center gap-2 w-[380px]">
                <div class="pl-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Cari nama file / order ID..."
                       class="flex-1 bg-transparent border-none focus:outline-none text-slate-700 placeholder:text-slate-400 px-1 py-1.5 text-sm">
                <button type="submit"
                        class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-3 py-1.5 rounded-full transition text-xs whitespace-nowrap">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.galeri.simpan') }}">
        @csrf

        @if ($kandidat->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
                <div class="w-16 h-16 rounded-full bg-violet-50 text-[#6D28D9] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <circle cx="8.5" cy="9.5" r="1.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 16l-5-5-8 8"/>
                    </svg>
                </div>
                <p class="text-slate-500 mb-1">Tidak ada file kandidat.</p>
                <p class="text-sm text-slate-400">Pastikan sudah ada order berstatus <b>Selesai</b> dengan file gambar.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($kandidat as $att)
                    @php
                        $order = $att->message?->conversation?->order;
                    @endphp
                    <label class="bg-white rounded-2xl border overflow-hidden cursor-pointer transition"
                        :class="selected.includes('{{ $att->id }}')
                            ? 'ring-2 ring-[#6D28D9] border-[#6D28D9]'
                            : 'border-slate-100 hover:border-[#6D28D9]/40'">
                        <input type="checkbox" name="attachment_ids[]" value="{{ $att->id }}"
                            class="sr-only" x-model="selected">

                        <div class="aspect-square bg-slate-100 overflow-hidden relative group">
                            <img src="{{ $att->preview_url }}" alt="{{ $att->file_name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                loading="lazy">

                            <div class="absolute top-2 right-2 w-6 h-6 rounded-full border flex items-center justify-center transition-colors duration-200"
                                :class="selected.includes('{{ $att->id }}')
                                    ? 'bg-[#6D28D9] border-[#6D28D9]'
                                    : 'bg-white/90 border-slate-300'">
                                <svg class="w-3.5 h-3.5 text-white transition-opacity duration-200"
                                    :class="selected.includes('{{ $att->id }}') ? 'opacity-100' : 'opacity-0'"
                                    fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>

                        <div class="p-3">
                            <p class="text-xs font-semibold text-slate-800 truncate" title="{{ $att->file_name }}">
                                {{ $att->file_name }}
                            </p>
                            <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                {{ $order?->product?->name ?? 'Tanpa produk' }} · {{ $att->humanSize() }}
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="sticky bottom-4 mt-6 bg-white rounded-2xl border border-slate-100 shadow-lg p-4 flex items-center justify-between">
                <p class="text-sm text-slate-600">
                    <span class="font-bold text-[#6D28D9]" x-text="selected.length"></span> file dipilih
                </p>
                <button type="submit"
                        :disabled="selected.length === 0"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#6D28D9] text-white text-sm font-semibold rounded-full
                               hover:bg-[#5B21B6] disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                    </svg>
                    Tambahkan ke Galeri
                </button>
            </div>
        @endif
    </form>

    @if ($kandidat->hasPages())
        <div class="mt-8 pt-5 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
            <p class="text-sm text-slate-500">
                Menampilkan {{ $kandidat->firstItem() }} - {{ $kandidat->lastItem() }} dari {{ $kandidat->total() }} file
            </p>

            <div class="flex items-center gap-2">
                <a href="{{ $kandidat->previousPageUrl() ?? '#' }}"
                   class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $kandidat->onFirstPage() ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }} transition">
                    &lt; Sebelumnya
                </a>

                @for ($i = 1; $i <= $kandidat->lastPage(); $i++)
                    <a href="{{ $kandidat->url($i) }}"
                       class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-semibold transition
                               {{ $i === $kandidat->currentPage()
                                   ? 'bg-[#6D28D9] text-white shadow-sm'
                                   : 'border border-slate-200 bg-white text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                        {{ $i }}
                    </a>
                @endfor

                <a href="{{ $kandidat->nextPageUrl() ?? '#' }}"
                   class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $kandidat->hasMorePages() ? 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' : 'text-slate-300 pointer-events-none' }} transition">
                    Selanjutnya &gt;
                </a>
            </div>
        </div>
    @endif

</div>
@endsection