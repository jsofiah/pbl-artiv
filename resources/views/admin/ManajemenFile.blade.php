@extends('layouts.admin')

@section('title', 'Manajemen File')

@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }
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
        const link = e.target.closest('#table-container nav a');
        if (link) {
            e.preventDefault();
            fetchTableData(link.href);
        }
    });">

        {{-- Filter, Search & Tab Navigasi --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm space-y-4 relative z-10">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                {{-- Tab Navigasi Async (Tanpa Reload) --}}
                <div class="flex items-center gap-2 p-1 bg-purple-50/60 rounded-xl w-fit border border-purple-100/50">
                    <button type="button" @click="switchTab('deliverables')"
                        :class="tab === 'deliverables' ? 'bg-[#745BB8] text-white shadow-sm' :
                            'text-gray-600 hover:text-[#745BB8] hover:bg-purple-100/50'"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-all">
                        Hasil Design
                    </button>
                    <button type="button" @click="switchTab('references')"
                        :class="tab === 'references' ? 'bg-[#745BB8] text-white shadow-sm' :
                            'text-gray-600 hover:text-[#745BB8] hover:bg-purple-100/50'"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-all">
                        References
                    </button>
                </div>

                {{-- Form Search & Sort --}}
                <form @submit.prevent="fetchTableData()" class="flex flex-wrap items-center gap-2">
                    {{-- Input Cari --}}
                    <div class="relative min-w-[180px] sm:min-w-[200px]">
                        <input type="text" x-model="search" @keyup.enter="fetchTableData()"
                            placeholder="Cari file / Order ID..."
                            class="w-full pl-8 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 focus:bg-white focus:border-[#745BB8] focus:ring-[#745BB8] transition placeholder:text-gray-400">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    {{-- Dropdown Sort --}}
                    <select x-model="sort" @change="fetchTableData()"
                        class="bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 py-1.5 pl-2.5 pr-7 focus:bg-white focus:border-[#745BB8] focus:ring-[#745BB8]">
                        <option value="terbaru">Urutkan: Diunggah Terbaru</option>
                        <option value="terlama">Urutkan: Diunggah Terlama</option>
                    </select>
                </form>
            </div>

            {{-- Container Tabel Dynamic dengan Loading Overlay --}}
            <div class="relative min-h-[200px]">
                <div x-show="loading"
                    class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-20 flex items-center justify-center rounded-xl transition">
                    <div class="flex items-center gap-2 text-xs font-semibold text-[#745BB8]">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span>Memuat berkas...</span>
                    </div>
                </div>

                <div id="table-container">
                    @include('admin.manajemenFileTable')
                </div>
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
                    class="bg-white rounded-3xl max-w-3xl w-full p-5 sm:p-6 shadow-2xl border border-gray-100 space-y-4">

                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3.5">
                        <div class="min-w-0 pr-4">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 truncate" x-text="previewName"></h3>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Tombol Unduh --}}
                            <template x-if="downloadUrl">
                                <a :href="downloadUrl"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#745BB8] hover:bg-[#5C469C] text-white font-semibold text-xs rounded-xl transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Unduh
                                </a>
                            </template>

                            {{-- Tombol Tutup --}}
                            <button @click="previewModalOpen = false"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Area Pratinjau Konten --}}
                    <div
                        class="min-h-[280px] max-h-[68vh] flex items-center justify-center overflow-auto rounded-2xl bg-gray-50/80 border border-gray-100 p-3">
                        <template x-if="previewLoading">
                            <div class="flex flex-col items-center gap-2 text-xs text-gray-400 font-medium py-12">
                                <svg class="w-6 h-6 animate-spin text-[#745BB8]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>Memuat berkas...</span>
                            </div>
                        </template>

                        <template x-if="!previewLoading && previewUrl">
                            <div class="w-full h-full flex justify-center items-center">
                                {{-- Format Gambar --}}
                                <template x-if="previewName.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)">
                                    <img :src="previewUrl" :alt="previewName"
                                        class="max-h-[62vh] w-auto rounded-xl object-contain shadow-sm border border-gray-200">
                                </template>

                                {{-- Format PDF --}}
                                <template x-if="previewName.match(/\.(pdf)$/i)">
                                    <iframe :src="previewUrl"
                                        class="w-full h-[62vh] rounded-xl border-0 shadow-sm"></iframe>
                                </template>

                                {{-- Format Lain --}}
                                <template x-if="!previewName.match(/\.(jpg|jpeg|png|gif|webp|svg|pdf)$/i)">
                                    <div class="text-center py-10 space-y-3">
                                        <div class="w-14 h-14 rounded-2xl bg-purple-100 text-[#745BB8] flex items-center justify-center mx-auto font-bold text-base"
                                            x-text="(previewName.split('.').pop() || 'FILE').toUpperCase()">
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-800">Format Dokumen / Mentahan</h4>
                                            <p class="text-xs text-gray-400 mt-0.5">Berkas ini tidak mendukung pratinjau
                                                langsung di browser.</p>
                                        </div>
                                        <a :href="downloadUrl"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#745BB8] hover:bg-[#5C469C] text-white text-xs font-semibold rounded-xl transition shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
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
                class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4"
                style="display: none;">

                <div @click.away="deleteModalOpen = false"
                    class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-xl space-y-4 text-center">
                    <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Hapus File Ini?</h3>
                        <p class="text-xs text-slate-500 mt-1">Berkas akan dihapus secara permanen dari Cloudflare R2 dan
                            database.</p>
                    </div>

                    <form :action="deleteUrl" method="POST" class="flex items-center gap-2 pt-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="deleteModalOpen = false"
                            class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="w-1/2 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </template>

    </div>
@endsection
