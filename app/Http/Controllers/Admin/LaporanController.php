<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Carbon\CarbonPeriod;
use App\Models\Payment;

class LaporanController extends Controller
{

    public function index()
    {
        // 1. Tentukan tanggal transaksi pertama & tanggal bulan ini
        $firstOrder = Order::oldest('created_at')->first();
        $startDate  = $firstOrder ? $firstOrder->created_at->copy()->startOfMonth() : now()->startOfMonth();
        $endDate    = now()->startOfMonth();

        // 2. Buat periode bulanan dari tanggal awal sampai bulan ini
        $period = CarbonPeriod::create($startDate, '1 month', $endDate);

        $monthsByYear = [];

        // 3. Balik urutan periode (array_reverse) agar bulan terbaru (Oktober) tampil paling atas
        foreach (array_reverse($period->toArray()) as $date) {
            $year = $date->format('Y');

            $monthsByYear[$year][] = [
                'name'       => $date->translatedFormat('F'),
                'start_date' => $date->copy()->startOfMonth()->format('Y-m-d'),
                'end_date'   => $date->copy()->endOfMonth()->format('Y-m-d'),
            ];
        }

        return view('admin.laporan', compact('monthsByYear'));
    }

    public function export(Request $request): response
    {
        $request->validate([
            'format'     => 'required|in:xlsx,pdf,csv',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);
        $startDate = Carbon::parse($request->string('start_date'))->startOfDay();
        $endDate   = Carbon::parse($request->string('end_date'))->endOfDay();
        $format    = $request->string('format')->value();

        /** @var Collection<int, Order> $orders */
        $orders = Order::with(['customer', 'product', 'payments'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        $fileName = 'laporan-transaksi-artiv-' . now()->format('Ymd-His');

        return match ($format) {
            'csv'   => $this->exportCsv($orders, $fileName),
            'pdf'   => $this->exportPdf($orders, $fileName, $startDate, $endDate),
            'xlsx'  => Excel::download(new OrdersExport($orders, $startDate, $endDate), "{$fileName}.xlsx"),
            default => Excel::download(new OrdersExport($orders, $startDate, $endDate), "{$fileName}.xlsx"),
        };
    }

    /**
     * Ekspor ke format CSV.
     *
     * @param Collection<int, Order> $orders
     */
    private function exportCsv(Collection $orders, string $fileName): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}.csv\"",
        ];

        $callback = function () use ($orders): void {
            $file = fopen('php://output', 'w');

            if ($file === false) {
                return;
            }

            // 1. Kalkulasi Data KPI (Disamakan dengan Dashboard & Excel)
            $totalTransaksi = $orders->count();
            $totalOmset     = (float) $orders->sum(function ($order) {
                return $order->payments->where('status', Payment::STATUS_BERHASIL)->sum('amount');
            });
            $orderSelesai   = $orders->where('status', 'completed')->count();

            // 2. Baris Header & Ringkasan KPI di Bagian Atas CSV
            fputcsv($file, ['LAPORAN TRANSAKSI ARTIV']);
            fputcsv($file, ['RINGKASAN PERFORMA']);
            fputcsv($file, ['Total Transaksi', $totalTransaksi . ' Order']);
            fputcsv($file, ['Total Omset (Di-ACC)', $totalOmset]);
            fputcsv($file, ['Order Selesai', $orderSelesai . ' Order']);
            fputcsv($file, []); // Baris kosong pemisah

            // 3. Header Kolom Tabel Utama
            fputcsv($file, [
                'No',
                'Kode Order',
                'Tanggal Transaksi',
                'Nama Customer',
                'Email Customer',
                'Layanan Jasa',
                'Nominal (Rp)',
                'Status Order',
            ]);

            // 4. Baris Data Transaksi
            $no = 1;
            foreach ($orders as $order) {
                fputcsv($file, [
                    $no++,
                    '#' . $order->order_code,
                    $order->created_at?->format('Y-m-d H:i') ?? '-',
                    $order->customer?->full_name ?? '-',
                    $order->customer?->email ?? '-',
                    $order->product?->name ?? 'Jasa Kreatif',
                    $order->total_price,
                    str_replace('_', ' ', strtoupper($order->status)),
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, "{$fileName}.csv", $headers);
    }

    /**
     * Ekspor ke format PDF.
     *
     * @param Collection<int, Order> $orders
     */
    private function exportPdf(Collection $orders, string $fileName, Carbon $startDate, Carbon $endDate): StreamedResponse|RedirectResponse
    {
        if (class_exists(Pdf::class)) {
            /** @var \Barryvdh\DomPDF\PDF $pdf */
            $pdf = Pdf::loadView('admin.laporan.pdf', compact('orders', 'startDate', 'endDate'));
            return response()->streamDownload(fn() => print($pdf->output()), "{$fileName}.pdf");
        }

        return redirect()->back()->with('error', 'Package DomPDF belum terinstall.');
    }
}
