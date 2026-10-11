@extends('layouts.customer')

@section('title', 'Katalog Jasa')

@section('content')
<div class="max-w-7xl mx-auto py-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl font-extrabold text-slate-900 mb-3">Butuh desain?</h1>
        <p class="text-slate-500 max-w-2xl mx-auto">
            Temukan berbagai layanan desain grafis di sini.
        </p>
    </div>

    {{-- Search Bar --}}
    <form method="GET" action="{{ route('customer.katalog') }}" class="mb-6">
        <div class="bg-white rounded-full shadow-sm border border-slate-100 p-2 flex items-center gap-2 max-w-3xl mx-auto">
            <div class="pl-4 text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari jasa desain, logo, kemasan, ilustrasi..."
                   class="flex-1 bg-transparent border-none focus:outline-none text-slate-700 placeholder:text-slate-400 px-2 py-2">
            <button type="submit"
                    class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-6 py-2.5 rounded-full transition">
                Cari Jasa →
            </button>
        </div>
    </form>

    {{-- Filter Bar --}}
    <div class="mb-6 flex items-center gap-3">

        {{-- Kategori: scroll horizontal --}}
        <div class="flex-1 min-w-0 overflow-x-auto scrollbar-thin">
            <div class="flex items-center gap-2 w-max">

                {{-- Semua --}}
                <a href="{{ route('customer.katalog', request()->except('category', 'page')) }}"
                class="shrink-0 px-5 py-2.5 rounded-full text-sm font-medium transition border
                        {{ !request('category') || request('category') === 'Semua Kategori'
                                ? 'bg-[#6D28D9] text-white border-[#6D28D9]'
                                : 'bg-white text-slate-700 border-slate-200 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                    Semua
                </a>

                {{-- Kategori dari DB --}}
                @foreach ($categories as $cat)
                    <a href="{{ route('customer.katalog', array_merge(request()->except('page'), ['category' => $cat])) }}"
                    class="shrink-0 px-5 py-2.5 rounded-full text-sm font-medium transition border
                            {{ request('category') === $cat
                                    ? 'bg-[#6D28D9] text-white border-[#6D28D9]'
                                    : 'bg-white text-slate-700 border-slate-200 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                        {{ $cat }}
                    </a>
                @endforeach

            </div>
        </div>

        {{-- Sort: tetap di kanan --}}
        <div x-data="{ open: false }" class="relative shrink-0">
            <button type="button" @click="open = !open"
                    class="flex items-center gap-2 w-max bg-white border border-slate-200 rounded-full pl-5 pr-4 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[200px] justify-between">
                <span>
                    @php
                        $sortLabels = [
                            'cheapest' => 'Urutkan: Termurah',
                            'expensive' => 'Urutkan: Termahal',
                        ];
                    @endphp
                    {{ $sortLabels[request('sort')] ?? 'Urutkan: Terpopuler' }}
                </span>
                <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" @click.outside="open = false"
                    class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 right-0"
                    style="display: none;">
                <a href="{{ route('customer.katalog', request()->except('sort', 'page')) }}"
                    class="block w-full text-left px-5 py-2 text-sm text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9] transition">
                    Terpopuler
                </a>
                <a href="{{ route('customer.katalog', array_merge(request()->except('page'), ['sort' => 'cheapest'])) }}"
                    class="block w-full text-left px-5 py-2 text-sm transition
                            {{ request('sort') === 'cheapest' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                    Termurah
                </a>
                <a href="{{ route('customer.katalog', array_merge(request()->except('page'), ['sort' => 'expensive'])) }}"
                    class="block w-full text-left px-5 py-2 text-sm transition
                            {{ request('sort') === 'expensive' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                    Termahal
                </a>
            </div>
        </div>

    </div>

    {{-- Info Hasil Pencarian --}}
    @if (request()->hasAny(['q', 'category']))
        <div class="mb-4 text-sm text-slate-500">
            Menampilkan <strong>{{ $products->total() }}</strong> hasil
            @if (request('q')) untuk pencarian "<strong>{{ request('q') }}</strong>"@endif
            @if (request('category')) di kategori "<strong>{{ request('category') }}</strong>"@endif
            .
            <a href="{{ route('customer.katalog') }}" class="text-[#6D28D9] font-semibold hover:underline ml-2">
                Reset filter
            </a>
        </div>
    @endif

    {{-- Product Grid --}}
    @if ($products->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-100">
            <div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <p class="text-slate-500 font-semibold">Jasa tidak ditemukan</p>
            <p class="text-sm text-slate-400 mt-1">Coba kata kunci lain atau ubah filter pencarian.</p>
            <a href="{{ route('customer.katalog') }}"
                class="inline-block mt-4 text-[#6D28D9] font-semibold hover:underline">
                ← Lihat semua jasa
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($products as $product)
                @php
                    $thumbUrl = \App\Helpers\R2Helper::url($product->thumbnail_url);
                    $minPrice = $product->tiers->min('price') ?? $product->price ?? 0;
                    $badgeLabel = $product->name;
                @endphp
                <a href="{{ route('customer.katalog.detail', $product->id) }}"
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:border-[#6D28D9] hover:shadow-md transition overflow-hidden group">

                    {{-- Thumbnail --}}
                    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-violet-100 to-violet-200">
                        @if ($thumbUrl)
                            <img src="{{ $thumbUrl }}" alt="{{ $product->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full hidden items-center justify-center absolute inset-0">
                                <span class="text-5xl font-extrabold text-[#6D28D9]/40">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </span>
                            </div>
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="text-5xl font-extrabold text-[#6D28D9]/40">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif

                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-[#D5FC55] text-neutral-900 text-[10px] font-bold uppercase tracking-wide z-10">
                            {{ $badgeLabel }}
                        </span>
                    </div>

                    {{-- Info --}}
                    <div class="p-4">
                        <h3 class="font-bold text-slate-900 leading-tight mb-2 line-clamp-2 min-h-[3rem]">
                            {{ $product->name }}
                        </h3>
                        <p class="text-sm text-slate-500 line-clamp-2 mb-3 min-h-[2.5rem]">
                            {{ $product->description ?? 'Jasa desain profesional dengan hasil berkualitas tinggi.' }}
                        </p>

                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Mulai dari</p>
                                <p class="font-extrabold text-[#6D28D9] text-lg">
                                    Rp {{ number_format($minPrice, 0, ',', '.') }}
                                </p>
                            </div>
                            <span class="px-4 py-1.5 rounded-full bg-[#6D28D9] text-white text-xs font-bold group-hover:bg-[#5B21B6] transition">
                                Detail
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-10">
            {{ $products->links('vendor.pagination.artiv') }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush