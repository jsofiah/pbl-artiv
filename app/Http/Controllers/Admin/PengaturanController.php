<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlacklistKeyword;
use App\Models\RevisionFee;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index(Request $request)
    {
        $query = BlacklistKeyword::query();

        if ($request->filled('q')) {
            $search = strtolower(trim($request->q));
            $query->whereRaw('LOWER(keyword) LIKE ?', ["%{$search}%"]);
        }

        if ($request->filled('type') && in_array($request->type, ['link', 'phone', 'email', 'word'])) {
            $query->where('type', $request->type);
        }

        $keywords = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $revisionFee = RevisionFee::first();
        if (!$revisionFee) {
            $revisionFee = RevisionFee::create([
                'name'      => 'Revisi Tambahan',
                'fee'       => 15000,
                'is_active' => true,
            ]);
        }

        $totalKeywords = BlacklistKeyword::count();

        return view('admin.pengaturan', compact('keywords', 'revisionFee', 'totalKeywords'));
    }

    public function storeBlacklist(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:100|unique:blacklist_keywords,keyword',
            'type'    => 'required|in:link,phone,email,word',
        ], [
            'keyword.required' => 'Kata kunci wajib diisi.',
            'keyword.unique'   => 'Kata kunci tersebut sudah ada di daftar blacklist.',
            'type.required'    => 'Tipe kata kunci wajib dipilih.',
            'type.in'          => 'Tipe kata kunci tidak valid.',
        ]);

        BlacklistKeyword::create([
            'keyword'   => trim($validated['keyword']),
            'type'      => $validated['type'],
            'is_active' => true,
        ]);

        cache()->forget('blacklist_keywords_v2');

        return redirect()->route('admin.pengaturan')->with('success', 'Keyword blacklist berhasil ditambahkan.');
    }

    public function updateBlacklist(Request $request, BlacklistKeyword $keyword)
    {
        $validated = $request->validate([
            'keyword'   => 'required|string|max:100|unique:blacklist_keywords,keyword,' . $keyword->id,
            'type'      => 'required|in:link,phone,email,word',
            'is_active' => 'nullable|boolean',
        ], [
            'keyword.required' => 'Kata kunci wajib diisi.',
            'keyword.unique'   => 'Kata kunci tersebut sudah terdaftar.',
            'type.required'    => 'Tipe kata kunci wajib dipilih.',
            'type.in'          => 'Tipe kata kunci tidak valid.',
        ]);

        $keyword->update([
            'keyword'   => trim($validated['keyword']),
            'type'      => $validated['type'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        cache()->forget('blacklist_keywords_v2');

        return redirect()->route('admin.pengaturan')->with('success', 'Keyword blacklist berhasil diperbarui.');
    }

    public function destroyBlacklist(BlacklistKeyword $keyword)
    {
        $keywordName = $keyword->keyword;
        $keyword->delete();

        cache()->forget('blacklist_keywords_v2');

        return redirect()->route('admin.pengaturan')->with('success', "Keyword \"{$keywordName}\" berhasil dihapus.");
    }

    public function updateRevisionFee(Request $request)
    {
        $validated = $request->validate([
            'fee'       => 'required|numeric|min:0',
            'name'      => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'fee.required' => 'Nominal harga revisi wajib diisi.',
            'fee.numeric'  => 'Nominal harga revisi harus berupa angka.',
            'fee.min'      => 'Nominal harga revisi tidak boleh bernilai negatif.',
        ]);

        $revisionFee = RevisionFee::first();
        if ($revisionFee) {
            $revisionFee->update([
                'fee'       => $validated['fee'],
                'name'      => $validated['name'] ?? $revisionFee->name ?? 'Revisi Tambahan',
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            ]);
        } else {
            RevisionFee::create([
                'fee'       => $validated['fee'],
                'name'      => $validated['name'] ?? 'Revisi Tambahan',
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.pengaturan')->with('success', 'Pengaturan harga revisi berhasil diperbarui.');
    }
}
