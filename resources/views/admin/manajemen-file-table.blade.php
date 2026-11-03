{{-- Tabel Berkas --}}
<div class="overflow-x-auto rounded-xl border border-gray-100">
    <table class="w-full text-left border-collapse table-fixed">
        <thead>
            <tr class="bg-gray-50/80 text-[11px] font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100">
                <th class="py-3 px-4 w-12 text-center">NO</th>
                <th class="py-3 px-4 w-40">ORDER ID</th>
                <th class="py-3 px-4 w-auto">NAMA FILE</th>
                <th class="py-3 px-4 w-40">PENGUNGGAH</th>
                <th class="py-3 px-4 w-36">DIUNGGAH</th>
                <th class="py-3 px-4 w-36 text-center">AKSI</th>
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
                        <a href="{{ route('admin.monitoring', ['q' => $order?->order_code]) }}" class="hover:text-[#745BB8] hover:underline">
                            #{{ $order?->order_code ?? '-' }}
                        </a>
                    </td>

                    {{-- Nama File & Subtitle Size --}}
                    <td class="py-3.5 px-4 min-w-0">
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-gray-800 truncate" title="{{ $fileName }}">
                                {{ $fileName }}
                            </div>
                            <div class="text-[11px] text-gray-400 truncate">
                                {{ $fileSize }}
                            </div>
                        </div>
                    </td>

                    {{-- Pengunggah --}}
                    <td class="py-3.5 px-4 text-xs sm:text-sm font-medium text-gray-700 truncate">
                        {{ $uploader }}
                    </td>

                    {{-- Tanggal Upload --}}
                    <td class="py-3.5 px-4 text-gray-500 text-xs whitespace-nowrap">
                        {{ $item->created_at ? $item->created_at->translatedFormat('d/m/Y H:i') . ' WIB' : '-' }}
                    </td>

                    {{-- Tombol Aksi --}}
                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5 justify-center">
                            {{-- Lihat --}}
                            <button type="button" 
                                    @click="openPreview('{{ route('admin.manajemen-file.preview', ['type' => $tab, 'id' => $item->id]) }}', '{{ route('admin.manajemen-file.download', ['type' => $tab, 'id' => $item->id]) }}', {{ \Illuminate\Support\Js::from($fileName) }})"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg transition">
                                Lihat
                            </button>

                            {{-- Hapus --}}
                            <button type="button" 
                                    @click="deleteModalOpen = true; deleteUrl = '{{ route('admin.manajemen-file.destroy', ['type' => $tab, 'id' => $item->id]) }}'"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs rounded-lg transition">
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

<div class="mt-2 pt-4 border-t border-slate-100 flex items-center justify-between gap-3 flex-wrap px-4 pb-4">
    <p class="text-sm text-slate-500">
        Menampilkan {{ $files->firstItem() ?? 0 }} - {{ $files->lastItem() ?? 0 }} dari {{ $files->total() }} file
    </p>

    @if($files->hasPages())
        <div class="flex items-center gap-2">
            <a href="{{ $files->previousPageUrl() ?? '#' }}"
                class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $files->onFirstPage() ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' }} transition">
                &lt; Sebelumnya
            </a>

            @for ($i = 1; $i <= $files->lastPage(); $i++)
                <a href="{{ $files->url($i) }}"
                    class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-semibold transition
                            {{ $i === $files->currentPage()
                                ? 'bg-[#6D28D9] text-white shadow-sm'
                                : 'border border-slate-200 bg-white text-slate-600 hover:border-[#6D28D9] hover:text-[#6D28D9]' }}">
                    {{ $i }}
                </a>
            @endfor

            <a href="{{ $files->nextPageUrl() ?? '#' }}"
                class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white {{ $files->hasMorePages() ? 'text-slate-700 hover:border-[#6D28D9] hover:text-[#6D28D9]' : 'text-slate-300 pointer-events-none' }} transition">
                Selanjutnya &gt;
            </a>
        </div>
    @endif
</div>