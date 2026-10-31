@extends('layouts.designer')

@section('title', 'Pekerjaan Saya')

@section('content')
<div>
    <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Pekerjaan Saya</h1>
            <p class="text-slate-500 mt-1.5">
                Kelola pesanan yang sedang dan sudah kamu kerjakan sebagai designer.
            </p>
        </div>
        <span class="px-4 py-2 rounded-full bg-white text-[#6D28D9] text-sm font-semibold shrink-0">
            Total: {{ $totalSemua }}
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Total Pekerjaan</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSemua }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Dikerjakan</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $totalDikerjakan }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Selesai</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $totalSelesai }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('designer.pekerjaan.index') }}" class="mb-6">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="flex flex-wrap items-center gap-3">

        {{-- Search Bar --}}
        <div class="flex-1 min-w-[280px] bg-white rounded-full shadow-sm border border-slate-100 p-2 flex items-center gap-2 focus-within:border-[#6D28D9] transition-colors duration-200">
            <div class="pl-4 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <input type="text" name="q" value="{{ $kataKunci }}"
                placeholder="Cari order code, layanan, atau customer..."
                class="flex-1 bg-transparent border-0 ring-0 focus:ring-0 focus:outline-none focus:border-transparent focus:bg-transparent shadow-none focus:shadow-none text-slate-700 placeholder:text-slate-400 px-2 py-2 text-sm">
            <button type="submit"
                    class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-5 py-2 rounded-full transition text-sm whitespace-nowrap">
                Cari
            </button>
        </div>

            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open"
                        class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-5 pr-4 py-3.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[200px] justify-between">
                    <span>
                        @php
                            $statusLabels = [
                                'semua'      => 'Status: Semua',
                                'dikerjakan' => 'Status: Dikerjakan',
                                'selesai'    => 'Status: Selesai',
                            ];
                        @endphp
                        {{ $statusLabels[$status] ?? 'Status: Semua' }}
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false"
                    class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 max-h-72 overflow-y-auto"
                    style="display: none;">
                    @foreach (['semua' => 'Semua', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai'] as $value => $label)
                        <a href="{{ route('designer.pekerjaan.index', array_merge(request()->except('status', 'page'), ['status' => $value])) }}"
                        class="block w-full text-left px-5 py-2 text-sm transition
                                {{ $status === $value ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            @if ($kataKunci !== '' || $status !== 'semua')
                <a href="{{ route('designer.pekerjaan.index') }}" title="Reset filter"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:text-[#6D28D9] hover:border-[#6D28D9] transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </a>
            @endif

        </div>
    </form>

    @if ($kataKunci !== '' || $status !== 'semua')
        <div class="mb-4 text-sm text-slate-500">
            Menampilkan <strong>{{ $pekerjaan->total() }}</strong> pekerjaan
            @if ($kataKunci !== '')
                untuk pencarian "<strong>{{ $kataKunci }}</strong>"
            @endif
            @if ($status !== 'semua')
                dengan status <strong>{{ ucfirst($status) }}</strong>
            @endif
            .
            <a href="{{ route('designer.pekerjaan.index') }}"
                class="text-[#6D28D9] font-semibold hover:underline ml-2">
                Reset filter
            </a>
        </div>
    @endif

    @if ($pekerjaan->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 py-20 text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-50">
                <svg class="h-8 w-8 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h7m4-5h6m-3-3v6"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900">
                {{ ($kataKunci !== '' || $status !== 'semua') ? 'Tidak Ada Hasil' : 'Belum Ada Pekerjaan' }}
            </h3>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                @if ($kataKunci !== '' || $status !== 'semua')
                    Tidak ada pekerjaan yang cocok dengan filter kamu. Coba ubah kata kunci atau reset filter.
                @else
                    Pekerjaan yang masuk akan muncul di sini.
                @endif
            </p>
            @if ($kataKunci !== '' || $status !== 'semua')
                <a href="{{ route('designer.pekerjaan.index') }}"
                    class="mt-5 inline-flex items-center gap-2 rounded-full bg-[#D5FC55] px-5 py-2.5 text-sm font-bold text-neutral-900 hover:bg-[#c5ec45] transition">
                    Reset Filter
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($pekerjaan as $order)
                <x-designer.card :order="$order" context="default">
                    <x-slot:action>
                        <a href="{{ route('designer.pekerjaan.show', $order->id) }}"
                        class="w-full inline-flex items-center justify-center gap-1.5 border border-slate-200 hover:bg-[#D5FC55] hover:text-[#1A1A1A] text-slate-700 text-sm font-semibold rounded-full py-2.5 transition">
                            Lihat Pekerjaan
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </a>
                    </x-slot:action>
                </x-designer.card>
            @endforeach
        </div>

        @if ($pekerjaan->total() > 0)
            <nav class="mt-8 flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between" aria-label="Pagination">
                
                <div class="text-sm text-slate-500">
                    Menampilkan {{ $pekerjaan->firstItem() }} &ndash; {{ $pekerjaan->lastItem() }} dari <span class="font-semibold text-slate-700">{{ $pekerjaan->total() }}</span> Pekerjaan
                </div>

                <div class="flex items-center gap-2">
                    @if ($pekerjaan->onFirstPage())
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-400 cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Sebelumnya
                        </span>
                    @else
                        <a href="{{ $pekerjaan->previousPageUrl() }}" class="inline-flex items-center gap-1 rounded-full bg-white border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Sebelumnya
                        </a>
                    @endif

                    <div class="flex items-center gap-1.5">
                        @foreach ($pekerjaan->getUrlRange(max(1, $pekerjaan->currentPage() - 1), min($pekerjaan->lastPage(), $pekerjaan->currentPage() + 1)) as $page => $url)
                            @if ($page == $pekerjaan->currentPage())
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#6D28D9] text-sm font-bold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-600 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($pekerjaan->currentPage() + 1 < $pekerjaan->lastPage())
                            <span class="px-1 text-slate-400">...</span>
                            <a href="{{ $pekerjaan->url($pekerjaan->lastPage()) }}" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-600 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">{{ $pekerjaan->lastPage() }}</a>
                        @endif
                    </div>

                    @if ($pekerjaan->hasMorePages())
                        <a href="{{ $pekerjaan->nextPageUrl() }}" class="inline-flex items-center gap-1 rounded-full bg-white border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-[#6D28D9] hover:text-[#6D28D9]">
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
    @endif

</div>
@endsection