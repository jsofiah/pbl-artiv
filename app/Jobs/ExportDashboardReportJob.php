<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExportDashboardReportJob implements ShouldQueue
{
    use Queueable;

    protected User $adminUser;
    protected ?string $startDate;
    protected ?string $endDate;

    public function __construct(User $adminUser, $startDate = null, $endDate = null)
    {
        $this->adminUser = $adminUser;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function handle(): void
    {
        $orders = Order::with(['customer', 'product', 'designer'])
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->get();

        $fileName = 'laporan-dashboard-' . now()->format('Y-m-d-His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Kode Order', 'Customer', 'Produk', 'Total Harga', 'Status', 'Tanggal']);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->order_code,
                $order->customer->full_name ?? '-',
                $order->product->name ?? '-',
                $order->total_price,
                $order->status,
                $order->created_at->format('Y-m-d H:i')
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put("exports/{$fileName}", $content);

        Log::info("Ekspor laporan berhasil diproses untuk Admin: {$this->adminUser->full_name}, File: {$fileName}");
    }
}