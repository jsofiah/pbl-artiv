@extends('layouts.designer')

@section('title', 'Detail Job')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6" x-data="{ showAmbil: false }">
    <a href="{{ route('designer.job-pool') }}"
    class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#6D28D9] mb-4 transition rounded-full px-3 py-2 bg-white border border-slate-200 hover:bg-[#D5FC55] hover:text-[#1A1A1A] hover:border-[#D5FC55]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke Job Pool
    </a>
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-slate-900">Detail Job</h1>
        <p class="text-slate-500 mt-1">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#F3F1FA] text-[#6D28D9] text-xs font-bold">
                {{ $order->order_code }}
            </span>
            · Periksa detail pekerjaan sebelum mengambil order.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            {{-- Preview & Referensi Desain --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-lg text-[#1E1B39]">Preview & Referensi Desain</h2>
                    @php
                        $imageRef = $order->references
                            ->where('type', 'file')
                            ->first(fn($ref) => in_array(strtolower($ref->mime_type ?? ''), [
                                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'
                            ]));
                    @endphp

                    @if ($imageRef)
                        <button type="button"
                                onclick="previewImage(
                                    '{{ route('designer.job-pool.reference.preview', ['order' => $order->id, 'reference' => $imageRef->id]) }}',
                                    '{{ route('designer.job-pool.reference.download', ['order' => $order->id, 'reference' => $imageRef->id]) }}',
                                    '{{ addslashes($imageRef->file_name) }}'
                                )"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#6D28D9] hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                            </svg>
                            Perbesar
                        </button>
                    @endif
                </div>

                <div class="relative aspect-video rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center mb-4 overflow-hidden">
                    @if ($imageRef)
                        <div x-data="{
                                imgUrl: '',
                                loading: true,
                                async loadPreview() {
                                    try {
                                        const res = await fetch('{{ route('designer.job-pool.reference.preview', ['order' => $order->id, 'reference' => $imageRef->id]) }}', {
                                            headers: { 'Accept': 'application/json' }
                                        });
                                        const data = await res.json();
                                        this.imgUrl = data.url;
                                    } catch(e) {
                                        console.error(e);
                                    } finally {
                                        this.loading = false;
                                    }
                                }
                            }"
                            x-init="loadPreview()"
                            class="w-full h-full">

                            <template x-if="loading">
                                <div class="w-full h-full flex items-center justify-center bg-slate-100">
                                    <svg class="w-10 h-10 text-[#6D28D9] animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </div>
                            </template>

                            <template x-if="!loading && imgUrl">
                                <img :src="imgUrl" alt="{{ $imageRef->file_name }}"
                                     class="w-full h-full object-cover">
                            </template>

                            <template x-if="!loading && !imgUrl">
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-xs text-slate-400 font-medium">Gagal memuat preview</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @else
                        @php
                            $productThumb = \App\Helpers\R2Helper::url($order->product->thumbnail_url);
                        @endphp

                        @if ($productThumb)
                            <img src="{{ $productThumb }}" alt="{{ $order->product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="text-center">
                                <svg class="w-16 h-16 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs text-slate-400 font-medium">Preview tidak tersedia</p>
                            </div>
                        @endif
                    @endif
                </div>

                @if (!$imageRef)
                    <p class="text-xs text-slate-400 italic text-center">
                        Customer tidak melampirkan gambar referensi. Preview menampilkan gambar produk.
                    </p>
                @endif
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-1 h-5 rounded-full bg-[#6D28D9]"></span>
                    <h2 class="font-bold text-lg text-[#1E1B39]">Brief Customer</h2>
                </div>
                @if ($order->brief_note)
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $order->brief_note }}</p>
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic">Customer tidak memberikan brief tambahan.</p>
                @endif
            </div>

        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-1 h-5 rounded-full bg-[#6D28D9]"></span>
                    <h2 class="font-bold text-lg text-[#1E1B39]">Informasi Pesanan</h2>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">Kategori</span>
                        <span class="font-semibold text-[#1E1B39] text-right">{{ $order->product->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">Paket</span>
                        <span class="font-semibold text-[#1E1B39] text-right">{{ $order->productTier->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">Jumlah</span>
                        <span class="font-semibold text-[#1E1B39] text-right">{{ $order->quantity }}x</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">Deadline</span>
                        <span class="font-semibold text-[#1E1B39] text-right">
                            {{ $order->deadline?->translatedFormat('d M Y') ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="border-t border-dashed border-slate-200 my-4"></div>

                <div class="relative bg-[#F3F1FA] rounded-2xl p-5 overflow-hidden">
                    <span class="absolute inset-y-0 left-0 w-1.5 bg-[#D5FC55]"></span>
                    <div class="pl-3 flex items-center justify-between gap-4">
                        {{-- Label --}}
                        <div class="flex items-center gap-1.5">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Budget</p>
                        </div>

                        <p class="text-2xl font-extrabold text-[#6D28D9]">
                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-1 h-5 rounded-full bg-[#6D28D9]"></span>
                        <h2 class="font-bold text-lg text-[#1E1B39]">File Referensi</h2>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">{{ $order->references->count() }} Berkas</span>
                </div>

                @if ($order->references->isEmpty())
                    <p class="text-sm text-slate-400 italic">Tidak ada file referensi.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($order->references as $ref)
                            @php
                                $isImage = $ref->type === 'file' && in_array(strtolower($ref->mime_type ?? ''), ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg']);
                            @endphp

                            <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3 border border-slate-100 hover:border-[#6D28D9]/30 transition group">
                                <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0">
                                    @if ($ref->type === 'file')
                                        <svg class="w-4 h-4 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5l4-4a3 3 0 10-4.24-4.24l-6 6a3 3 0 000 4.24m3 3.5l-4 4a3 3 0 11-4.24-4.24l6-6a3 3 0 014.24 0"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-[#1E1B39] truncate">
                                        {{ $ref->file_name ?? $ref->external_url }}
                                    </p>
                                </div>

                                @if ($ref->type === 'file')
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @if ($isImage)
                                            <button type="button"
                                                    onclick="previewImage(
                                                        '{{ route('designer.job-pool.reference.preview', ['order' => $order->id, 'reference' => $ref->id]) }}',
                                                        '{{ route('designer.job-pool.reference.download', ['order' => $order->id, 'reference' => $ref->id]) }}',
                                                        '{{ addslashes($ref->file_name ?? 'Gambar') }}'
                                                    )"
                                                    class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:border-[#6D28D9] hover:bg-[#6D28D9]/5 flex items-center justify-center text-slate-500 hover:text-[#6D28D9] transition shadow-sm"
                                                    title="Lihat gambar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                            </button>
                                        @endif

                                        <a href="{{ route('designer.job-pool.reference.download', ['order' => $order->id, 'reference' => $ref->id]) }}"
                                            class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:border-[#6D28D9] hover:bg-[#6D28D9]/5 flex items-center justify-center text-slate-500 hover:text-[#6D28D9] transition shadow-sm"
                                            title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                            </svg>
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ $ref->external_url }}" target="_blank"
                                        class="shrink-0 w-9 h-9 rounded-lg bg-white border border-slate-200 hover:border-[#6D28D9] hover:bg-[#6D28D9]/5 flex items-center justify-center text-slate-500 hover:text-[#6D28D9] transition shadow-sm"
                                        title="Buka Link">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-4 flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-xs text-amber-800">
                        Dengan menekan <strong>Ambil Order</strong>, kamu berkomitmen mengerjakan sesuai deadline.
                    </p>
                </div>

                <form action="{{ route('designer.job-pool.ambil', $order->id) }}" method="POST" id="form-ambil">
                @csrf
                <button type="button" @click="showAmbil = true"
                        class="w-full inline-flex items-center justify-center gap-2 bg-[#6D28D9] hover:bg-[#5B21B6] text-white font-bold px-6 py-3.5 rounded-full transition shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#6D28D9] focus-visible:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ambil Order
                </button>
            </form>

            <a href="{{ route('designer.job-pool') }}"
            class="block text-center w-full mt-3 px-6 py-3 rounded-full border-2 border-slate-200 font-semibold text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9] transition">
                Kembali
            </a>
            </div>

        </div>
    </div>

    @include('designer.pekerjaan.partials.modal-image-preview')

    <div x-show="showAmbil" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @click.self="showAmbil = false"
        @keydown.escape.window="showAmbil = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            @click.stop>

            {{-- Icon --}}
            <div class="pt-8 pb-4 flex justify-center">
                <div class="w-20 h-20 rounded-full bg-[#F3F1FA] flex items-center justify-center">
                    <div class="w-14 h-14 rounded-full bg-[#6D28D9] flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="px-6 pb-6 text-center">
                <h3 class="text-xl font-extrabold text-[#1E1B39] mb-2">Ambil Order Ini?</h3>
                <p class="text-sm text-slate-500 leading-relaxed mb-5">
                    Dengan mengambil order ini, kamu <strong class="text-[#6D28D9]">berkomitmen</strong> mengerjakan sesuai deadline yang ditentukan.
                </p>

                <div class="bg-[#F3F1FA] rounded-2xl p-4 text-left mb-5">
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between gap-2">
                            <span class="text-slate-500">Order Code</span>
                            <span class="font-bold text-[#1E1B39]">{{ $order->order_code }}</span>
                        </div>
                        <div class="flex justify-between gap-2">
                            <span class="text-slate-500">Deadline</span>
                            <span class="font-bold text-[#1E1B39]">{{ $order->deadline?->translatedFormat('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-2">
                            <span class="text-slate-500">Budget</span>
                            <span class="font-bold text-[#6D28D9]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showAmbil = false"
                            class="flex-1 px-5 py-3 rounded-full border-2 border-slate-200 font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" onclick="document.getElementById('form-ambil').submit()"
                            class="flex-1 px-5 py-3 rounded-full bg-[#D5FC55] hover:bg-[#C5EC45] text-neutral-900 font-bold transition shadow-sm">
                        Ya, Ambil
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
async function previewImage(previewUrl, downloadUrl, title) {
    const modal   = document.getElementById('image-preview-modal');
    const img     = document.getElementById('image-preview-img');
    const titleEl = document.getElementById('image-preview-title');
    const dl      = document.getElementById('image-preview-download');

    if (!modal) return;

    titleEl.textContent = title;
    img.src = '';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    try {
        const res = await fetch(previewUrl, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Gagal load preview');
        const data = await res.json();
        img.src = data.url;
        dl.href = downloadUrl;
    } catch (err) {
        console.error(err);
        alert('Gagal memuat gambar.');
        closeImagePreview();
    }
}

function closeImagePreview() {
    const modal = document.getElementById('image-preview-modal');
    const img   = document.getElementById('image-preview-img');
    if (modal) modal.classList.add('hidden');
    if (img) img.src = '';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('image-preview-modal');
        if (modal && !modal.classList.contains('hidden')) closeImagePreview();
    }
});
</script>
@endpush