<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Payment;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    /**
     * @param Collection<int, Order> $orders
     */
    public function __construct(
        protected Collection $orders,
        protected Carbon $startDate,
        protected Carbon $endDate
    ) {}

    public function title(): string
    {
        return 'Laporan Transaksi';
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    /**
     * Header & Ringkasan KPI Menyamping 3 Kotak
     */
    public function headings(): array
    {
        $totalTransaksi = $this->orders->count();
        $totalOmset = (float) $this->orders->sum(function ($order) {
            return $order->payments->where('status', Payment::STATUS_BERHASIL)->sum('amount');
        });
        $orderSelesai   = $this->orders->where('status', 'completed')->count();

        return [
            ['LAPORAN TRANSAKSI ARTIV'],
            ['Periode: ' . $this->startDate->translatedFormat('d M Y') . ' - ' . $this->endDate->translatedFormat('d M Y')],
            [], // Baris 3: Kosong
            ['RINGKASAN PERFORMA'], // Baris 4: Judul Section KPI
            ['Total Transaksi', '', 'Total Omset (Di-ACC)', '', '', 'Order Selesai', '', ''], // Baris 5: Labels
            [$totalTransaksi . ' Order', '', $totalOmset, '', '', $orderSelesai . ' Order', '', ''], // Baris 6: Values
            [], // Baris 7: Kosong
            [ // Baris 8: Header Tabel Utama
                'No',
                'Kode Order',
                'Tanggal Transaksi',
                'Nama Customer',
                'Email Customer',
                'Layanan Jasa',
                'Nominal (Rp)',
                'Status Order',
            ],
        ];
    }

    /**
     * Mapping Baris Data Transaksi
     *
     * @param Order $order
     */
    public function map($order): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            '#' . $order->order_code,
            $order->created_at ? $order->created_at->format('Y-m-d H:i') : '-',
            $order->customer?->full_name ?? '-',
            $order->customer?->email ?? '-',
            $order->product?->name ?? 'Jasa Kreatif',
            (float) $order->total_price,
            str_replace('_', ' ', strtoupper($order->status)),
        ];
    }

    /**
     * Format Angka Rupiah
     */
    public function columnFormats(): array
    {
        return [
            'C6' => '"Rp "#,##0', // Format Rupiah di KPI Omset (Cell C6)
            'G'  => '"Rp "#,##0', // Format Rupiah di Kolom Nominal Data Tabel
        ];
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet): array
    {
        // Merge Header Judul
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A4:H4');

        // Merge Box KPI 1 (Total Transaksi)
        $sheet->mergeCells('A5:B5');
        $sheet->mergeCells('A6:B6');

        // Merge Box KPI 2 (Total Omset)
        $sheet->mergeCells('C5:E5');
        $sheet->mergeCells('C6:E6');

        // Merge Box KPI 3 (Order Selesai)
        $sheet->mergeCells('F5:H5');
        $sheet->mergeCells('F6:H6');

        $totalRows = $this->orders->count() + 8; // Data dimulai dari baris 9

        return [
            // Title Header
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '745BB8']],
            ],
            2 => [
                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '6B7280']],
            ],

            // Section Header KPI
            4 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '374151']],
            ],

            // Labels KPI (Baris 5) -> Force Rata Kiri
            5 => [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],

            // Values KPI (Baris 6) -> Force Rata Kiri (Mencegah angka omset melompat ke kanan)
            6 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],

            // Warna Khusus Angka KPI
            'A6' => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '111827']]],
            'C6' => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '745BB8']]], // Purple Omset
            'F6' => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '059669']]], // Green Completed

            // Box Border & Fill Background untuk 3 Kotak KPI
            'A5:B6' => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ],
            'C5:E6' => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ],
            'F5:H6' => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ],

            // Header Tabel Utama (Baris 8)
            8 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '745BB8'],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            // Alignment Data Tabel
            "A8:A{$totalRows}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            "B8:B{$totalRows}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            "C8:C{$totalRows}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            "G8:G{$totalRows}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            "H8:H{$totalRows}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],

            // Border Tabel Utama
            "A8:H{$totalRows}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ],
        ];
    }
}
