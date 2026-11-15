@extends('layouts.admin')
@section('title', 'Manajemen Galeri')

@section('content')

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900">Manajemen Galeri</h1>
        <p class="text-slate-500 mt-1.5">Pilih hasil desain terbaik dari order selesai untuk tampil di galeri publik.</p>
    </div>
    <a href="{{ route('admin.galeri.kandidat') }}"
       class="inline-flex items-center justify-center gap-2 bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-sm font-semibold px-5 py-2.5 rounded-full transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        Tambah dari Order Selesai
    </a>
</div>

@if (session('success'))
    <x-shared.alert type="success" :message="session('success')" :auto-hide="true" :duration="3000" />
@endif

@if (session('error'))
    <x-shared.alert type="error" :message="session('error')" :auto-hide="false" />
@endif

@if ($items->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-violet-50 text-[#6D28D9] flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="16" rx="2"/>
                <circle cx="8.5" cy="9.5" r="1.5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 16l-5-5-8 8"/>
            </svg>
        </div>
        <p class="text-slate-500 mb-1">Belum ada item galeri.</p>
        <p class="text-sm text-slate-400">Klik tombol <b>Tambah dari Order Selesai</b> di atas untuk memilih hasil desain.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach ($items as $item)
            <article class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col hover:border-[#6D28D9]/40 hover:shadow-md transition overflow-hidden">

                <div class="relative h-44 bg-slate-100 overflow-hidden group">
                    <img src="{{ $item->display_url }}" alt="{{ $item->title }}"
                        class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">

                    <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-semibold
                                {{ $item->is_active ? 'bg-[#D5FC55] text-[#1A1A1A]' : 'bg-slate-300 text-slate-500' }}">
                        {{ $item->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                    </span>
                </div>

                <div class="p-4 flex flex-col flex-1">
                    <h3 class="text-base font-bold {{ $item->is_active ? 'text-slate-900' : 'text-slate-500' }} line-clamp-1">
                        {{ $item->title }}
                    </h3>

                    @if ($item->order?->order_code)
                        <p class="text-xs text-slate-400 mt-1">
                            Order <a href="{{ route('admin.monitoring', ['q' => $item->order->order_code]) }}"
                                     class="font-semibold text-[#6D28D9] hover:underline">
                                #{{ $item->order->order_code }}
                            </a>
                        </p>
                    @else
                        <p class="text-xs text-slate-400 mt-1">Ditambahkan manual</p>
                    @endif

                    @if ($item->product)
                        <p class="text-xs text-slate-500 mt-2 line-clamp-1">
                            {{ $item->product->name }}
                        </p>
                    @endif

                    <div class="border-t border-slate-200 mt-4 pt-3 flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.galeri.toggle', $item) }}" class="flex-1">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-full border transition
                                           {{ $item->is_active
                                                ? 'border-slate-200 bg-white text-slate-700 hover:border-amber-300 hover:text-amber-600'
                                                : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-300 hover:text-emerald-600' }}">
                                @if ($item->is_active)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.36 6.64a9 9 0 11-12.73 0M12 2v10"/>
                                    </svg>
                                    Nonaktifkan
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Aktifkan
                                @endif
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.galeri.destroy', $item) }}"
                              onsubmit="return confirm('Hapus item galeri ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-full border border-slate-200 bg-white text-slate-700 hover:border-rose-300 hover:text-rose-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-8 pt-5 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
        <p class="text-sm text-slate-500">
            Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} item galeri
        </p>

        @if ($items->hasPages())
            <div class="flex items-center gap-2">
                <a href="{{ $items->previousPageUrl() ?? '#' }}"
                   class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $items->onFirstPage() ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }} transition">
                    &lt; Sebelumnya
                </a>

                @for ($i = 1; $i <= $items->lastPage(); $i++)
                    <a href="{{ $items->url($i) }}"
                       class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-semibold transition
                               {{ $i === $items->currentPage()
                                   ? 'bg-[#6D28D9] text-white shadow-sm'
                                   : 'border border-slate-200 bg-white text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                        {{ $i }}
                    </a>
                @endfor

                <a href="{{ $items->nextPageUrl() ?? '#' }}"
                   class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $items->hasMorePages() ? 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' : 'text-slate-300 pointer-events-none' }} transition">
                    Selanjutnya &gt;
                </a>
            </div>
        @endif
    </div>
@endif

@endsection