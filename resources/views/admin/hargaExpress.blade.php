@extends('layouts.admin')

@section('title', 'Manajemen Harga Express')

@section('content')
<div class="space-y-6" x-data="{ 
    currentTab: 'semua',
    createModalOpen: false,
    deleteModalOpen: false,
    deleteUrl: ''
}">

    {{-- Header Halaman --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Manajemen Harga Express</h1>
            <p class="text-slate-500 mt-1.5">Kelola opsi tambahan biaya pengerjaan kilat (express) untuk customer.</p>
        </div>
        <button type="button" 
                @click="createModalOpen = true"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-full bg-[#6D28D9] hover:bg-[#5B21B6] transition shadow-sm shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            Tambah Harga Express
        </button>
    </div>

    {{-- Flash Message Success / Error --}}
    @if(session('success'))
        <div class="px-4 py-3 mb-4 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 font-bold">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="px-4 py-3 mb-4 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-xl flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-500 font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="px-4 py-3 mb-4 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-xl space-y-1">
            <p class="font-bold">Gagal Menyimpan Data:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Search + tab + sort --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        {{-- Tab Filter (Pill Style) --}}
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="currentTab = 'semua'"
                    class="px-4 py-2 rounded-full text-sm font-semibold border transition
                           {{-- Menggunakan x-bind:class agar reaktif --}}
                           "
                    :class="currentTab === 'semua' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'">
                Semua
                <span class="ml-1.5 inline-block rounded-full px-1.5 py-0.5 text-[11px] font-bold" :class="currentTab === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                    {{ $stats['semua'] }}
                </span>
            </button>
            <button type="button" @click="currentTab = 'aktif'"
                    class="px-4 py-2 rounded-full text-sm font-semibold border transition"
                    :class="currentTab === 'aktif' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'">
                Aktif
                <span class="ml-1.5 inline-block rounded-full px-1.5 py-0.5 text-[11px] font-bold" :class="currentTab === 'aktif' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                    {{ $stats['aktif'] }}
                </span>
            </button>
            <button type="button" @click="currentTab = 'nonaktif'"
                    class="px-4 py-2 rounded-full text-sm font-semibold border transition"
                    :class="currentTab === 'nonaktif' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'">
                Dinonaktifkan
                <span class="ml-1.5 inline-block rounded-full px-1.5 py-0.5 text-[11px] font-bold" :class="currentTab === 'nonaktif' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                    {{ $stats['nonaktif'] }}
                </span>
            </button>
        </div>

        {{-- Search --}}
        <div class="flex items-center gap-2 flex-wrap ml-auto">
            <form action="{{ route('admin.harga-express.index') }}" method="GET" class="flex items-center">
                <div class="bg-white rounded-full shadow-sm border border-slate-200 p-1.5 flex items-center gap-2 w-[320px]">
                    <div class="pl-3 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama paket..."
                           class="flex-1 bg-transparent border-none focus:outline-none text-slate-700 placeholder:text-slate-400 px-1 py-1.5 text-sm">
                    <button type="submit" class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-3 py-1.5 rounded-full transition text-xs whitespace-nowrap">
                        Cari
                    </button>
                </div>
            </form>
            
            @if($search !== '')
                <a href="{{ route('admin.harga-express.index') }}" title="Reset filter"
                   class="flex h-[42px] w-[42px] items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:text-[#6D28D9] hover:border-[#6D28D9] transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Grid Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($expressFees as $item)
            <form action="{{ route('admin.harga-express.update', $item->id) }}" method="POST"
                  x-data="{ 
                      editing: false, 
                      isActive: {{ $item->is_active ? 'true' : 'false' }}
                  }" 
                  x-show="currentTab === 'semua' || (currentTab === 'aktif' && isActive) || (currentTab === 'nonaktif' && !isActive)"
                  x-transition:enter="transition ease-out duration-150"
                  x-transition:enter-start="opacity-0 scale-95"
                  x-transition:enter-end="opacity-100 scale-100"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:border-[#6D28D9]/40 p-5 transition flex flex-col justify-between">
                @csrf
                @method('PUT')

                <input type="hidden" name="is_active" :value="isActive ? '1' : '0'">

                <div class="space-y-4 flex-1 flex flex-col justify-between">
                    
                    {{-- Header Card --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full transition-colors duration-200 flex items-center justify-center shrink-0"
                                 :class="isActive ? 'bg-[#6D28D9]/10 text-[#6D28D9]' : 'bg-slate-100 text-slate-400'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 truncate" title="{{ $item->name }}">
                                {{ $item->name }}
                            </h3>
                        </div>

                        <span x-show="!editing" 
                              class="px-2.5 py-1 rounded-full text-sm font-semibold shrink-0 transition-colors duration-200"
                              :class="isActive ? 'bg-[#6D28D9]/10 text-[#5B21B6]' : 'bg-slate-100 text-slate-500'"
                              x-text="isActive ? 'Aktif' : 'Nonaktif'">
                        </span>

                        {{-- Mode Edit: Badge Toggle Status --}}
                        <button x-show="editing" 
                                x-cloak 
                                type="button" 
                                @click="isActive = !isActive" 
                                class="px-2.5 py-1 rounded-full text-sm font-semibold shrink-0 transition-all cursor-pointer hover:scale-105 active:scale-95"
                                :class="isActive ? 'bg-[#6D28D9]/10 text-[#5B21B6]' : 'bg-slate-100 text-slate-500'"
                                title="Klik untuk merubah status">
                            <span x-text="isActive ? 'Aktif' : 'Nonaktif'"></span>
                        </button>
                    </div>

                    {{-- Box Biaya Tambahan --}}
                    <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-100/80 space-y-1">
                        <span class="text-sm font-bold tracking-wider text-slate-400 uppercase">Biaya Tambahan</span>
                        
                        {{-- Mode Normal --}}
                        <div x-show="!editing" class="text-2xl font-extrabold text-slate-900">
                            Rp {{ number_format($item->fee, 0, ',', '.') }}
                        </div>

                        {{-- Mode Edit: Langsung Ketik Angka --}}
                        <div x-show="editing" x-cloak class="flex items-center gap-1">
                            <span class="text-2xl font-extrabold text-slate-900 select-none">Rp</span>
                            <input type="number" 
                                   step="1"
                                   name="fee" 
                                   value="{{ (int) $item->fee }}" 
                                   min="0" 
                                   required
                                   onkeydown="if(['e','E','+','-','.',','].includes(event.key)) event.preventDefault();"
                                   class="w-full bg-transparent border-0 p-0 text-2xl font-extrabold text-slate-900 focus:ring-0 focus:outline-none border-b border-[#6D28D9] border-dashed [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="pt-2">
                        <div x-show="!editing" class="flex items-center gap-2 w-full">
                            <button type="button" 
                                    @click="editing = true"
                                    class="w-1/2 py-2 bg-white border border-slate-200 hover:border-[#6D28D9] hover:text-[#6D28D9] text-slate-700 font-semibold text-sm rounded-full transition flex items-center justify-center gap-1.5 shadow-sm">
                                Edit
                            </button>

                            <button type="button" 
                                    @click="deleteModalOpen = true; deleteUrl = '{{ route('admin.harga-express.destroy', $item->id) }}'"
                                    class="w-1/2 py-2 bg-white border border-slate-200 hover:border-rose-300 hover:text-rose-600 text-slate-700 font-semibold text-sm rounded-full transition flex items-center justify-center gap-1.5 shadow-sm">
                                Hapus
                            </button>
                        </div>

                        <div x-show="editing" x-cloak class="flex items-center gap-2 w-full">
                            <button type="button" 
                                    @click="editing = false; isActive = {{ $item->is_active ? 'true' : 'false' }}"
                                    class="w-1/2 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-full transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="w-1/2 py-2 bg-[#6D28D9] hover:bg-[#5B21B6] text-white font-bold text-sm rounded-full shadow-sm transition">
                                Simpan
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        @empty
            <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-100">
                Tidak ada data harga express ditemukan.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($expressFees->hasPages())
        <div class="mt-8 pt-5 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
            <p class="text-sm text-slate-500">
                Menampilkan {{ $expressFees->firstItem() }} - {{ $expressFees->lastItem() }} dari {{ $expressFees->total() }} data
            </p>
            <div class="flex items-center gap-2">
                <a href="{{ $expressFees->previousPageUrl() ?? '#' }}"
                    class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $expressFees->onFirstPage() ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }} transition">
                    &lt; Sebelumnya
                </a>
                @for ($i = 1; $i <= $expressFees->lastPage(); $i++)
                    <a href="{{ $expressFees->url($i) }}"
                        class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-semibold transition
                                {{ $i === $expressFees->currentPage()
                                    ? 'bg-[#6D28D9] text-white shadow-sm'
                                    : 'border border-slate-200 bg-white text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                        {{ $i }}
                    </a>
                @endfor
                <a href="{{ $expressFees->nextPageUrl() ?? '#' }}"
                    class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $expressFees->hasMorePages() ? 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' : 'text-slate-300 pointer-events-none' }} transition">
                    Selanjutnya &gt;
                </a>
            </div>
        </div>
    @endif

    {{-- MODAL TAMBAH HARGA EXPRESS --}}
    <template x-teleport="body">
        <div x-show="createModalOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
             style="display: none;">
            
            <div @click.away="createModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900">Tambah Harga Express</h3>
                    <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.harga-express.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Durasi Pengerjaan (Hari)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" 
                                   step="1"
                                   name="name" 
                                   required 
                                   min="1" 
                                   placeholder="1"
                                   onkeydown="if(['e','E','+','-','.',','].includes(event.key)) event.preventDefault();"
                                   class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:border-[#6D28D9] focus:ring-[#6D28D9] transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <span class="text-sm font-semibold text-slate-700 shrink-0">Hari</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Biaya Tambahan (Rp)</label>
                        <input type="number" 
                               step="1"
                               name="fee" 
                               required 
                               min="0" 
                               placeholder="50000"
                               onkeydown="if(['e','E','+','-','.',','].includes(event.key)) event.preventDefault();"
                               class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:border-[#6D28D9] focus:ring-[#6D28D9] transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                               class="rounded text-[#6D28D9] focus:ring-[#6D28D9] border-slate-300">
                        <label for="create_is_active" class="text-sm font-semibold text-slate-700 cursor-pointer">Aktifkan opsi ini langsung</label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModalOpen = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-full transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-sm font-semibold rounded-full transition shadow-sm">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- MODAL HAPUS --}}
    <template x-teleport="body">
        <div x-show="deleteModalOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            
            <div @click.away="deleteModalOpen = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-xl space-y-4 text-center">
                <div class="w-14 h-14 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto text-2xl font-bold">!</div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Hapus Harga Express?</h3>
                    <p class="text-sm text-slate-500 mt-1">Data akan dihapus permanen dari sistem jika belum terikat pesanan.</p>
                </div>
                
                <form :action="deleteUrl" method="POST" class="flex items-center justify-center gap-2 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false" class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-full transition">
                        Batal
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-full transition">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection