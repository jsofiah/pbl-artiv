<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Pekerjaan</title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 12px; color: #0f172a; margin: 32px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        p.meta { color: #64748b; margin: 0 0 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        td.r { text-align: right; }
    </style>
</head>
<body onload="window.print()">
    <h1>Laporan Riwayat Pekerjaan</h1>
    <p class="meta">{{ auth()->user()->full_name }} · dicetak {{ now()->translatedFormat('d F Y') }}</p>

    <table>
        <thead>
            <tr><th>Kode</th><th>Layanan</th><th>Customer</th><th>Selesai</th><th>Kompensasi</th><th>Rating</th></tr>
        </thead>
        <tbody>
        @foreach ($orders as $o)
            <tr>
                <td>{{ $o->order_code }}</td>
                <td>{{ $o->product->name ?? '-' }}</td>
                <td>{{ $o->customer->full_name ?? '-' }}</td>
                <td>{{ optional($o->completed_at ?? $o->updated_at)->format('d/m/Y') }}</td>
                <td class="r">Rp {{ number_format($o->split->designer_earning ?? 0, 0, ',', '.') }}</td>
                <td>{{ $o->review->rating ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th class="r">Rp {{ number_format($orders->sum(fn ($o) => $o->split->designer_earning ?? 0), 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>