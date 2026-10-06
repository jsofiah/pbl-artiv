@extends('layouts.customer')

@section('title', 'Form Pemesanan')

@section('content')
<div class="max-w-5xl mx-auto py-8">
        <div x-data="pemesananForm(
            {{ Js::from($tiers) }},
            {{ Js::from($expressFeeMap) }},
            {
                selectedTier: '{{ old('product_tier_id', $pemesananData['product_tier_id'] ?? $defaultTier?->id) }}',
                quantity: {{ old('quantity', $pemesananData['quantity'] ?? 1) }},
                deadlineOption: '{{ old('deadline_option', ($pemesananData['is_express'] ?? false) ? 'express' : 'default') }}',
                targetDeadline: '{{ old('target_deadline', isset($pemesananData['deadline']) ? \Carbon\Carbon::parse($pemesananData['deadline'])->format('Y-m-d') : now()->addDays(2)->format('Y-m-d')) }}',
                briefNote: '{{ old('brief_note', $pemesananData['brief_note'] ?? '') }}'
            }
        )">

    {{-- Header --}}
    <div class="mb-8">
        <span class="inline-block px-3 py-1 rounded-full bg-violet-100 text-[#6D28D9] text-xs font-bold mb-3">
            TAHAP 1 DARI 3 • PENGISIAN FORMULIR
        </span>
        <h1 class="text-4xl font-extrabold text-slate-900">Form Pemesanan</h1>
        <div class="w-14 h-1.5 bg-[#6D28D9] rounded-full mt-2 mb-3"></div>
        <p class="text-slate-500 max-w-2xl">Lengkapi detail pesananmu agar desainer dapat memahami kebutuhan</p>
    </div>

    {{-- Info Produk --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded-xl bg-[#6D28D9] text-white font-bold flex items-center justify-center shrink-0 overflow-hidden">
                @php
                    $thumbUrl = \App\Helpers\R2Helper::url($product->thumbnail_url);
                @endphp
                @if ($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="hidden w-full h-full items-center justify-center">
                        {{ strtoupper(substr($product->name, 0, 1)) }}
                    </span>
                @else
                    {{ strtoupper(substr($product->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1">
                <span class="inline-block px-2.5 py-0.5 rounded-md bg-violet-50 text-[#6D28D9] text-xs font-semibold mb-1">
                    {{ $product->name }}
                </span>
                <h2 class="font-bold text-xl text-slate-900">{{ $product->name }}</h2>
                <p class="text-sm text-slate-500">
                    Harga mulai dari Rp {{ number_format($product->tiers->min('price') ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <a href="{{ route('customer.katalog.detail', $product->id) }}"
                class="shrink-0 px-4 py-2 rounded-xl border-2 border-slate-200 text-sm font-semibold text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9] transition">
                Ubah Jasa
            </a>
        </div>
    </div>

    <form action="{{ route('customer.pemesanan.ringkasan', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-8">
            <h3 class="flex items-center gap-2 font-bold text-lg text-slate-900 mb-4">
                <span class="w-7 h-7 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-sm font-bold">1</span>
                Pilih Paket Desain
            </h3>

            @if ($product->tiers->isEmpty())
                <div class="bg-white rounded-2xl p-8 text-center border border-slate-100">
                    <p class="text-slate-500">Belum ada paket tersedia untuk jasa ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($product->tiers as $tier)
                        @php
                            $isPopular = $loop->index === 1;
                            $isSelected = old('product_tier_id', $defaultTier?->id) == $tier->id;
                        @endphp
                        <label class="relative border-2 rounded-2xl p-5 cursor-pointer transition"
                                :class="selectedTier === '{{ $tier->id }}' ? 'border-[#6D28D9] bg-violet-50/50' : 'border-slate-200 hover:border-[#6D28D9]/50'">

                            @if ($isPopular)
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-[#D5FC55] text-neutral-900 text-[10px] font-bold">
                                    PALING POPULER
                                </span>
                            @endif

                            <input type="radio" name="product_tier_id" value="{{ $tier->id }}" class="hidden"
                                    x-model="selectedTier" required>

                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-slate-500 uppercase">{{ $tier->name }}</span>
                                <span class="w-5 h-5 rounded-full flex items-center justify-center transition"
                                        :class="selectedTier === '{{ $tier->id }}' ? 'bg-[#6D28D9]' : 'border-2 border-slate-300'">
                                    <svg x-show="selectedTier === '{{ $tier->id }}'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </div>

                            <p class="text-2xl font-extrabold text-[#6D28D9]">
                                Rp {{ number_format($tier->price, 0, ',', '.') }}
                                <span class="text-sm font-normal text-slate-500">/ paket</span>
                            </p>

                            @if ($tier->description)
                                <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
                                    @foreach (explode("\n", $tier->description) as $feature)
                                        @if (trim($feature))
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-[#6D28D9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>{{ trim($feature) }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </label>
                    @endforeach
                </div>
            @endif
            @error('product_tier_id')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-8">
            <h3 class="flex items-center gap-2 font-bold text-lg text-slate-900 mb-4">
                <span class="w-7 h-7 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-sm font-bold">2</span>
                Jumlah Pesanan
            </h3>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between gap-4">
                <div>
                    <p class="font-semibold text-slate-900">Kuantitas Lisensi / Variasi</p>
                    <p class="text-sm text-slate-500">Tentukan berapa banyak karakter atau paket lisensi yang ingin Anda pesan.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="decrementQty()"
                            class="w-10 h-10 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                        </svg>
                    </button>

                    <div class="w-14 h-10 flex items-center justify-center border border-slate-200 rounded-lg font-bold text-slate-900"
                        x-text="quantity"></div>

                    <input type="hidden" name="quantity" :value="quantity">

                    <button type="button" @click="incrementQty()"
                            class="w-10 h-10 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="flex items-center gap-2 font-bold text-lg text-slate-900 mb-4">
                <span class="w-7 h-7 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-sm font-bold">3</span>
                Pilih Deadline Pengerjaan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="border-2 rounded-2xl p-5 cursor-pointer transition"
                        :class="deadlineOption === 'default' ? 'border-[#6D28D9] bg-violet-50/50' : 'border-slate-200 hover:border-[#6D28D9]/50'">
                    <input type="radio" name="deadline_option" value="default" class="hidden"
                            x-model="deadlineOption">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-5 h-5 rounded-full border-2 border-[#6D28D9] flex items-center justify-center">
                            <span class="w-2.5 h-2.5 rounded-full transition"
                                    :class="deadlineOption === 'default' ? 'bg-[#6D28D9]' : 'bg-transparent'"></span>
                        </span>
                        <span class="font-bold text-slate-900">Standar (Normal)</span>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Waktu pengerjaan mengikuti alur antrean dan disesuaikan dengan estimasi waktu pada paket yang dipilih.
                        Proses pengerjaan dimulai setelah pesanan dikonfirmasi dan pembayaran berhasil.
                    </p>
                    <p class="text-sm font-semibold text-emerald-600 mt-3">Bebas Biaya Tambahan</p>
                </label>

                <label class="border-2 rounded-2xl p-5 cursor-pointer transition"
                        :class="deadlineOption === 'express' ? 'border-[#6D28D9] bg-violet-50/50' : 'border-slate-200 hover:border-[#6D28D9]/50'">
                    <input type="radio" name="deadline_option" value="express" class="hidden"
                            x-model="deadlineOption">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded-full border-2 border-[#6D28D9] flex items-center justify-center">
                                <span class="w-2.5 h-2.5 rounded-full transition"
                                        :class="deadlineOption === 'express' ? 'bg-[#6D28D9]' : 'bg-transparent'"></span>
                            </span>
                            <span class="font-bold text-slate-900">Express (Prioritas Cepat)</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-[#D5FC55] text-neutral-900 text-[10px] font-bold">AKTIF</span>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Prioritas antrean teratas langsung dikerjakan oleh lead designer.
                    </p>
                </label>
            </div>

            <div x-show="deadlineOption === 'express'" x-transition x-cloak
                    class="mt-4 bg-violet-50 border border-violet-100 rounded-2xl p-5">

                @if ($expressFees->isNotEmpty())
                    <div class="bg-white border border-violet-100 rounded-xl p-4 mb-4">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Informasi Biaya Tambahan Express:</p>
                        <ul class="space-y-1 text-sm">
                            @foreach ($expressFees as $fee)
                                <li class="flex items-center justify-between rounded px-2 py-1 transition"
                                    :class="selectedFee && selectedFee.id === '{{ $fee->id }}' ? 'bg-violet-100 font-semibold' : ''">
                                    <span class="text-slate-600">{{ $fee->name }}</span>
                                    <span class="font-bold text-[#6D28D9]">+Rp {{ number_format($fee->fee, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="font-bold text-slate-900 mb-1">Pilih Target Deadline</p>
                <p class="text-sm text-slate-500 mb-4">
                    Pilih tanggal selesai. Biaya tambahan akan otomatis menyesuaikan durasi yang kamu pilih.
                </p>

                <div class="max-w-xs">
                    <label class="text-xs font-semibold text-slate-600">Tanggal Selesai:</label>
                    <input type="date" name="target_deadline" x-model="targetDeadline"
                            :min="minDate" :max="maxDate"
                            class="w-full mt-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30">
                    <!-- <p class="text-xs text-slate-400 mt-1">
                        Minimal <span x-text="2"></span> hari, maksimal <span x-text="maxDuration"></span> hari dari hari ini.
                    </p> -->
                    <p x-show="durationDays < 2" x-cloak class="text-xs text-red-500 mt-1">
                        Tanggal minimal H+2 dari hari ini.
                    </p>
                </div>

                <div x-show="selectedFee" x-cloak
                    class="mt-4 flex items-center justify-between bg-white border border-violet-200 rounded-xl px-4 py-3">
                <div>
                    <p class="text-xs text-slate-500">Biaya Tambahan Express</p>
                    <p class="font-bold text-slate-900" x-text="selectedFee ? selectedFee.name : '-'"></p>
                </div>
                <p class="text-2xl font-extrabold text-[#6D28D9]"
                    x-text="'+Rp ' + formatNumber(expressFee)"></p>
            </div>

            <p x-show="!selectedFee && deadlineOption === 'express'" x-cloak
                class="mt-4 text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                Tidak ada tarif express untuk durasi ini. Pilih tanggal lain.
            </p>
            </div>

            @error('target_deadline')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-8">
            <h3 class="flex items-center gap-2 font-bold text-lg text-slate-900 mb-4">
                <span class="w-7 h-7 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-sm font-bold">4</span>
                File & Tautan Referensi
            </h3>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center hover:border-[#6D28D9] transition cursor-pointer"
                        onclick="document.getElementById('reference-files').click()">
                    <div class="w-12 h-12 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-[#6D28D9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-[#6D28D9] mb-1">Upload File Referensi Desain</p>
                    <p class="text-sm text-slate-500">Drag & drop file moodboard, sketsa, logo lama, atau dokumen pendukung.</p>
                    <p class="text-xs text-slate-400 mt-2">Format didukung: JPG, PNG, PDF, ZIP (Maksimal 20MB per file)</p>
                    <input type="file" id="reference-files" name="reference_files[]" multiple class="hidden"
                        accept=".jpg,.jpeg,.png,.pdf,.zip"
                        onchange="handleReferenceFiles(this)">
                </div>

                @php
                    $oldFiles = collect($references)->where('type', 'file');
                @endphp

                @if ($oldFiles->count() > 0)
                    <div class="mt-4 space-y-2">
                        @foreach ($references as $i => $ref)
                            @if ($ref['type'] === 'file')
                                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-4 py-2"
                                    id="old-file-{{ $i }}">
                                    
                                    {{-- Bagian kiri bisa diklik --}}
                                    <a href="{{ \Storage::disk('r2')->temporaryUrl($ref['file_url'], now()->addHour()) }}"
                                    target="_blank" rel="noopener"
                                    class="flex items-center gap-3 min-w-0 flex-1 hover:opacity-80 transition">
                                        <svg class="w-5 h-5 text-[#6D28D9] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-700 truncate">{{ $ref['file_name'] }}</p>
                                            <p class="text-xs text-slate-400">
                                                {{ number_format(($ref['file_size'] ?? 0) / 1024 / 1024, 1) }} MB
                                            </p>
                                        </div>
                                    </a>

                                    <input type="hidden" name="existing_files[]" value="{{ $ref['file_url'] }}">

                                    <button type="button" onclick="openDeleteModal({{ $i }}, 'old-file-{{ $i }}')"
                                            class="text-slate-400 hover:text-red-500 transition shrink-0 ml-3"
                                            title="Hapus file">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div id="file-list" class="mt-2 space-y-2"></div>

                <p id="file-error" class="text-sm text-red-500 mt-2 hidden"></p>

                <div class="mt-5">
                    <label class="text-sm font-semibold text-slate-700">
                        Tautan Referensi Eksternal
                        <span class="text-xs text-slate-400 font-normal">(wajib jika tidak ada file)</span>
                    </label>
                    <div id="link-container" class="mt-2 space-y-2">
                        @error('reference_links')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror

                        @php
                            $hasOldLinks = collect($references)->where('type', 'link')->count() > 0;
                        @endphp

                        @if ($hasOldLinks)
                            @foreach ($references as $i => $ref)
                                @if ($ref['type'] === 'link')
                                    <div class="flex items-center gap-2" id="old-link-{{ $i }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400 shrink-0">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                        </svg>
                                        <input type="url" name="reference_links[]" value="{{ $ref['external_url'] }}"
                                                class="flex-1 min-w-0 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30">
                                        <button type="button" onclick="openDeleteModal({{ $i }}, 'old-link-{{ $i }}')"
                                                class="shrink-0 w-9 h-9 flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                                                title="Hapus tautan">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="w-5 h-5 text-slate-400 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                </svg>

                                <input type="url"
                                    name="reference_links[]"
                                    placeholder="https://drive/file/..."
                                    class="flex-1 min-w-0 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30">

                                {{-- X dari awal sudah tampil --}}
                                <button type="button"
                                    onclick="this.parentElement.remove()"
                                    class="shrink-0 w-9 h-9 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                                    title="Hapus tautan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                    <button type="button" onclick="addLinkInput()" class="text-[#6D28D9] text-sm font-semibold mt-2 hover:underline">
                        + Tambah Tautan
                    </button>
                </div>
            </div>
        </div>

        <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6 transform transition-all">
                <div class="flex items-center justify-center w-14 h-14 rounded-full bg-red-100 mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M4.93 19h14.14a2 2 0 001.74-2.97L13.74 4.03a2 2 0 00-3.48 0L3.19 16.03A2 2 0 004.93 19z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-slate-900 text-center mb-2">Hapus Referensi?</h3>
                <p class="text-sm text-slate-500 text-center mb-6">
                    File yang dihapus tidak akan disertakan dalam pesanan. Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()"
                            class="flex-1 px-4 py-2.5 rounded-xl border-2 border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" onclick="confirmDelete()"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold transition">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="flex items-center gap-2 font-bold text-lg text-slate-900 mb-4">
                <span class="w-7 h-7 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-sm font-bold">5</span>
                Catatan Brief Desain
            </h3>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <label class="font-semibold text-slate-700 mb-2 block">Deskripsi Kebutuhan Desain</label>
                <textarea name="brief_note" id="brief_note" rows="6" maxlength="1000"
                    x-model="briefNote"
                    placeholder="Warna, tipografi, nuansa brand, teks yang wajib dicantumkan, target audiens."
                    class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30"
                    oninput="updateCharCount()">{{ old('brief_note', $pemesananData['brief_note'] ?? '') }}</textarea>
                    @error('brief_note')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                <div class="flex justify-end mt-2">
                    <span class="text-xs text-slate-400"><span id="char-count">0</span> / 1000 Karakter</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl px-6 py-4 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between gap-4">
                {{-- KIRI: Tombol Kembali --}}
                <a href="{{ route('customer.katalog.detail', $product->id) }}"
                    class="shrink-0 px-5 py-3 rounded-xl border-2 border-[#6D28D9]/30 text-[#6D28D9] font-semibold hover:bg-violet-50 transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali
                </a>

                <div class="flex items-center gap-4 ml-auto">
                    {{-- Total Estimasi --}}
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">
                            Total Estimasi Sementara
                        </p>
                        <p class="text-3xl font-extrabold text-slate-900 leading-none">
                            Rp <span x-text="formatNumber(totalPrice)"></span>
                            <span class="text-xs font-semibold text-slate-400 align-middle">IDR</span>
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            Paket <span x-text="selectedTierName"></span>
                        </p>
                    </div>

                    <button type="submit"
                            :disabled="!isFormValid"
                            :class="!isFormValid ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#c5ec45]'"
                            class="shrink-0 inline-flex items-center gap-2 bg-[#D5FC55] text-neutral-900 font-bold px-6 py-3 rounded-xl transition shadow-sm">
                        Lanjutkan ke Ringkasan
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3 mt-3">
                <p class="text-xs text-slate-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Transaksi aman & terproteksi dengan Sistem Bersama ARTIV.
                </p>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    let selectedFiles = [];

    function handleReferenceFiles(input) {
        const errorEl = document.getElementById('file-error');
        errorEl.classList.add('hidden');
        errorEl.textContent = '';

        const MAX_SIZE = 20 * 1024 * 1024;
        const files = Array.from(input.files);
        const rejected = [];

        files.forEach(file => {
            if (file.size > MAX_SIZE) {
                rejected.push(file.name);
            } else {
                const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!exists) selectedFiles.push(file);
            }
        });

        if (rejected.length > 0) {
            errorEl.textContent = 'File berikut melebihi 20MB dan tidak diunggah: ' + rejected.join(', ');
            errorEl.classList.remove('hidden');
        }

        input.value = '';
        renderFileList();
    }

    function renderFileList() {
        const list = document.getElementById('file-list');
        list.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

            const row = document.createElement('div');
            row.className = 'flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-4 py-2';

            row.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-5 h-5 text-[#6D28D9] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">${file.name}</p>
                        <p class="text-xs text-slate-400">${sizeMB} MB</p>
                    </div>
                </div>
                <button type="button" onclick="removeFile(${index})"
                        class="text-slate-400 hover:text-red-500 transition shrink-0 ml-3"
                        title="Batalkan unggahan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;

            list.appendChild(row);
        });
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        renderFileList();
    }

    function addLinkInput() {
        const container = document.getElementById('link-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
            <input type="url" name="reference_links[]" placeholder="https://drive/file/..."
                    class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6D28D9]/30">
            <button type="button" onclick="this.parentElement.remove()"
                    class="text-slate-400 hover:text-red-500 transition shrink-0"
                    title="Hapus tautan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        container.appendChild(div);
    }

    function updateCharCount() {
        const textarea = document.getElementById('brief_note');
        if (textarea) {
            document.getElementById('char-count').textContent = textarea.value.length;
        }
    }

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateCharCount();
    });

    document.querySelector('form[action*="ringkasan"]').addEventListener('submit', function () {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        const input = document.getElementById('reference-files');
        if (input) input.files = dt.files;
    });

    let pendingDeleteIndex = null;
    let pendingDeleteElementId = null;

    function openDeleteModal(index, elementId) {
        pendingDeleteIndex = index;
        pendingDeleteElementId = elementId;
        
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        pendingDeleteIndex = null;
        pendingDeleteElementId = null;
        
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function confirmDelete() {
        if (pendingDeleteIndex === null) return;

        const index = pendingDeleteIndex;
        const elementId = pendingDeleteElementId;
        closeDeleteModal();

        fetch('{{ route('customer.pemesanan.hapus-referensi', $product->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ index: index })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById(elementId);
                if (el) {
                    el.style.transition = 'opacity 0.2s';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 200);
                }
            } else {
                alert('Gagal hapus. Coba lagi.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Gagal hapus. Coba lagi.');
        });
    }

    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
@endpush