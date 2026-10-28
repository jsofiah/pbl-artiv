<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductTier;
use App\Services\R2StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductTierController extends Controller
{
    public function index(string $product): View
    {
        $product = Product::with(['tiers' => fn ($q) => $q->orderBy('price')])
            ->findOrFail($product);

        return view('admin.katalog.tiers', compact('product'));
    }

    public function store(Request $request, string $product): RedirectResponse
    {
        $product = Product::findOrFail($product);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'price'       => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'thumbnail'   => ['nullable', 'image', 'max:10240'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        /** @var R2StorageService $storage */
        $storage = app(R2StorageService::class);

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $storage->upload(
                $request->file('thumbnail'),
                'product_tiers',
                R2StorageService::DISK_PUBLIC
            );
        }

        $tier = $product->tiers()->create([
            'name'          => $validated['name'],
            'price'         => $validated['price'],
            'description'   => $validated['description'] ?? null,
            'thumbnail_url' => $thumbnailUrl,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'create_tier',
            'target_type' => 'product_tier',
            'target_id'   => $tier->id,
            'description' => "Menambahkan tier \"{$tier->name}\" pada katalog \"{$product->name}\"",
        ]);

        return redirect()
            ->route('admin.katalog.tiers.index', $product->id)
            ->with('status', "Tier \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, string $product, string $tier): RedirectResponse
    {
        $product = Product::findOrFail($product);
        $tierModel = ProductTier::where('product_id', $product->id)->findOrFail($tier);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'price'       => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'thumbnail'   => ['nullable', 'image', 'max:10240'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        /** @var R2StorageService $storage */
        $storage = app(R2StorageService::class);

        $oldName = $tierModel->name;

        if ($request->hasFile('thumbnail')) {
            if ($tierModel->thumbnail_url) {
                $storage->delete($tierModel->thumbnail_url, R2StorageService::DISK_PUBLIC);
            }

            $validated['thumbnail_url'] = $storage->upload(
                $request->file('thumbnail'),
                'product_tiers',
                R2StorageService::DISK_PUBLIC
            );
        }

        $tierModel->update([
            'name'          => $validated['name'],
            'price'         => $validated['price'],
            'description'   => $validated['description'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? $tierModel->thumbnail_url,
            'is_active'     => $request->boolean('is_active'),
        ]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'update_tier',
            'target_type' => 'product_tier',
            'target_id'   => $tierModel->id,
            'description' => "Memperbarui tier \"{$oldName}\" → \"{$tierModel->name}\" pada katalog \"{$product->name}\"",
        ]);

        return redirect()
            ->route('admin.katalog.tiers.index', $product->id)
            ->with('status', "Tier \"{$tierModel->name}\" berhasil diperbarui.");
    }

    public function destroy(string $product, string $tier): RedirectResponse
    {
        $product = Product::findOrFail($product);
        $tierModel = ProductTier::where('product_id', $product->id)->findOrFail($tier);
        $nama = $tierModel->name;

        /** @var R2StorageService $storage */
        $storage = app(R2StorageService::class);

        if ($tierModel->thumbnail_url) {
            $storage->delete($tierModel->thumbnail_url, R2StorageService::DISK_PUBLIC);
        }

        $tierModel->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'delete_tier',
            'target_type' => 'product_tier',
            'target_id'   => $tierModel->id,
            'description' => "Menghapus tier \"{$nama}\" dari katalog \"{$product->name}\"",
        ]);

        return redirect()
            ->route('admin.katalog.tiers.index', $product->id)
            ->with('status', "Tier \"{$nama}\" berhasil dihapus.");
    }
}