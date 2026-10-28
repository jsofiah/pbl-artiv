@extends('layouts.admin')

@section('title', 'Edit Katalog')

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
    <div>
        <a href="{{ route('admin.katalog.index') }}"
            class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#6D28D9] mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Katalog
        </a>
        <h1 class="text-4xl font-extrabold text-slate-900">Edit Katalog</h1>
        <p class="text-slate-500 mt-2">{{ $product->name }}</p>
    </div>
</div>

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

<form method="POST" action="{{ route('admin.katalog.update', $product->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Informasi Katalog</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Katalog <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm
                                        focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi</label>
                        <textarea name="description" rows="4" maxlength="2000"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm
                                            focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition resize-none">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Harga Utama <span class="text-slate-400 font-normal">(kalau tidak ada tier)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">Rp</span>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="1000"
                                    class="w-full rounded-xl border border-slate-200 pl-12 pr-4 py-2.5 text-sm
                                            focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/20 focus:border-[#6D28D9] transition">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <input type="hidden" name="is_active" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                    @checked(old('is_active', $product->is_active))>
                            <div class="w-11 h-6 bg-slate-200 peer-checked:bg-[#6D28D9] rounded-full
                                        peer-checked:after:translate-x-full after:content-[''] after:absolute
                                        after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full
                                        after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                        <span class="text-sm font-medium text-slate-700">Aktifkan katalog ini</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Varian Tipe Paket</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola tier di halaman terpisah.</p>
                    </div>
                    <a href="{{ route('admin.katalog.tiers.index', $product->id) }}"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-[#D5FC55] text-[#1A1A1A] hover:bg-[#c5ec45] transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                        </svg>
                        Kelola Tier ({{ $product->tiers->count() }})
                    </a>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Thumbnail Katalog</h2>

                <div class="space-y-3">
                    <div id="thumbnail-preview"
                        class="w-full aspect-square rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center text-slate-400 overflow-hidden">
                        @if ($product->thumbnail_url)
                            <img src="{{ \App\Helpers\R2Helper::url($product->thumbnail_url) }}"
                                alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <circle cx="8.5" cy="9.5" r="1.5"/>
                                <path stroke-linecap="round" d="M21 16l-5-5-8 8"/>
                            </svg>
                            <span class="text-xs font-semibold tracking-widest">BELUM ADA</span>
                        @endif
                    </div>

                    <input type="file" name="thumbnail" id="thumbnail-input" accept="image/*"
                        class="hidden" onchange="previewThumbnail(this)">

                    <button type="button" onclick="document.getElementById('thumbnail-input').click()"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                                    border border-slate-200 bg-white text-sm font-semibold text-slate-700
                                    hover:border-[#6D28D9] hover:text-[#6D28D9] transition">
                        {{ $product->thumbnail_url ? 'Ganti Gambar' : 'Pilih Gambar' }}
                    </button>

                    <p class="text-xs text-slate-400 text-center">
                        Format: JPG, PNG, WEBP. Maks 10 MB.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('admin.katalog.index') }}"
            class="px-6 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition">
            Batal
        </a>
        <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-[#6D28D9] text-white text-sm font-semibold hover:bg-[#5B21B6] transition shadow-sm">
            Simpan Perubahan
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
function previewThumbnail(input) {
    const preview = document.getElementById('thumbnail-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush