@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Inisialisasi Alpine.js State Khusus Export --}}
    <div x-data="{ exportModal: false, exportFormat: 'xlsx' }">

        {{-- 6 Card Statistik Top Bar (Operasional Detail) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">

            {{-- Card 1: Butuh Verifikasi --}}
            <div class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
                <p class="text-[11px] font-medium text-gray-500">Butuh Verifikasi</p>
                <h3 class="text-base font-bold text-black mt-1">{{ number_format($stats['pending_payments'] ?? 0) }}
                </h3>
                <p class="text-[10px] text-gray-500 font-medium mt-0.5">Bukti transfer baru</p>
            </div>

            {{-- Card 2: Sudah Dibayar --}}
            <div class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-medium text-gray-500">Sudah Dibayar</p>
                <h3 class="text-base font-bold text-black mt-1">{{ number_format($stats['paid_orders_monthly'] ?? 0) }}
                </h3>
                <p class="text-[10px] text-gray-500 font-medium mt-0.5">Lunas bulan ini</p>
            </div>

            {{-- Card 3: Menunggu Bayar --}}
            <div class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-medium text-gray-500">Menunggu Bayar</p>
                <h3 class="text-base font-bold text-black mt-1">{{ number_format($stats['unpaid_active'] ?? 0) }}</h3>
                <p class="text-[10px] text-gray-500 font-medium mt-0.5">&lt; 24 jam</p>
            </div>

            {{-- Card 4: Lewat 24 Jam --}}
            <div class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
                <p class="text-[11px] font-medium text-gray-500">Lewat 24 Jam</p>
                <h3 class="text-base font-bold text-black mt-1">{{ number_format($stats['unpaid_expired'] ?? 0) }}</h3>
                <p class="text-[10px] text-gray-500 font-medium mt-0.5">Kedaluwarsa</p>
            </div>

            {{-- Card 5: Cari Desainer --}}
            <div class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-medium text-gray-500">Cari Desainer</p>
                <h3 class="text-base font-bold text-black mt-1">
                    {{ number_format($stats['waiting_designer_orders'] ?? 0) }}</h3>
                <p class="text-[10px] text-gray-500 font-medium mt-0.5">Belum ditugaskan</p>
            </div>

            {{-- Card 6: Sedang Dikerjakan --}}
            <div class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-medium text-gray-500">Sedang Dikerjakan</p>
                <h3 class="text-base font-bold text-black mt-1">{{ number_format($stats['in_progress_orders'] ?? 0) }}
                </h3>
                <p class="text-[10px] text-gray-500 font-medium mt-0.5">Proses/Revisi</p>
            </div>

        </div>

        {{-- Bagian Tengah: Grafik Pendapatan (2/3) & Performa Bulanan (1/3) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Left (2/3): Grafik Pendapatan Harian --}}
            <div
                class="lg:col-span-2 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between h-full">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 mb-2">Statistik Pendapatan Harian</h2>
                </div>
                <div id="chart-statistik" class="w-full flex-1 flex flex-col justify-end"></div>
            </div>

            {{-- Right (1/3): Rekapitulasi Performa Bulan Berjalan --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    {{-- Header Kartu: Judul Dinamis Nama Bulan & Link "Lihat Semua" --}}
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <h3 class="text-sm font-bold text-gray-900">
                            {{ now()->translatedFormat('F Y') }}
                        </h3>
                        <a href="{{ route('admin.laporan') }}"
                            class="text-xs font-semibold text-purple-600 hover:text-purple-700 flex items-center gap-1 transition">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="space-y-4">
                        {{-- Sub-Section 1: Pendapatan Bulan Ini --}}
                        <div>
                            <p class="text-[11px] font-medium text-gray-500 mb-1">Total omset</p>
                            <h4 class="text-2xl font-extrabold text-black">
                                Rp {{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', '.') }}
                            </h4>
                        </div>

                        {{-- Separator Line --}}
                        <div class="border-t border-gray-100 pt-4">
                            {{-- Sub-Section 2: Order Selesai Bulan Ini --}}
                            <p class="text-[11px] font-medium text-gray-500 mb-1">Order Selesai</p>
                            <div class="flex items-baseline gap-1.5">
                                <h4 class="text-2xl font-extrabold text-black">
                                    {{ number_format($stats['completed_orders_monthly'] ?? 0) }}
                                </h4>
                                <span class="text-xs font-semibold text-gray-600">Pesanan</span>
                            </div>
                        </div>

                        {{-- Separator Line --}}
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-[11px] font-medium text-gray-500">Rata-rata ulasan</p>
                            <div class="flex items-center gap-1 mt-1">
                                <h4 class="text-2xl font-extrabold text-black">
                                    {{ $stats['designer_avg_rating'] ?? '0.0' }}
                                </h4>
                                <svg class="w-5 h-5 text-amber-400 fill-current shrink-0 mb-0.5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        </div>

                        {{-- Separator Line & Tombol Trigger Ekspor Laporan --}}
                        <div class="border-t border-gray-100 pt-4 relative" x-data="{ dropdownOpen: false }">
                            <button type="button" @click="dropdownOpen = !dropdownOpen"
                                class="w-full py-2.5 px-4 text-xs font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl transition shadow-sm flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Ekspor Laporan
                                </span>
                                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
                                    :class="dropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Menu Popover Dropdown (Kebawah) --}}
                            <div x-show="dropdownOpen" x-cloak @click.away="dropdownOpen = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 left-0 top-full mt-2 z-30 bg-white rounded-2xl border border-gray-100 shadow-xl p-1.5 space-y-1">

                                {{-- Opsi 1: Excel --}}
                                <button type="button"
                                    @click="exportFormat = 'xlsx'; exportModal = true; dropdownOpen = false"
                                    class="w-full text-left px-3 py-2 text-xs font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700 rounded-xl transition flex items-center gap-2.5">
                                    <div class="font-bold text-gray-900">Excel (.xlsx)</div>
                                </button>

                                {{-- Opsi 2: PDF --}}
                                <button type="button"
                                    @click="exportFormat = 'pdf'; exportModal = true; dropdownOpen = false"
                                    class="w-full text-left px-3 py-2 text-xs font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700 rounded-xl transition flex items-center gap-2.5">
                                    <div class="font-bold text-gray-900">PDF (.pdf)</div>
                                </button>

                                {{-- Opsi 3: CSV --}}
                                <button type="button"
                                    @click="exportFormat = 'csv'; exportModal = true; dropdownOpen = false"
                                    class="w-full text-left px-3 py-2 text-xs font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700 rounded-xl transition flex items-center gap-2.5">
                                    <div class="font-bold text-gray-900">CSV (.csv)</div>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Bagian Kategori Jasa --}}
        <div class="w-full bg-white p-5 rounded-2xl border border-gray-100 shadow-sm mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Jumlah Order Terjual per Kategori Jasa</h2>
                </div>
            </div>
            <div id="chart-kategori" class="w-full min-h-[280px]"></div>
        </div>

        {{-- Bagian Bawah: Tabel Transaksi Terbaru --}}
        <div
            class="w-full bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Ringkasan Order & Transaksi Terbaru</h2>
                <a href="{{ route('admin.monitoring') }}"
                    class="text-xs font-semibold text-purple-600 hover:text-purple-700">Lihat Semua</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead
                        class="bg-gray-50/80 text-gray-700 font-semibold uppercase text-[11px] border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">Kode Order</th>
                            <th class="px-5 py-3">Customer & Layanan</th>
                            <th class="px-5 py-3">Nominal</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-3.5 font-bold text-gray-900">#{{ $order->order_code }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-gray-900">
                                        {{ $order->product->name ?? 'Jasa Kreatif' }}
                                    </div>
                                    <div class="text-[11px] text-gray-400">{{ $order->customer->full_name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-gray-900">Rp
                                    {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-600 border border-purple-100 capitalize">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-left">
    <a href="{{ route('admin.monitoring') }}"
        class="text-xs font-semibold text-purple-600 hover:underline">Detail</a>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-400">Belum ada transaksi terbaru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-gray-50/50 border-t border-gray-100 text-right">
                <span class="text-[11px] text-gray-400">Menampilkan 5 transaksi terakhir</span>
            </div>
        </div>

        {{-- MODAL EXPORT LAPORAN --}}
        <div x-show="exportModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative" @click.away="exportModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-base font-bold text-gray-900">Ekspor Laporan Transaksi</h3>
                    <button @click="exportModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <form action="{{ route('admin.laporan.export') }}" 
      method="POST"
      @submit.prevent="
          exportModal = false;
          $dispatch('toast', { message: 'Menyiapkan file', type: 'info' });

          try {
              const response = await fetch($el.action, {
                  method: 'POST',
                  body: new FormData($el)
              });

              if (!response.ok) throw new Error();

              const blob = await response.blob();
              const url = window.URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = url;
              a.download = `laporan-transaksi.${exportFormat}`;
              document.body.appendChild(a);
              a.click();
              a.remove();
              window.URL.revokeObjectURL(url);

              $dispatch('toast', { message: 'Laporan berhasil diunduh', type: 'success' });
          } catch (err) {
              $dispatch('toast', { message: 'Gagal mengunduh laporan.', type: 'error' });
          }
      ">
    @csrf
    <input type="hidden" name="format" :value="exportFormat">

    <p class="text-xs text-gray-500 mb-4">
        Format dipilih: <span class="font-bold text-purple-600 uppercase" x-text="exportFormat"></span>.
        Tentukan rentang tanggal laporan yang akan diunduh.
    </p>

    <div class="space-y-3 mb-6">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" required
                class="w-full text-xs rounded-xl border border-gray-200 py-2 px-3 focus:ring-purple-500/20 focus:border-purple-600">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" required
                class="w-full text-xs rounded-xl border border-gray-200 py-2 px-3 focus:ring-purple-500/20 focus:border-purple-600">
        </div>
    </div>

    <div class="flex items-center justify-end gap-2">
        <button type="button" @click="exportModal = false"
            class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">Batal</button>
        <button type="submit"
            class="px-4 py-2 text-xs font-semibold text-white bg-[#745BB8] hover:bg-[#5C469C] rounded-xl transition shadow-sm">
            Mulai Ekspor
        </button>
    </div>
</form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Chart Pendapatan Harian (Area Chart)
            var optionsPendapatan = {
                series: [{
                    name: 'Pendapatan Harian',
                    data: @json($chartRevenues ?? [])
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },
                colors: ['#745BB8'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 95, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: @json($chartDates ?? []),
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                },
                grid: {
                    strokeDashArray: 4
                }
            };

            var chartPendapatan = new ApexCharts(document.querySelector("#chart-statistik"), optionsPendapatan);
            chartPendapatan.render();

            // 2. Chart Distribusi Kategori (Jumlah Terjual & Truncate Nama Panjang)
            var optionsKategori = {
                series: [{
                    name: 'Total Order',
                    data: @json($categoryCounts ?? [])
                }],
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '40%',
                        borderRadius: 6,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: ['#745BB8'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val + " Order";
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '11px',
                        colors: ['#4B5563']
                    }
                },
                xaxis: {
                    categories: @json($categoryNames ?? []),
                    labels: {
                        style: {
                            fontSize: '11px'
                        },
                        formatter: function(val) {
                            if (typeof val === 'string' && val.length > 15) {
                                return val.substring(0, 15) + '...';
                            }
                            return val;
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return Math.floor(val);
                        }
                    }
                },
                tooltip: {
                    x: {
                        formatter: function(val, opts) {
                            return opts.w.globals.labels[opts.dataPointIndex] || val;
                        }
                    },
                    y: {
                        formatter: function(val) {
                            return val + " Order";
                        }
                    }
                },
                grid: {
                    strokeDashArray: 4
                }
            };

            var chartKategori = new ApexCharts(document.querySelector("#chart-kategori"), optionsKategori);
            chartKategori.render();
        });
    </script>
@endpush
