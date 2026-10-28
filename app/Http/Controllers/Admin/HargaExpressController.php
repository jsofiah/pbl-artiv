<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpressFee;
use Illuminate\Http\Request;

class HargaExpressController extends Controller
{
    public function index(Request $request)
    {
        $tab    = $request->query('tab', 'semua');
        $search = $request->query('q');
        $sort   = $request->query('sort', 'durasi_asc');

        $stats = [
            'semua'    => ExpressFee::count(),
            'aktif'    => ExpressFee::where('is_active', true)->count(),
            'nonaktif' => ExpressFee::where('is_active', false)->count(),
        ];

        $query = ExpressFee::query();

        if ($tab === 'aktif') {
            $query->where('is_active', true);
        } elseif ($tab === 'nonaktif') {
            $query->where('is_active', false);
        }

        if ($search) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        // Pengurutan bawaan otomatis berdasarkan angka jumlah hari (tercepat ke terlama)
        match ($sort) {
            'durasi_desc' => $query->orderByRaw("CAST(REGEXP_REPLACE(name, '[^0-9]', '', 'g') AS INTEGER) DESC"),
            'termurah'    => $query->orderBy('fee', 'asc'),
            'termahal'    => $query->orderBy('fee', 'desc'),
            'terbaru'     => $query->latest(),
            default       => $query->orderByRaw("CAST(REGEXP_REPLACE(name, '[^0-9]', '', 'g') AS INTEGER) ASC"),
        };

        $expressFees = $query->paginate(6)->withQueryString();

        return view('admin.harga-express', compact('expressFees', 'stats', 'tab', 'search', 'sort'));
    }

    public function store(Request $request)
    {
        $days = (int) preg_replace('/[^0-9]/', '', $request->input('name'));
        $formattedName = $days . ' Hari';

        $request->merge([
            'days_num'       => $days,
            'formatted_name' => $formattedName,
        ]);

        $request->validate([
            'days_num'       => 'required|integer|min:1',
            'formatted_name' => 'unique:express_fees,name',
            'fee'            => 'required|numeric|min:0',
            'is_active'      => 'nullable|boolean',
        ], [
            'days_num.required'     => 'Durasi hari wajib diisi.',
            'days_num.integer'      => 'Durasi hari harus berupa angka bulat.',
            'formatted_name.unique' => 'Harga Express untuk durasi ' . $formattedName . ' sudah ada.',
            'fee.required'          => 'Biaya tambahan wajib diisi.',
        ]);

        ExpressFee::create([
            'name'      => $formattedName,
            'fee'       => $request->fee,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Harga Express berhasil ditambahkan.');
    }

    public function update(Request $request, ExpressFee $hargaExpress)
    {
        $validated = $request->validate([
            'fee'       => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'fee.required' => 'Biaya tambahan wajib diisi.',
            'fee.numeric'  => 'Biaya tambahan harus berupa angka.',
        ]);

        $hargaExpress->update([
            'fee'       => $validated['fee'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Harga Express berhasil diperbarui.');
    }

    public function destroy(ExpressFee $hargaExpress)
    {
        if ($hargaExpress->orders()->exists()) {
            return back()->with('error', 'Gagal menghapus! Paket ini sudah pernah digunakan dalam transaksi pesanan.');
        }

        $hargaExpress->delete();

        return back()->with('success', 'Harga Express berhasil dihapus.');
    }
}