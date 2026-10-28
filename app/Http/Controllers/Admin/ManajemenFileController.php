<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\OrderReference;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManajemenFileController extends Controller
{
    protected R2StorageService $r2Service;

    public function __construct(R2StorageService $r2Service)
    {
        $this->r2Service = $r2Service;
    }

    public function index(Request $request)
    {
        $tab    = $request->input('tab', 'deliverables');
        $search = $request->input('q');
        $sort   = $request->input('sort', 'terbaru');

        if ($tab === 'references') {
            $query = OrderReference::with(['order.customer'])
                ->where('type', OrderReference::TYPE_FILE);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('file_name', 'ilike', "%{$search}%")
                        ->orWhereHas('order', fn($oq) => $oq->where('order_code', 'ilike', "%{$search}%"));
                });
            }

            $files = $query->{$sort === 'terlama' ? 'oldest' : 'latest'}()
                ->paginate(10)
                ->withQueryString();
        } else {
            $query = MessageAttachment::whereHas('message', function ($m) {
                $m->where('type', Message::TYPE_DELIVERABLE);
            })->with([
                'message.deliverable.order.designer',
                'message.deliverable.order.product',
                'message.conversation.order.designer',
                'message.conversation.order.product',
            ]);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('file_name', 'ilike', "%{$search}%")
                        ->orWhereHas('message.conversation.order', fn($oq) => $oq->where('order_code', 'ilike', "%{$search}%"));
                });
            }

            $files = $query->{$sort === 'terlama' ? 'oldest' : 'latest'}()
                ->paginate(10)
                ->withQueryString();
        }

        // Jika dipanggil via AJAX / Fetch (Berpindah Tab / Pagination)
        if ($request->ajax()) {
    return view('admin.manajemen-file-table', compact('files', 'tab'))->render();
}

return view('admin.manajemen-file', compact('files', 'tab', 'search', 'sort'));
    }

    public function preview(Request $request, string $type, string $id)
    {
        $file = $type === 'references'
            ? OrderReference::findOrFail($id)
            : MessageAttachment::findOrFail($id);

        $cleanPath = ltrim($file->file_url ?? '', '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $cleanPath, $m)) {
            $cleanPath = $m[1];
        }

        if (!$cleanPath || !$this->r2Service->existsPrivate($cleanPath)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'File tidak ditemukan di Cloudflare R2 storage.'], 404);
            }
            return back()->with('error', 'File tidak ditemukan di Cloudflare R2 storage.');
        }

        $url = $this->r2Service->temporaryUrlPrivate($cleanPath, 15);

        if ($request->expectsJson()) {
            return response()->json([
                'url'       => $url,
                'file_name' => $file->file_name ?? 'Preview File',
                'mime_type' => $file->mime_type ?? '',
            ]);
        }

        return redirect()->away($url);
    }

    public function download(string $type, string $id)
    {
        $file = $type === 'references'
            ? OrderReference::findOrFail($id)
            : MessageAttachment::findOrFail($id);

        $cleanPath = ltrim($file->file_url ?? $file->file_path ?? '', '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $cleanPath, $m)) {
            $cleanPath = $m[1];
        }

        if (!$cleanPath || !$this->r2Service->existsPrivate($cleanPath)) {
            return back()->with('error', 'File tidak ditemukan di Cloudflare R2 storage.');
        }

        $fileName = $file->file_name ?? 'download-file';

        return response()->streamDownload(function () use ($cleanPath) {
            $stream = Storage::disk('r2')->readStream($cleanPath);
            if ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, $fileName);
    }

    public function destroy(string $type, string $id)
    {
        $file = $type === 'references'
            ? OrderReference::findOrFail($id)
            : MessageAttachment::findOrFail($id);

        $cleanPath = ltrim($file->file_url ?? '', '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $cleanPath, $m)) {
            $cleanPath = $m[1];
        }

        if ($cleanPath && $this->r2Service->existsPrivate($cleanPath)) {
            $this->r2Service->deletePrivate($cleanPath);
        }

        $file->delete();

        return back()->with('success', 'File berhasil dihapus dari sistem.');
    }
}