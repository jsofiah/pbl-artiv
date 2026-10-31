@extends('layouts.admin')

@section('title', 'Manajemen File')

@section('content')
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="space-y-6" x-data="{
        tab: '{{ $tab }}',
        search: '{{ $search }}',
        sort: '{{ $sort }}',
        loading: false,
    
        deleteModalOpen: false,
        deleteUrl: '',
        previewModalOpen: false,
        previewUrl: '',
        downloadUrl: '',
        previewName: '',
        previewLoading: false,
    
        async switchTab(newTab) {
            if (this.tab === newTab || this.loading) return;
            this.tab = newTab;
            await this.fetchTableData();
        },
    
        async fetchTableData(pageUrl = null) {
            this.loading = true;
            let url;
    
            if (pageUrl) {
                url = new URL(pageUrl);
            } else {
                url = new URL('{{ route('admin.manajemen-file.index') }}', window.location.origin);
                url.searchParams.set('tab', this.tab);
                if (this.search) url.searchParams.set('q', this.search);
                if (this.sort) url.searchParams.set('sort', this.sort);
            }
    
            window.history.pushState({}, '', url);
    
            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (response.ok) {
                    const html = await response.text();
                    document.getElementById('table-container').innerHTML = html;
                }
            } catch (e) {
                console.error('Gagal memuat data:', e);
            } finally {
                this.loading = false;
            }
        },
    
        async openPreview(endpoint, downloadEndpoint, fileName) {
            this.previewName = fileName;
            this.previewUrl = '';
            this.downloadUrl = downloadEndpoint;
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
    }" x-init="document.addEventListener('click', (e) => {
        const link = e.target.closest('#table-container a');
        if (link) {
            e.preventDefault();
            fetchTableData(link.href);
        }
    });">

        {{-- Header Halaman --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900">Manajemen File</h1>
                <p class="text-slate-500 mt-1.5">Kelola hasil desain dan file referensi dari customer maupun desainer.</p>
            </div>
        </div>

        {{-- Filter, Search & Tab Navigasi --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            
            {{-- Tab Navigasi Async (Pill Style) --}}
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="switchTab('deliverables')"
                    :class="tab === 'deliverables' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'"
                    class="px-4 py-2 rounded-full text-sm font-semibold border transition">
                    Hasil Design
                </button>
                <button type="button" @click="switchTab('references')"
                    :class="tab === 'references' ? 'bg-[#6D28D9] border-[#6D28D9] text-white shadow-sm' : 'bg-white border-slate-300 text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]'"
                    class="px-4 py-2 rounded-full text-sm font-semibold border transition">
                    References
                </button>
            </div>

            {{-- Form Search & Sort --}}
            <div class="flex items-center gap-2 flex-wrap ml-auto">
                <form @submit.prevent="fetchTableData()" class="flex items-center gap-2">
                    
                    {{-- Input Cari --}}
                    <div class="bg-white rounded-full shadow-sm border border-slate-200 p-1.5 flex items-center gap-2 w-[320px]">
                        <div class="pl-3 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input type="text" x-model="search" @keyup.enter="fetchTableData()"
                            placeholder="Cari file atau Order ID..."
                            class="flex-1 bg-transparent border-none focus:outline-none text-slate-700 placeholder:text-slate-400 px-1 py-1.5 text-sm">
                        <button type="button" @click="fetchTableData()" class="bg-[#D5FC55] hover:bg-[#c5ec45] text-neutral-900 font-bold px-3 py-1.5 rounded-full transition text-xs whitespace-nowrap">
                            Cari
                        </button>
                    </div>

                    {{-- Dropdown Sort (Alpine Dropdown) --}}
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-4 pr-3 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[190px] justify-between">
                            <span x-text="sort === 'terbaru' ? 'Urutkan: Terbaru' : 'Urutkan: Terlama'"></span>
                            <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg py-2 right-0" style="display: none;">
                            <button type="button" @click="sort = 'terbaru'; open = false; fetchTableData()" class="block w-full text-left px-5 py-2 text-sm transition" :class="sort === 'terbaru' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]'">
                                Diunggah Terbaru
                            </button>
                            <button type="button" @click="sort = 'terlama'; open = false; fetchTableData()" class="block w-full text-left px-5 py-2 text-sm transition" :class="sort === 'terlama' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]'">
                                Diunggah Terlama
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Container Tabel Dynamic dengan Loading Overlay --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm relative min-h-[200px] overflow-hidden">
            
            {{-- Loading Overlay --}}
            <div x-show="loading"
                class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-20 flex items-center justify-center transition"
                style="display: none;">
                <div class="flex items-center gap-2 text-sm font-semibold text-[#6D28D9]">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memuat berkas...</span>
                </div>
            </div>

            <div id="table-container" class="w-full">
                @include('admin.manajemenFileTable')
            </div>
        </div>

        {{-- MODAL LIGHTBOX PREVIEW --}}
        <template x-teleport="body">
            <div x-cloak x-show="previewModalOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/70 backdrop-blur-md flex items-center justify-center p-4 sm:p-6"
                style="display: none;">

                <div @click.away="previewModalOpen = false"
                    class="bg-white rounded-3xl max-w-3xl w-full p-5 sm:p-6 shadow-2xl border border-slate-100 space-y-4">

                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                        <div class="min-w-0 pr-4">
                            <h3 class="text-lg font-bold text-slate-900 truncate" x-text="previewName"></h3>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Tombol Unduh --}}
                            <template x-if="downloadUrl">
                                <a :href="downloadUrl"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#6D28D9] hover:bg-[#5B21B6] text-white font-semibold text-sm rounded-full transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Unduh
                                </a>
                            </template>

                            {{-- Tombol Tutup --}}
                            <button @click="previewModalOpen = false"
                                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Area Pratinjau Konten --}}
                    <div class="min-h-[280px] max-h-[68vh] flex items-center justify-center overflow-auto rounded-2xl bg-slate-50 border border-slate-100 p-3">
                        <template x-if="previewLoading">
                            <div class="flex flex-col items-center gap-2 text-sm text-slate-400 font-medium py-12">
                                <svg class="w-6 h-6 animate-spin text-[#6D28D9]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memuat berkas...</span>
                            </div>
                        </template>

                        <template x-if="!previewLoading && previewUrl">
                            <div class="w-full h-full flex justify-center items-center">
                                {{-- Format Gambar --}}
                                <template x-if="previewName.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)">
                                    <img :src="previewUrl" :alt="previewName" class="max-h-[62vh] w-auto rounded-xl object-contain shadow-sm border border-slate-200">
                                </template>

                                {{-- Format PDF --}}
                                <template x-if="previewName.match(/\.(pdf)$/i)">
                                    <iframe :src="previewUrl" class="w-full h-[62vh] rounded-xl border-0 shadow-sm"></iframe>
                                </template>

                                {{-- Format Lain --}}
                                <template x-if="!previewName.match(/\.(jpg|jpeg|png|gif|webp|svg|pdf)$/i)">
                                    <div class="text-center py-10 space-y-4">
                                        <div class="w-16 h-16 rounded-2xl bg-purple-50 text-[#6D28D9] flex items-center justify-center mx-auto font-bold text-lg" x-text="(previewName.split('.').pop() || 'FILE').toUpperCase()"></div>
                                        <div>
                                            <h4 class="text-base font-bold text-slate-800">Format Dokumen / Mentahan</h4>
                                            <p class="text-sm text-slate-500 mt-1">Berkas ini tidak mendukung pratinjau langsung di browser.</p>
                                        </div>
                                        <a :href="downloadUrl" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-sm font-semibold rounded-full transition shadow-sm mt-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Unduh Berkas Sekarang
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
            <div x-cloak x-show="deleteModalOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
                style="display: none;">

                <div @click.away="deleteModalOpen = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-xl space-y-4 text-center">
                    <div class="w-14 h-14 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto text-2xl font-bold">!</div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Hapus File Ini?</h3>
                        <p class="text-sm text-slate-500 mt-1">Berkas akan dihapus secara permanen dari Cloudflare R2 dan database.</p>
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