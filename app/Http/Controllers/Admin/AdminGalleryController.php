<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminGalleryController extends Controller
{
    private function resolveUrl(?string $path, int $minutes = 30): ?string
    {
        if (! $path) return null;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $path, $m)) {
            $path = $m[1];
        }

        try {
            return Storage::disk('r2')->temporaryUrl($path, now()->addMinutes($minutes));
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function index()
    {
        $items = GalleryItem::with(['product', 'order', 'attachment'])
            ->latest()
            ->paginate(12)
            ->through(function ($item) {
                $item->display_url = $this->resolveUrl($item->image_url);
                return $item;
            });

        return view('admin.galeri.index', compact('items'));
    }

    public function kandidat(Request $request)
    {
        $q = $request->input('q');

        $kandidat = MessageAttachment::galleryCandidate()
            ->with([
                'message.conversation.order.product',
                'message.conversation.order.designer',
            ])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('file_name', 'ilike', "%{$q}%")
                        ->orWhereHas('message.conversation.order', function ($order) use ($q) {
                            $order->where('order_code', 'ilike', "%{$q}%");
                        });
                });
            })
            ->latest()
            ->paginate(24)
            ->through(function ($att) {
                $att->preview_url = $this->resolveUrl($att->file_url);
                return $att;
            });

        return view('admin.galeri.kandidat', compact('kandidat', 'q'));
    }

    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'attachment_ids'   => ['required', 'array', 'min:1'],
            'attachment_ids.*' => ['exists:message_attachments,id'],
            'product_id'       => ['nullable', 'exists:products,id'],
        ]);

        $created = 0;

        foreach ($validated['attachment_ids'] as $attachmentId) {
            $attachment = MessageAttachment::with('message.conversation.order.product')
                ->find($attachmentId);

            if (! $attachment || $attachment->galleryItem()->exists()) {
                continue;
            }

            $order = $attachment->message?->conversation?->order;

            GalleryItem::create([
                'product_id'            => $validated['product_id'] ?? $order?->product_id,
                'order_id'              => $order?->id,
                'message_attachment_id' => $attachment->id,
                'title'                 => $order?->product?->name ?? $attachment->file_name,
                'description'           => null,
                'image_url'             => $attachment->file_url,
                'is_active'             => true,
                'created_by'            => $request->user()->id,
            ]);

            $created++;
        }

        return redirect()
            ->route('admin.galeri.index')
            ->with('success', "{$created} file berhasil ditambahkan ke galeri.");
            }

    public function destroy(GalleryItem $galleryItem)
    {
        $galleryItem->delete();
        return back()->with('success', 'Item galeri dihapus.');
    }

    public function toggle(GalleryItem $galleryItem)
    {
        $galleryItem->update(['is_active' => ! $galleryItem->is_active]);
        return back()->with('success', 'Status galeri diperbarui.');
    }
}