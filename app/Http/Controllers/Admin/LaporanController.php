<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class LaporanController extends Controller
{
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
     * Ekspor langsung ke format CSV (Stream Download).
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

            // Header Kolom CSV
            fputcsv($file, [
                'Kode Order',
                'Tanggal Transaksi',
                'Nama Customer',
                'Email Customer',
                'Layanan Jasa',
                'Nominal (Rp)',
                'Status Order',
            ]);

            // Baris Data Transaksi
            foreach ($orders as $order) {
                fputcsv($file, [
                    '#' . $order->order_code,
                    $order->created_at?->format('Y-m-d H:i') ?? '-',
                    $order->customer?->full_name ?? '-',
                    $order->customer?->email ?? '-',
                    $order->product?->name ?? 'Jasa Kreatif',
                    $order->total_price,
                    str_replace('_', ' ', $order->status),
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
