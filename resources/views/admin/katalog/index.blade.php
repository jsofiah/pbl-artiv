@extends('layouts.admin')

@section('title', 'Manajemen Katalog')

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
    <div>
        <h1 class="text-4xl font-extrabold text-slate-900">Manajemen Katalog</h1>
        <p class="text-slate-500 mt-2">Kelola layanan jasa desain, varian tipe paket, dan harganya.</p>
    </div>
    <a href="{{ route('admin.katalog.create') }}" class="inline-flex items-center gap-2 bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-sm font-semibold px-5 py-3 rounded-xl transition shadow-sm">        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Tambah Katalog
    </a>
</div>

@if (session('status'))
    <x-shared.alert type="success" :message="session('status')" :auto-hide="true" :duration="3000" />
@endif

@if ($errors->any())
    <x-shared.alert type="error" :auto-hide="false">
        <p class="font-semibold mb-1">Gagal menyimpan:</p>
        <ul class="text-xs list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-shared.alert>
@endif

@php
    $tab = [
        'semua'    => 'Semua (' . $jumlah['semua'] . ')',
        'aktif'    => 'Aktif (' . $jumlah['aktif'] . ')',
        'nonaktif' => 'Dinonaktifkan (' . $jumlah['nonaktif'] . ')',
    ];
@endphp
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-2 flex-wrap">
        @php
            $tabs = [
                'semua'    => ['label' => 'Semua',          'count' => $jumlah['semua']],
                'aktif'    => ['label' => 'Aktif',          'count' => $jumlah['aktif']],
                'nonaktif' => ['label' => 'Dinonaktifkan',  'count' => $jumlah['nonaktif']],
            ];
        @endphp

        @foreach ($tabs as $key => $tab)
            <a href="{{ route('admin.katalog.index', array_merge(request()->except('status', 'page'), ['status' => $key, 'urut' => $urut])) }}"
                class="px-4 py-2 rounded-full text-sm font-semibold border transition
                        {{ $status === $key
                            ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm'
                            : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                {{ $tab['label'] }}
                <span class="ml-1.5 inline-block rounded-full px-1.5 py-0.5 text-[10px] font-bold
                            {{ $status === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <div class="flex items-center gap-2 flex-wrap ml-auto" style="margin-left: 0px">

        <form method="GET" action="{{ route('admin.katalog.index') }}" class="flex items-center">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="urut" value="{{ $urut }}">
            <div class="bg-white rounded-full shadow-sm border border-slate-200 p-1.5 flex items-center gap-2 w-[380px]">
                <div class="pl-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $kataKunci }}"
                       placeholder="Cari katalog..."
                       class="flex-1 bg-transparent border-none focus:outline-none text-slate-700 placeholder:text-slate-400 px-1 py-1.5 text-sm">
                <button type="submit"
                        class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-3 py-1.5 rounded-full transition text-xs whitespace-nowrap">
                    Cari
                </button>
            </div>
        </form>

        <div x-data="{ open: false }" class="relative">
            <button type="button" @click="open = !open"
                    class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-4 pr-3 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[190px] justify-between">
                <span>
                    @php
                        $sortLabels = [
                            'terbaru' => 'Terbaru',
                            'terlama' => 'Terlama',
                            'nama'    => 'Nama A–Z',
                        ];
                    @endphp
                    Urutkan: {{ $sortLabels[$urut] ?? 'Terbaru' }}
                </span>
                <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" @click.outside="open = false"
                 class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 right-0"
                 style="display: none;">
                @foreach (['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'nama' => 'Nama A–Z'] as $value => $label)
                    <a href="{{ route('admin.katalog.index', array_merge(request()->except('urut', 'page'), ['status' => $status, 'urut' => $value])) }}"
                       class="block w-full text-left px-5 py-2 text-sm transition
                              {{ $urut === $value ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($kataKunci !== '' || $status !== 'semua' || $urut !== 'terbaru')
            <a href="{{ route('admin.katalog.index') }}" title="Reset filter"
               class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:text-[#6D28D9] hover:border-[#6D28D9] transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>
        @endif

    </div>
</div>

@if (request()->hasAny(['q', 'status', 'urut']))
    <div class="mb-4 text-sm text-slate-500">
        Menampilkan <strong>{{ $katalog->total() }}</strong> katalog
        @if (request('q')) untuk pencarian "<strong>{{ request('q') }}</strong>"@endif
        .
        <a href="{{ route('admin.katalog.index') }}"
           class="text-[#6D28D9] font-semibold hover:underline ml-2">
            Reset filter
        </a>
    </div>
@endif

@if ($katalog->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 py-20 text-center text-slate-400">
        Tidak ada katalog pada filter ini.
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($katalog as $k)
            @php
                $thumbUrl = $k->thumbnail_url ? \App\Helpers\R2Helper::url($k->thumbnail_url) : null;
                $hargaTerendah = $k->tiers->isNotEmpty()
                    ? $k->tiers->min('price')
                    : $k->price;
            @endphp
            <article class="bg-white rounded-3xl border border-slate-200 p-5 flex flex-col hover:border-[#6D28D9]/40 hover:shadow-md transition">

                <div class="relative h-44 rounded-2xl border border-slate-200 bg-slate-100 flex flex-col items-center justify-center gap-1.5 text-slate-400 overflow-hidden">
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="{{ $k->name }}" class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 16l-5-5-8 8"/></svg>
                        <span class="text-[11px] font-semibold tracking-widest">PREVIEW</span>
                    @endif

                    <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-semibold
                                {{ $k->is_active ? 'bg-[#D5FC55] text-[#1A1A1A]' : 'bg-slate-300 text-slate-500' }}">
                    {{ $k->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                    </span>
                </div>

                <h3 class="text-xl font-bold mt-4 {{ $k->is_active ? 'text-slate-900' : 'text-slate-500' }}">
                    {{ $k->name }}
                </h3>
                <p class="text-sm text-slate-500 leading-relaxed mt-2 mb-4 line-clamp-3">
                    {{ $k->description }}
                </p>

                <div class="border-t border-slate-200 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold tracking-wider text-slate-500">
                            @if ($k->tiers->isNotEmpty())
                                {{ $k->tiers->count() }} VARIAN TIPE PAKET
                            @else
                                HARGA
                            @endif
                        </p>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded
                                    {{ $k->is_active ? 'bg-lime-100 text-lime-700' : 'bg-slate-200 text-slate-500' }}">
                            {{ $k->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>

                    @if ($k->tiers->isEmpty())
                        <div class="bg-lime-50 border border-lime-200 rounded-xl px-4 py-3 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-700">Harga</span>
                            <span class="text-lg font-extrabold text-[#6D28D9]">
                                @if ($k->price)
                                    Rp {{ number_format($k->price, 0, ',', '.') }}
                                @else
                                    <span class="text-sm font-normal text-slate-400 italic">Belum ditentukan</span>
                                @endif
                            </span>
                        </div>
                    @else
                        <ul class="bg-slate-50 rounded-xl px-4 py-2 divide-y divide-slate-100">
                            @foreach ($k->tiers as $i => $v)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <span class="flex items-center gap-2.5 {{ $k->is_active ? 'text-slate-600' : 'text-slate-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $k->is_active && $i === 0 ? 'bg-[#6D28D9]' : 'bg-slate-400' }}"></span>
                                        {{ $v->name }}
                                    </span>
                                    <span class="font-semibold {{ $k->is_active ? 'text-slate-900' : 'text-slate-500' }}">
                                        Rp {{ number_format($v->price, 0, ',', '.') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="border-t border-slate-200 mt-5 pt-4 flex items-center gap-2">
                    <a href="{{ route('admin.katalog.edit', $k->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9] transition">                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z"/></svg>
                        Edit
                    </a>

                    <form method="POST" action="{{ route('admin.katalog.destroy', $k->id) }}"
                            onsubmit="return confirm('Hapus katalog {{ e($k->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:border-rose-300 hover:text-rose-600 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                            Hapus
                        </button>
                    </form>

                    <a href="{{ route('admin.katalog.tiers.index', $k->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#D5FC55] text-[#1A1A1A] hover:bg-[#c5ec45] transition">                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        Tipe
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-8 pt-5 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
        <p class="text-sm text-slate-500">
            Menampilkan {{ $katalog->firstItem() }} - {{ $katalog->lastItem() }} dari {{ $katalog->total() }} Katalog
        </p>

        @if ($katalog->hasPages())
            <div class="flex items-center gap-2">
                <a href="{{ $katalog->previousPageUrl() ?? '#' }}"
                    class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $katalog->onFirstPage() ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }} transition">
                    &lt; Sebelumnya
                </a>
                @for ($i = 1; $i <= $katalog->lastPage(); $i++)
                    <a href="{{ $katalog->url($i) }}"
                        class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-semibold transition
                                {{ $i === $katalog->currentPage()
                                    ? 'bg-[#6D28D9] text-white shadow-sm'
                                    : 'border border-slate-200 bg-white text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                        {{ $i }}
                    </a>
                @endfor
                <a href="{{ $katalog->nextPageUrl() ?? '#' }}"
                    class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $katalog->hasMorePages() ? 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' : 'text-slate-300 pointer-events-none' }} transition">
                    Selanjutnya &gt;
                </a>
            </div>
        @endif
    </div>
@endif
@endsection