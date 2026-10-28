<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Services\R2StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KatalogController extends Controller
{
    private const PER_HALAMAN = 6;

    public function index(Request $request): View
    {
        $status    = $request->query('status', 'semua');
        $urut      = $request->query('urut', 'terbaru');
        $kataKunci = trim((string) $request->query('q', ''));

        $base = Product::with(['tiers' => fn ($q) => $q->orderBy('price')]);

        $jumlah = [
            'semua'    => (clone $base)->count(),
            'aktif'    => (clone $base)->where('is_active', true)->count(),
            'nonaktif' => (clone $base)->where('is_active', false)->count(),
        ];

        $query = (clone $base)
            ->when($status === 'aktif',    fn ($q) => $q->where('is_active', true))
            ->when($status === 'nonaktif', fn ($q) => $q->where('is_active', false));

        if ($kataKunci !== '') {
            $query->where(function ($sub) use ($kataKunci) {
                $sub->where('name', 'ilike', "%{$kataKunci}%")
                    ->orWhere('description', 'ilike', "%{$kataKunci}%");
            });
        }

        $query = match ($urut) {
            'terlama' => $query->oldest(),
            'nama'    => $query->orderBy('name'),
            default   => $query->latest(),
        };

        $katalog = $query->paginate(self::PER_HALAMAN)->withQueryString();

        return view('admin.katalog.index', [
            'katalog'           => $katalog,
            'jumlah'            => $jumlah,
            'status'            => $status,
            'urut'              => $urut,
            'kataKunci'         => $kataKunci,
            'badgeKatalog'      => $jumlah['semua'],
            'badgeHargaExpress' => 6,
        ]);
    }

    public function create(): View
    {
        return view('admin.katalog.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        /** @var R2StorageService $storage */
        $storage = app(R2StorageService::class);

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $storage->upload(
                $request->file('thumbnail'),
                'products',
                R2StorageService::DISK_PUBLIC
            );
        }

        $product = Product::create([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'thumbnail_url' => $thumbnailUrl,
            'price'         => $validated['price'] ?? null,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        if (!empty($validated['tiers'])) {
            foreach ($validated['tiers'] as $tier) {
                if (empty($tier['name']) && empty($tier['price'])) continue;

                $tierThumbUrl = null;
                if (isset($tier['thumbnail']) && $tier['thumbnail'] instanceof \Illuminate\Http\UploadedFile) {
                    $tierThumbUrl = $storage->upload(
                        $tier['thumbnail'],
                        'product_tiers',
                        R2StorageService::DISK_PUBLIC
                    );
                }

                $product->tiers()->create([
                    'name'          => $tier['name'],
                    'price'         => $tier['price'],
                    'description'   => $tier['description'] ?? null,
                    'thumbnail_url' => $tierThumbUrl,
                    'is_active'     => true,
                ]);
            }
        }

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'create_katalog',
            'target_type' => 'product',
            'target_id'   => $product->id,
            'description' => "Menambahkan katalog baru: \"{$product->name}\"",
        ]);

        return redirect()
            ->route('admin.katalog.index')
            ->with('status', "Katalog \"{$product->name}\" berhasil ditambahkan.");
    }

    public function edit(string $product): View
    {
        $product = Product::with(['tiers' => fn ($q) => $q->orderBy('price')])
            ->findOrFail($product);

        return view('admin.katalog.edit', compact('product'));
    }

    public function update(Request $request, string $product): RedirectResponse
    {
        $productModel = Product::findOrFail($product);
        $validated = $this->validateProduct($request);

        /** @var R2StorageService $storage */
        $storage = app(R2StorageService::class);

        $oldName = $productModel->name;

        if ($request->hasFile('thumbnail')) {
            if ($productModel->thumbnail_url) {
                $storage->delete($productModel->thumbnail_url, R2StorageService::DISK_PUBLIC);
            }

            $validated['thumbnail_url'] = $storage->upload(
                $request->file('thumbnail'),
                'products',
                R2StorageService::DISK_PUBLIC
            );
        }

        $productModel->update([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? $productModel->thumbnail_url,
            'price'         => $validated['price'] ?? null,
            'is_active'     => $request->boolean('is_active'),
        ]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'update_katalog',
            'target_type' => 'product',
            'target_id'   => $productModel->id,
            'description' => "Memperbarui katalog: \"{$oldName}\" → \"{$productModel->name}\"",
        ]);

        return redirect()
            ->route('admin.katalog.index')
            ->with('status', "Katalog \"{$productModel->name}\" berhasil diperbarui.");
    }

    public function destroy(string $product): RedirectResponse
    {
        $productModel = Product::with('tiers')->findOrFail($product);
        $nama = $productModel->name;

        /** @var R2StorageService $storage */
        $storage = app(R2StorageService::class);

        if ($productModel->thumbnail_url) {
            $storage->delete($productModel->thumbnail_url, R2StorageService::DISK_PUBLIC);
        }

        foreach ($productModel->tiers as $tier) {
            if ($tier->thumbnail_url) {
                $storage->delete($tier->thumbnail_url, R2StorageService::DISK_PUBLIC);
            }
        }

        $productModel->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'delete_katalog',
            'target_type' => 'product',
            'target_id'   => $productModel->id,
            'description' => "Menghapus katalog: \"{$nama}\"",
        ]);

        return redirect()
            ->route('admin.katalog.index')
            ->with('status', "Katalog \"{$nama}\" berhasil dihapus.");
    }


    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'thumbnail'   => ['nullable', 'image', 'max:10240'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],

            'tiers'               => ['nullable', 'array'],
            'tiers.*.name'        => ['required_with:tiers.*.price', 'string', 'max:100'],
            'tiers.*.price'       => ['required_with:tiers.*.name', 'numeric', 'min:0'],
            'tiers.*.description' => ['nullable', 'string', 'max:1000'],
            'tiers.*.thumbnail'   => ['nullable', 'image', 'max:10240'],
        ], [
            'name.required'   => 'Nama katalog wajib diisi.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar.',
            'thumbnail.max'   => 'Thumbnail maksimal 10 MB.',
            'tiers.*.name.required_with'  => 'Nama tier wajib jika harga diisi.',
            'tiers.*.price.required_with' => 'Harga tier wajib jika nama diisi.',
            'tiers.*.thumbnail.image'     => 'Thumbnail tier harus gambar.',
        ]);
    }
}