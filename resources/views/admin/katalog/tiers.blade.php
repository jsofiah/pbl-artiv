@extends('layouts.admin')

@section('title', 'Kelola Tier')

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
    <div>
        <a href="{{ route('admin.katalog.edit', $product->id) }}"
            class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#6D28D9] mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Edit Katalog
        </a>
        <h1 class="text-4xl font-extrabold text-slate-900">Varian Tipe Paket</h1>
        <p class="text-slate-500 mt-2">{{ $product->name }}</p>
    </div>
</div>

@if (session('status'))
    <x-shared.alert type="success" :message="session('status')" :auto-hide="true" :duration="4000" />
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-4">
        @forelse ($product->tiers as $tier)
            <div class="bg-white rounded-2xl border border-slate-200 p-4"
                 x-data="{ edit: false }">

                <div x-show="!edit" class="flex items-start gap-4">
                    <div class="w-20 h-20 rounded-xl bg-slate-100 border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center">
                        @if ($tier->thumbnail_url)
                            <img src="{{ \App\Helpers\R2Helper::url($tier->thumbnail_url) }}"
                                 alt="{{ $tier->name }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <circle cx="8.5" cy="9.5" r="1.5"/>
                                <path stroke-linecap="round" d="M21 16l-5-5-8 8"/>
                            </svg>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-slate-900">{{ $tier->name }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                         {{ $tier->is_active ? 'bg-lime-100 text-lime-700' : 'bg-slate-200 text-slate-500' }}">
                                {{ $tier->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </div>
                        <p class="text-lg font-extrabold text-[#6D28D9] mt-1">
                            Rp {{ number_format($tier->price, 0, ',', '.') }}
                        </p>
                        @if ($tier->description)
                            <p class="text-sm text-slate-500 mt-1">{{ $tier->description }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col gap-1 shrink-0">
                        <button type="button" @click="edit = true"
                                class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9] transition">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.katalog.tiers.destroy', [$product->id, $tier->id]) }}"
                                onsubmit="return confirm('Hapus tier {{ e($tier->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:border-rose-300 hover:text-rose-600 transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('admin.katalog.tiers.update', [$product->id, $tier->id]) }}"
                      enctype="multipart/form-data"
                      x-show="edit" style="display: none;"
                      class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-bold text-slate-900">Edit Tier: {{ $tier->name }}</p>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-20 h-20 rounded-xl bg-slate-100 border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center">
                            <img x-ref="previewImg"
                                    src="{{ $tier->thumbnail_url ? \App\Helpers\R2Helper::url($tier->thumbnail_url) : '' }}"
                                    alt="{{ $tier->name }}"
                                    class="w-full h-full object-cover {{ $tier->thumbnail_url ? '' : 'hidden' }}">

                            @if (!$tier->thumbnail_url)
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                                    <circle cx="8.5" cy="9.5" r="1.5"/>
                                    <path stroke-linecap="round" d="M21 16l-5-5-8 8"/>
                                </svg>
                            @endif
                        </div>

                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Thumbnail</label>
                            <input type="file" name="thumbnail" accept="image/*"
                                    class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                            file:text-xs file:font-semibold file:bg-violet-50 file:text-[#6D28D9]
                                            hover:file:bg-violet-100 cursor-pointer"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => {
                                                $refs.previewImg.src = e.target.result;
                                                $refs.previewImg.classList.remove('hidden');
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                    ">
                            <p class="text-[10px] text-slate-400 mt-1">JPG, PNG, WEBP. Maks 10 MB.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Tier <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ $tier->name }}" required
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm
                                            focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Harga <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-semibold">Rp</span>
                                <input type="number" name="price" value="{{ $tier->price }}" required min="0" step="1000"
                                        class="w-full rounded-lg border border-slate-200 pl-9 pr-3 py-2 text-sm
                                                focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9]">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Deskripsi (Opsional)</label>
                        <textarea name="description" rows="2" maxlength="1000"
                            placeholder="Fitur atau keunggulan tier ini..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm resize-none
                                focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9]">{{ $tier->description }}
                        </textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <input type="hidden" name="is_active" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                @checked($tier->is_active)>
                            <div class="w-10 h-5 bg-slate-200 peer-checked:bg-[#D5FC55] rounded-full
                                        peer-checked:after:translate-x-full after:content-[''] after:absolute
                                        after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full
                                        after:h-4 after:w-4 after:transition-all"></div>
                        </label>
                        <span class="text-xs font-semibold text-slate-700 peer-checked:text-lime-700">
                            Tampilkan tier ini ke customer
                        </span>
                    </div>

                    <div class="flex gap-2 pt-2 border-t border-slate-100">
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-[#6D28D9] text-white text-xs font-semibold hover:bg-[#5B21B6] transition">
                            Simpan Perubahan
                        </button>
                        <button type="button" @click="edit = false"
                                class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 py-12 text-center text-slate-400">
                Belum ada tier. Tambahkan tier pertama di form kanan.
            </div>
        @endforelse
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Tambah Tier Baru</h2>

            <form method="POST" action="{{ route('admin.katalog.tiers.store', $product->id) }}"
                enctype="multipart/form-data" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Tier <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Simple / Medium / Full"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm
                        focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Harga <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">Rp</span>
                        <input type="number" name="price" required min="0" step="1000" placeholder="150000"
                            class="w-full rounded-xl border border-slate-200 pl-12 pr-4 py-2.5 text-sm
                                focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi (Opsional)</label>
                    <textarea name="description" rows="2" maxlength="1000"
                            placeholder="Fitur atau keunggulan tier ini..."
                            class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm resize-none
                                focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
                    </textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Thumbnail (Opsional)</label>
                    <input type="file" name="thumbnail" accept="image/*"
                            class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                    file:text-xs file:font-semibold file:bg-violet-50 file:text-[#6D28D9]
                                    hover:file:bg-violet-100 cursor-pointer">
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                        <div class="w-10 h-5 bg-slate-200 peer-checked:bg-[#D5FC55] rounded-full
                                    peer-checked:after:translate-x-full after:content-[''] after:absolute
                                    after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full
                                    after:h-4 after:w-4 after:transition-all"></div>
                    </label>
                    <span class="text-xs font-semibold text-slate-700">Tampilkan tier ini ke customer</span>
                </div>

                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-xl bg-[#6D28D9] text-white text-sm font-semibold hover:bg-[#5B21B6] transition">
                    Tambah Tier
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush