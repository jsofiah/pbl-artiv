<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi ARTIV</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
        }

        /* 1. Header Section (Halaman 1 Only) */
        .header-box {
            width: 100%;
            border-bottom: 2px solid #745bb8;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #745bb8;
            letter-spacing: 1px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            text-align: right;
            text-transform: uppercase;
        }

        .report-period {
            font-size: 10px;
            color: #6b7280;
            text-align: right;
            margin-top: 3px;
        }

        /* 2. KPI Summary Box (1 Kotak Utuh Terbagi 3 Kolom) */
        .summary-container {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-col {
            width: 33.33%;
            padding: 10px 16px;
            vertical-align: middle;
        }

        .summary-border {
            border-left: 1px solid #e5e7eb;
        }

        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-top: 3px;
        }

        /* 3. Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .th-data {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            text-align: left;
        }

        .td-data {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
            vertical-align: middle;
        }

        .tr-striped:nth-child(even) {
            background-color: #fcfcfd;
        }

        /* Helpers & Status Badges */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #ecfdf5;
            color: #047857;
        }

        .badge-pending {
            background-color: #fffbebf;
            color: #b45309;
        }

        .badge-process {
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        .badge-danger {
            background-color: #fef2f2;
            color: #b91c1c;
        }

        .footer-note {
            font-size: 8px;
            color: #9ca3af;
            margin-top: 12px;
        }
    </style>
</head>

<body>

    {{-- HEADER UTAMA (Hanya muncul di Halaman 1) --}}
    <table class="header-box">
        <tr>
            <td style="width: 50%;">
                <div class="brand-title">ARTIV</div>
                <div style="font-size: 9px; color: #6b7280; margin-top: 2px;">Platform Jasa Kreatif & Desain Profesional
                </div>
            </td>
            <td style="width: 50%;">
                <div class="report-title">Laporan Transaksi</div>
                <div class="report-period">
                    Periode: {{ $startDate->translatedFormat('d M Y') }} – {{ $endDate->translatedFormat('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- RINGKASAN KPI (1 Kotak Utuh Terbagi 3 Kolom) --}}
    <div class="summary-container">
        <table class="summary-table">
            <tr>
                <td class="summary-col">
                    <div class="summary-label">Total Transaksi</div>
                    <div class="summary-value">{{ number_format($orders->count()) }} Order</div>
                </td>
                <td class="summary-col summary-border">
                    <div class="summary-label">Total Omset Masuk</div>
                    <div class="summary-value" style="color: #745bb8;">
                        Rp
                        {{ number_format($orders->sum(fn($o) => $o->payments->where('status', \App\Models\Payment::STATUS_BERHASIL)->sum('amount')), 0, ',', '.') }}
                    </div>
                </td>
                <td class="summary-col summary-border">
                    <div class="summary-label">Order Selesai</div>
                    <div class="summary-value" style="color: #059669;">
                        {{ number_format($orders->where('status', 'completed')->count()) }} Order
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- TABEL DATA UTAMA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="th-data text-center">No</th>
                <th style="width: 18%;" class="th-data">Kode Order</th>
                <th style="width: 16%;" class="th-data">Tanggal</th>
                <th style="width: 22%;" class="th-data">Customer</th>
                <th style="width: 17%;" class="th-data">Layanan Jasa</th>
                <th style="width: 12%;" class="th-data text-right">Nominal</th>
                <th style="width: 10%;" class="th-data text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
                <tr class="tr-striped">
                    <td class="td-data text-center" style="color: #6b7280;">{{ $index + 1 }}</td>
                    <td class="td-data font-bold">#{{ $order->order_code }}</td>
                    <td class="td-data">
                        {{ $order->created_at ? $order->created_at->translatedFormat('d M Y H:i') : '-' }}</td>
                    <td class="td-data">
                        <div class="font-bold">{{ $order->customer->full_name ?? '-' }}</div>
                        <div style="font-size: 8px; color: #9ca3af;">{{ $order->customer->email ?? '' }}</div>
                    </td>
                    <td class="td-data">{{ $order->product->name ?? 'Jasa Kreatif' }}</td>
                    <td class="td-data text-right font-bold">
                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                    </td>
                    <td class="td-data text-center">
                        @php
                            $status = strtolower($order->status);
                            $badgeClass = match (true) {
                                str_contains($status, 'completed') => 'badge-success',
                                str_contains($status, 'pending') || str_contains($status, 'waiting') => 'badge-pending',
                                str_contains($status, 'progress') => 'badge-process',
                                str_contains($status, 'cancel') || str_contains($status, 'failed') => 'badge-danger',
                                default => 'badge-process',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ str_replace('_', ' ', $order->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="td-data text-center" style="padding: 20px; color: #9ca3af;">
                        Tidak ada data transaksi pada rentang tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        * Dokumen ini dicetak secara otomatis oleh Sistem Administrator ARTIV pada
        {{ now()->translatedFormat('d F Y H:i') }} WIB.
    </div>

</body>

</html>
