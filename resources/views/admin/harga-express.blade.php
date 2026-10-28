@extends('layouts.admin')

@section('title', 'Manajemen Harga Express')

@section('content')
<div class="space-y-5" x-data="{ 
    currentTab: 'semua',
    createModalOpen: false,
    deleteModalOpen: false,
    deleteUrl: ''
}">

    {{-- Flash Message Success / Error --}}
    @if(session('success'))
        <div class="p-4 text-xs sm:text-sm text-emerald-800 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 font-bold">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 text-xs sm:text-sm text-rose-800 bg-rose-50 rounded-2xl border border-rose-200 flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-500 font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 text-xs sm:text-sm text-rose-800 bg-rose-50 rounded-2xl border border-rose-200 space-y-1">
            <p class="font-bold">Gagal Menyimpan Data:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top Bar Compact: Tab Filter (No Reload), Search & Tombol Tambah --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            
            {{-- Tab Filter Status (Client-Side via Alpine.js) --}}
            <div class="flex items-center gap-1.5 p-1 bg-gray-100/80 rounded-xl w-fit">
                <button type="button"
                        @click="currentTab = 'semua'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer"
                        :class="currentTab === 'semua' ? 'bg-[#745BB8] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                    Semua ({{ $stats['semua'] }})
                </button>
                <button type="button"
                        @click="currentTab = 'aktif'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer"
                        :class="currentTab === 'aktif' ? 'bg-[#745BB8] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                    Aktif ({{ $stats['aktif'] }})
                </button>
                <button type="button"
                        @click="currentTab = 'nonaktif'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer"
                        :class="currentTab === 'nonaktif' ? 'bg-[#745BB8] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                    Dinonaktifkan ({{ $stats['nonaktif'] }})
                </button>
            </div>

            {{-- Kanan: Form Search & Tombol Tambah --}}
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('admin.harga-express.index') }}" method="GET" class="relative min-w-[180px] flex-1 sm:flex-initial">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama paket..."
                           class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:bg-white focus:border-[#745BB8] focus:ring-[#745BB8] transition placeholder:text-gray-400">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </form>

                <button type="button" 
                        @click="createModalOpen = true"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#745BB8] hover:bg-[#5C469C] text-white text-xs font-semibold rounded-xl transition shadow-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Harga Express
                </button>
            </div>

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
                  class="bg-white rounded-2xl border border-gray-200/80 hover:border-[#745BB8]/50 p-5 shadow-sm transition flex flex-col justify-between">
                @csrf
                @method('PUT')

                <input type="hidden" name="is_active" :value="isActive ? '1' : '0'">

                <div class="space-y-4 flex-1 flex flex-col justify-between">
                    
                    {{-- Header Card --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full transition-colors duration-200 flex items-center justify-center shrink-0"
                                 :class="isActive ? 'bg-[#745BB8] text-white' : 'bg-gray-100 text-gray-400'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>

                            <h3 class="text-base font-bold text-gray-900 truncate" title="{{ $item->name }}">
                                {{ $item->name }}
                            </h3>
                        </div>

                        {{-- Mode Normal: Badge Statis --}}
                        <span x-show="!editing" 
                              class="px-2.5 py-1 rounded-full text-[10px] font-bold shrink-0 transition-colors duration-200"
                              :class="isActive ? 'bg-[#745BB8] text-white' : 'bg-gray-100 text-gray-400'"
                              x-text="isActive ? 'Aktif' : 'Nonaktif'">
                        </span>

                        {{-- Mode Edit: Badge Toggle Status --}}
                        <button x-show="editing" 
                                x-cloak 
                                type="button" 
                                @click="isActive = !isActive" 
                                class="px-2.5 py-1 rounded-full text-[10px] font-bold shrink-0 transition-all cursor-pointer hover:scale-105 active:scale-95"
                                :class="isActive ? 'bg-[#745BB8] text-white' : 'bg-gray-100 text-gray-400'"
                                title="Klik untuk merubah status">
                            <span x-text="isActive ? 'Aktif' : 'Nonaktif'"></span>
                        </button>
                    </div>

                    {{-- Box Biaya Tambahan --}}
                    <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-100/80 space-y-1">
                        <span class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">Biaya Tambahan</span>
                        
                        {{-- Mode Normal --}}
                        <div x-show="!editing" class="text-xl font-extrabold text-gray-900">
                            Rp {{ number_format($item->fee, 0, ',', '.') }}
                        </div>

                        {{-- Mode Edit: Langsung Ketik Angka --}}
                        <div x-show="editing" x-cloak class="flex items-center gap-1">
                            <span class="text-xl font-extrabold text-gray-900 select-none">Rp</span>
                            <input type="number" 
                                   step="1"
                                   name="fee" 
                                   value="{{ (int) $item->fee }}" 
                                   min="0" 
                                   required
                                   onkeydown="if(['e','E','+','-','.',','].includes(event.key)) event.preventDefault();"
                                   class="w-full bg-transparent border-0 p-0 text-xl font-extrabold text-gray-900 focus:ring-0 focus:outline-none border-b border-[#745BB8] border-dashed [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="pt-2">
                        <div x-show="!editing" class="flex items-center gap-2 w-full">
                            <button type="button" 
                                    @click="editing = true"
                                    class="w-1/2 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold text-xs rounded-xl border border-gray-200/80 transition flex items-center justify-center gap-1.5">
                                Edit
                            </button>

                            <button type="button" 
                                    @click="deleteModalOpen = true; deleteUrl = '{{ route('admin.harga-express.destroy', $item->id) }}'"
                                    class="w-1/2 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                                Hapus
                            </button>
                        </div>

                        <div x-show="editing" x-cloak class="flex items-center gap-2 w-full">
                            <button type="button" 
                                    @click="editing = false; isActive = {{ $item->is_active ? 'true' : 'false' }}"
                                    class="w-1/2 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="w-1/2 py-2 bg-[#745BB8] hover:bg-[#5C469C] text-white font-bold text-xs rounded-xl shadow-sm transition">
                                Simpan
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        @empty
            <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-100">
                Tidak ada data harga express ditemukan.
            </div>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $expressFees->links() }}
    </div>

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
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Tambah Harga Express</h3>
                    <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.harga-express.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Durasi Pengerjaan (Hari)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" 
                                   step="1"
                                   name="name" 
                                   required 
                                   min="1" 
                                   placeholder="1"
                                   onkeydown="if(['e','E','+','-','.',','].includes(event.key)) event.preventDefault();"
                                   class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:bg-white focus:border-[#745BB8] focus:ring-[#745BB8] transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <span class="text-xs font-bold text-gray-700 shrink-0">Hari</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Biaya Tambahan (Rp)</label>
                        <input type="number" 
                               step="1"
                               name="fee" 
                               required 
                               min="0" 
                               placeholder="50000"
                               onkeydown="if(['e','E','+','-','.',','].includes(event.key)) event.preventDefault();"
                               class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:bg-white focus:border-[#745BB8] focus:ring-[#745BB8] transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                               class="rounded text-[#745BB8] focus:ring-[#745BB8] border-gray-300">
                        <label for="create_is_active" class="text-xs font-semibold text-gray-700 cursor-pointer">Aktifkan opsi ini langsung</label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="createModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-[#745BB8] hover:bg-[#5C469C] text-white text-xs font-bold rounded-xl transition shadow-sm">
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
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            
            <div @click.away="deleteModalOpen = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-xl space-y-4 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Hapus Harga Express?</h3>
                    <p class="text-xs text-gray-500 mt-1">Data akan dihapus permanen dari sistem jika belum terikat pesanan.</p>
                </div>
                
                <form :action="deleteUrl" method="POST" class="flex items-center gap-2 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection