@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
<div x-data="{
    showAddModal: false,
    addKeyword: '',
    addType: 'word',

    showEditModal: false,
    editId: '',
    editKeyword: '',
    editType: 'word',
    editIsActive: true,
    editActionUrl: '',

    showDeleteModal: false,
    deleteId: '',
    deleteKeywordName: '',
    deleteActionUrl: '',

    showFeeModal: false,
    feeAmount: {{ (int) $revisionFee->fee }},
    feeName: '{{ addslashes($revisionFee->name) }}',

    openAddModal() {
        this.addKeyword = '';
        this.addType = 'word';
        this.showAddModal = true;
    },

    openEditModal(id, keyword, type, isActive) {
        this.editId = id;
        this.editKeyword = keyword;
        this.editType = type;
        this.editIsActive = isActive;
        this.editActionUrl = '{{ url('admin/pengaturan/blacklist') }}/' + id;
        this.showEditModal = true;
    },

    openDeleteModal(id, keyword) {
        this.deleteId = id;
        this.deleteKeywordName = keyword;
        this.deleteActionUrl = '{{ url('admin/pengaturan/blacklist') }}/' + id;
        this.showDeleteModal = true;
    }
}" class="max-w-7xl mx-auto space-y-8">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <x-shared.alert type="success" :message="session('success')" :auto-hide="true" :duration="4000" />
    @endif

    @if(session('error') || $errors->any())
        <x-shared.alert type="error" :auto-hide="false">
            <p class="font-semibold mb-1">Terjadi kesalahan:</p>
            @if(session('error'))
                <p class="text-sm">{{ session('error') }}</p>
            @endif
            @if($errors->any())
                <ul class="text-sm list-disc list-inside space-y-0.5 mt-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </x-shared.alert>
    @endif

    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pengaturan</h1>
            <p class="text-slate-500 mt-1.5">Kelola blacklist keyword & biaya revisi tambahan.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 text-[#6D28D9] flex items-center justify-center shrink-0 border border-violet-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-lg font-bold text-gray-900">Pengaturan Harga Revisi</h2>
                        <x-shared.ui.badge :variant="$revisionFee->is_active ? 'lime' : 'slate'" size="sm">
                            {{ $revisionFee->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-shared.ui.badge>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 max-w-xl leading-relaxed">
                        Customer berhak atas <strong>3x revisi gratis</strong>. Biaya ini dikenakan otomatis per pengajuan revisi tambahan.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0 bg-violet-50 px-5 py-3.5 rounded-2xl border border-violet-100">
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block uppercase tracking-wider">Tarif Per Revisi</span>
                    <span class="text-xl font-bold text-[#6D28D9]">Rp {{ number_format($revisionFee->fee, 0, ',', '.') }}</span>
                </div>
                <button type="button"
                        @click="showFeeModal = true"
                        class="px-4 py-2 text-sm font-semibold text-white bg-[#6D28D9] hover:bg-[#5B21B6] active:scale-95 rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Ubah Tarif
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Blacklist Keyword</h2>
                <p class="text-sm text-slate-500 mt-0.5">Cegah pertukaran kontak / transaksi di luar sistem.</p>
            </div>

            <button type="button"
                    @click="openAddModal()"
                    class="inline-flex items-center gap-2 bg-[#6D28D9] hover:bg-[#5B21B6] text-white text-sm font-semibold px-5 py-2.5 rounded-full transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Keyword
            </button>
        </div>

        <form method="GET" action="{{ route('admin.pengaturan') }}" class="py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <input type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari keyword blacklist..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                <div x-data="{
                        open: false,
                        selected: '{{ request('type') }}',
                        labels: {
                            '': 'Semua Tipe Keyword',
                            'word': 'Word (Kata Dilarang)',
                            'link': 'Link (Tautan URL)',
                            'phone': 'Phone (Nomor Telepon)',
                            'email': 'Email (Alamat Email)'
                        },
                        get selectedLabel() {
                            return this.labels[this.selected] ?? 'Semua Tipe Keyword';
                        },
                        choose(value) {
                            this.selected = value;
                            this.open = false;
                            const form = this.$root.closest('form');
                            let hidden = form.querySelector('input[name=type]');
                            if (!hidden) {
                                hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = 'type';
                                form.appendChild(hidden);
                            }
                            hidden.value = value;
                            form.submit();
                        }
                    }"
                    class="relative shrink-0 w-full sm:w-auto">

                    <button type="button" @click="open = !open"
                            class="flex items-center gap-2 w-full sm:w-max bg-white border border-gray-200 rounded-full pl-5 pr-4 py-2.5 text-sm font-medium text-slate-700 hover:border-[#6D28D9] transition min-w-[220px] justify-between">
                        <span x-text="selectedLabel">Semua Tipe Keyword</span>
                        <svg class="w-4 h-4 text-slate-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-cloak
                        class="absolute z-30 mt-2 w-full bg-white border border-gray-200 rounded-2xl shadow-lg py-2 right-0"
                        style="display: none;">
                        <button type="button" @click="choose('')"
                                class="block w-full text-left px-5 py-2 text-sm transition"
                                :class="selected === '' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]'">
                            Semua Tipe Keyword
                        </button>
                        <button type="button" @click="choose('word')"
                                class="block w-full text-left px-5 py-2 text-sm transition"
                                :class="selected === 'word' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]'">
                            Word (Kata Dilarang)
                        </button>
                        <button type="button" @click="choose('link')"
                                class="block w-full text-left px-5 py-2 text-sm transition"
                                :class="selected === 'link' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]'">
                            Link (Tautan URL)
                        </button>
                        <button type="button" @click="choose('phone')"
                                class="block w-full text-left px-5 py-2 text-sm transition"
                                :class="selected === 'phone' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]'">
                            Phone (Nomor Telepon)
                        </button>
                        <button type="button" @click="choose('email')"
                                class="block w-full text-left px-5 py-2 text-sm transition"
                                :class="selected === 'email' ? 'bg-violet-50 text-[#6D28D9] font-semibold' : 'text-slate-600 hover:bg-violet-50 hover:text-[#6D28D9]'">
                            Email (Alamat Email)
                        </button>
                    </div>
                </div>

                @if(request('q') || request('type'))
                    <a href="{{ route('admin.pengaturan') }}"
                    class="px-3 py-2.5 text-sm text-gray-500 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-full transition font-medium">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Data Table --}}
        <div class="overflow-x-auto no-scrollbar -mx-6 sm:mx-0">
            <table class="w-full text-left text-sm text-gray-600 border-collapse">
                <thead class="text-gray-400 font-semibold uppercase text-[11px] tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="py-4 px-6 text-center w-16">NO</th>
                        <th class="py-4 px-6">KEYWORD</th>
                        <th class="py-4 px-6 text-center w-40">TYPE</th>
                        <th class="py-4 px-6 text-center w-36">STATUS</th>
                        <th class="py-4 px-6 text-center w-52">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($keywords as $index => $item)
                        @php
                            $rowNum = sprintf('%02d', $index + 1 + ($keywords->currentPage() - 1) * $keywords->perPage());

                            $typeBadgeVariant = match($item->type) {
                                'link'  => 'blue',
                                'phone' => 'amber',
                                'email' => 'emerald',
                                default => 'violet',
                            };

                            $typeLabel = match($item->type) {
                                'link'  => 'Link / URL',
                                'phone' => 'Nomor Telepon',
                                'email' => 'Alamat Email',
                                default => 'Kata Terlarang',
                            };

                            $reasonHint = match($item->type) {
                                'link'  => 'Tautan eksternal yang dilarang dalam sistem',
                                'phone' => 'Pencegahan kontak atau transaksi luar platform',
                                'email' => 'Pencegahan kontak surel luar platform',
                                default => 'Kata/frasa yang dibatasi dalam percakapan',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="py-4 px-6 text-center text-gray-400 font-medium text-sm">
                                {{ $rowNum }}
                            </td>

                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-900 text-sm tracking-tight">
                                    {{ $item->keyword }}
                                </div>
                                <div class="text-gray-400 text-[11px] mt-0.5 font-normal">
                                    {{ $reasonHint }}
                                </div>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <x-shared.ui.badge :variant="$typeBadgeVariant" :pill="false" size="sm">
                                    {{ $typeLabel }}
                                </x-shared.ui.badge>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <x-shared.ui.badge :variant="$item->is_active ? 'emerald' : 'slate'" :dot="true" size="sm">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-shared.ui.badge>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                            @click="openEditModal('{{ $item->id }}', '{{ addslashes($item->keyword) }}', '{{ $item->type }}', {{ $item->is_active ? 'true' : 'false' }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-violet-50 hover:text-[#6D28D9] hover:border-violet-200 transition shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    <button type="button"
                                            @click="openDeleteModal('{{ $item->id }}', '{{ addslashes($item->keyword) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <div class="max-w-xs mx-auto">
                                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="font-medium text-gray-600">Tidak ada keyword blacklist ditemukan.</p>
                                    <p class="text-sm text-gray-400 mt-1">Klik "Tambah Keyword" di atas untuk menambahkan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-500 font-medium">
                Menampilkan
                <span class="font-bold text-gray-800">{{ $keywords->firstItem() ?? 0 }} - {{ $keywords->lastItem() ?? 0 }}</span>
                dari
                <span class="font-bold text-gray-800">{{ $keywords->total() }}</span>
                Keyword
            </div>

            @if($keywords->hasPages())
                <nav class="flex items-center gap-1.5 select-none">
                    @if($keywords->onFirstPage())
                        <span class="px-3.5 py-1.5 text-sm text-gray-300 border border-gray-100 rounded-xl cursor-not-allowed">&lsaquo; Sebelumnya</span>
                    @else
                        <a href="{{ $keywords->previousPageUrl() }}" class="px-3.5 py-1.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-[#6D28D9] hover:text-[#6D28D9] transition">&lsaquo; Sebelumnya</a>
                    @endif

                    @foreach($keywords->getUrlRange(1, $keywords->lastPage()) as $page => $url)
                        @if($page == $keywords->currentPage())
                            <span class="px-3 py-1.5 text-sm font-bold text-white bg-[#6D28D9] rounded-xl shadow-sm">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1.5 text-sm font-semibold text-gray-600 hover:text-[#6D28D9] hover:bg-violet-50 rounded-xl transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($keywords->hasMorePages())
                        <a href="{{ $keywords->nextPageUrl() }}" class="px-3.5 py-1.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-[#6D28D9] hover:text-[#6D28D9] transition">Selanjutnya &rsaquo;</a>
                    @else
                        <span class="px-3.5 py-1.5 text-sm text-gray-300 border border-gray-100 rounded-xl cursor-not-allowed">Selanjutnya &rsaquo;</span>
                    @endif
                </nav>
            @endif
        </div>
    </div>

    @include('admin.pengaturan.partials.modals')

</div>
@endsection