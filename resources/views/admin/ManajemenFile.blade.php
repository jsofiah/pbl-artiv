@extends('layouts.admin')

@section('title', 'Manajemen File')

@section('content')
<div class="space-y-6" x-data="{ 
    deleteModalOpen: false, 
    deleteUrl: '',
    previewModalOpen: false,
    previewUrl: '',
    previewName: '',
    previewLoading: false,

    async openPreview(endpoint, fileName) {
        this.previewName = fileName;
        this.previewUrl = '';
        this.previewLoading = true;
        this.previewModalOpen = true;

        try {
            const response = await fetch(endpoint, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (response.ok && data.url) {
                this.previewUrl = data.url;
            } else {
                alert(data.error || 'Gagal mengambil preview file.');
                this.previewModalOpen = false;
            }
        } catch (e) {
            alert('Terjadi kesalahan saat memuat preview file.');
            this.previewModalOpen = false;
        } finally {
            this.previewLoading = false;
        }
    }
}">

    {{-- Judul Halaman --}}
    {{-- <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen File</h1>
            <p class="text-sm text-gray-500 mt-1">Monitoring dan kelola berkas hasil desain serta referensi dari customer.</p>
        </div>
    </div> --}}

    {{-- Filter, Search & Tab Navigasi --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm space-y-4">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            {{-- Tab Navigasi Tema Ungu ARTIV --}}
            <div class="flex items-center gap-2 p-1 bg-purple-50/60 rounded-xl w-fit border border-purple-100/50">
                <a href="{{ route('admin.manajemen-file.index', ['tab' => 'deliverables', 'q' => $search, 'sort' => $sort]) }}"
                   class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-all {{ $tab === 'deliverables' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:text-purple-600 hover:bg-purple-100/50' }}">
                    Hasil Design
                </a>
                <a href="{{ route('admin.manajemen-file.index', ['tab' => 'references', 'q' => $search, 'sort' => $sort]) }}"
                   class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-all {{ $tab === 'references' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:text-purple-600 hover:bg-purple-100/50' }}">
                    References
                </a>
            </div>

            {{-- Form Search & Sort --}}
            <form action="{{ route('admin.manajemen-file.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">

                {{-- Input Cari Ringkas --}}
                <div class="relative min-w-[180px] sm:min-w-[200px]">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari file / Order ID..."
                           class="w-full pl-8 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 focus:bg-white focus:border-purple-500 focus:ring-purple-500 transition placeholder:text-gray-400">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                {{-- Select Dropdown Sort Ringkas --}}
                <select name="sort" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 py-1.5 pl-2.5 pr-7 focus:bg-white focus:border-purple-500 focus:ring-purple-500">
                    <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Urutkan: Diunggah Terbaru</option>
                    <option value="terlama" {{ $sort === 'terlama' ? 'selected' : '' }}>Urutkan: Diunggah Terlama</option>
                </select>
            </form>
        </div>

        {{-- Tabel Berkas dengan Lebar Kolom Proposional --}}
        <div class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="w-full text-left border-collapse table-fixed">
                <thead>
                    <tr class="bg-gray-50/80 text-[11px] font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100">
                        <th class="py-3 px-4 w-12 text-center">NO</th>
                        <th class="py-3 px-4 w-44">ORDER ID</th>
                        <th class="py-3 px-4">NAMA FILE</th>
                        <th class="py-3 px-4 w-40">STATUS ORDER</th>
                        <th class="py-3 px-4 w-40">DIUNGGAH</th>
                        <th class="py-3 px-4 w-44 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs sm:text-sm text-gray-700">
                    @forelse ($files as $index => $item)
                        @php
                            if ($tab === 'references') {
                                $order = $item->order;
                                $uploader = $order?->customer?->full_name ?? 'Customer';
                            } else {
                                $order = $item->message?->deliverable?->order ?? $item->message?->conversation?->order;
                                $uploader = $order?->designer?->full_name ?? 'Designer';
                            }

                            $fileName = $item->file_name ?? 'File Tanpa Nama';
                            $fileSize = isset($item->file_size) && $item->file_size > 0 
                                ? number_format($item->file_size / 1048576, 1) . ' MB' 
                                : 'N/A';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3.5 px-4 font-medium text-gray-400 text-center">
                                {{ $files->firstItem() + $index }}
                            </td>
                            
                            {{-- Order ID --}}
                            <td class="py-3.5 px-4 font-bold text-gray-900 truncate">
                                <a href="{{ route('admin.monitoring', ['q' => $order?->order_code]) }}" class="hover:text-purple-600 hover:underline">
                                    #{{ $order?->order_code ?? '-' }}
                                </a>
                            </td>

                            {{-- Nama File & Subtitle Uploader/Size --}}
                            <td class="py-3.5 px-4 min-w-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'FILE') }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-gray-800 truncate" title="{{ $fileName }}">
                                            {{ $fileName }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 truncate">
                                            Oleh: {{ $uploader }} • {{ $fileSize }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Status Order --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    {{ \App\Models\Order::STATUS_LABELS[$order?->status] ?? $order?->status ?? 'Draft' }}
                                </span>
                            </td>

                            {{-- Tanggal Upload --}}
                            <td class="py-3.5 px-4 text-gray-500 text-xs whitespace-nowrap">
                                {{ $item->created_at ? $item->created_at->translatedFormat('d/m/Y H:i') . ' WIB' : '-' }}
                            </td>

                            {{-- Tombol Aksi --}}
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Lihat In-Page Modal --}}
                                    <button type="button" 
                                            @click="openPreview('{{ route('admin.manajemen-file.preview', ['type' => $tab, 'id' => $item->id]) }}', '{{ addslashes($fileName) }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-purple-50 hover:text-purple-600 text-gray-700 font-semibold text-xs rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Lihat
                                    </button>

                                    {{-- Hapus --}}
                                    <button type="button" 
                                            @click="deleteModalOpen = true; deleteUrl = '{{ route('admin.manajemen-file.destroy', ['type' => $tab, 'id' => $item->id]) }}'"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">
                                Tidak ada berkas yang ditemukan pada kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pt-2">
            {{ $files->links() }}
        </div>
    </div>

    {{-- MODAL LIGHTBOX PREVIEW --}}
    <template x-teleport="body">
        <div x-show="previewModalOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
             style="display: none;">
            
            <div @click.away="previewModalOpen = false" class="bg-white rounded-3xl max-w-3xl w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                
                {{-- Header Modal Preview --}}
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3 min-w-0 pr-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-slate-900 truncate" x-text="previewName"></h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pratinjau berkas terenkripsi R2</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Tombol Unduh Direct --}}
                        <template x-if="previewUrl">
                            <a :href="previewUrl" download target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-semibold text-xs rounded-xl transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Unduh
                            </a>
                        </template>

                        {{-- Tombol Tutup --}}
                        <button @click="previewModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Container Content Preview --}}
                <div class="min-h-[300px] max-h-[72vh] flex items-center justify-center overflow-auto rounded-2xl bg-slate-50 border border-slate-100 p-3">
                    <template x-if="previewLoading">
                        <div class="flex items-center gap-2 text-xs text-slate-400 font-medium py-12">
                            <svg class="w-5 h-5 animate-spin text-purple-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memuat pratinjau berkas...
                        </div>
                    </template>

                    <template x-if="!previewLoading && previewUrl">
                        <div class="w-full h-full flex justify-center">
                            {{-- Format Gambar --}}
                            <template x-if="previewName.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)">
                                <img :src="previewUrl" :alt="previewName" class="max-h-[65vh] w-auto rounded-xl object-contain shadow-sm border border-slate-200/60">
                            </template>

                            {{-- Format PDF --}}
                            <template x-if="previewName.match(/\.(pdf)$/i)">
                                <iframe :src="previewUrl" class="w-full h-[65vh] rounded-xl border-0 shadow-sm"></iframe>
                            </template>

                            {{-- File Mentahan (ZIP, PSD, AI, DOC) --}}
                            <template x-if="!previewName.match(/\.(jpg|jpeg|png|gif|webp|svg|pdf)$/i)">
                                <div class="text-center py-10 space-y-4">
                                    <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center mx-auto font-bold text-lg"
                                         x-text="(previewName.split('.').pop() || 'FILE').toUpperCase()">
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800">File Mentahan / Dokumen</h4>
                                        <p class="text-xs text-slate-500 mt-1">Format berkas ini tidak dapat dipratinjau langsung di browser.</p>
                                    </div>
                                    <a :href="previewUrl" download target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition shadow-md shadow-purple-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Unduh File Sekarang
                                    </a>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <template x-teleport="body">
        <div x-show="deleteModalOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            
            <div @click.away="deleteModalOpen = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-xl space-y-4 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Hapus File Ini?</h3>
                    <p class="text-xs text-slate-500 mt-1">Berkas akan dihapus secara permanen dari Cloudflare R2 dan database.</p>
                </div>
                
                <form :action="deleteUrl" method="POST" class="flex items-center gap-2 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false" class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
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