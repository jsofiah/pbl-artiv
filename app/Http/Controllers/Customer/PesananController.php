<?php

namespace App\Http\Controllers\Customer;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderReference;
use App\Models\Deliverable;
use App\Services\BlacklistFilter;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['product', 'productTier', 'designer'])
            ->where('customer_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('customer.pesanan', compact('orders'));
    }

    public function show(string $order): View
    {
        $order = Order::with([
            'customer',
            'designer.designerStats',
            'product',
            'productTier',
            'expressFee',
            'references',
            'conversation.messages.sender',
            'conversation.messages.attachments',
            'conversation.messages.deliverable',
            'logs.actor',
            'deliverables',
            'payments',
            'review',
        ])
            ->where('customer_id', Auth::id())
            ->findOrFail($order);

        return view('customer.pesanan.detail', compact('order'));
    }

    public function kirimPesan(Request $request, string $order)
    {
        $request->validate([
            'isi'        => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:25600|mimes:png,jpg,jpeg,gif,webp,pdf,zip,doc,docx',
        ]);

        if (!$request->filled('isi') && !$request->hasFile('attachment')) {
            return back()->withErrors(['isi' => 'Pesan atau lampiran harus diisi.']);
        }

        $textToCheck = $request->isi ?? '';
        if ($request->hasFile('attachment')) {
            $textToCheck .= ' ' . $request->file('attachment')->getClientOriginalName();
        }
        $filter = BlacklistFilter::check($textToCheck);
        if ($filter['blocked']) {
            return back()
                ->withErrors(['isi' => $filter['reason'] . ' (' . $filter['keyword'] . ')'])
                ->withInput();
        }

        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        if (!$orderModel->isChatOpen()) {
            return back()->withErrors([
                'isi' => 'Percakapan sudah ditutup karena pesanan telah selesai.',
            ]);
        }

        $conversation = $orderModel->conversation;

        if (!$conversation && $orderModel->designer_id) {
            $conversation = Conversation::create([
                'order_id'    => $orderModel->id,
                'customer_id' => $orderModel->customer_id,
                'designer_id' => $orderModel->designer_id,
                'status'      => 'active',
            ]);
        }

        abort_if(!$conversation, 404, 'Percakapan belum tersedia. Menunggu designer.');

        $messageType = $request->hasFile('attachment') ? 'file' : 'text';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => Auth::id(),
            'type'            => $messageType,
            'body'            => $request->isi,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            /** @var R2StorageService $storage */
            $storage = app(R2StorageService::class);

            $fileUrl = $storage->upload($file, 'attachments');

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_url'   => $fileUrl,
                'file_name'  => $file->getClientOriginalName(),
                'file_size'  => $file->getSize(),
                'mime_type'  => $file->getMimeType(),
                'file_type'  => $file->getClientOriginalExtension(),
            ]);
        }

        $message->load('attachments');

        broadcast(new MessageSent($message, Auth::user()))->toOthers();

        $conversation->touch();

        return redirect()
            ->route('customer.pesanan.show', $orderModel->id)
            ->with('status', 'Pesan terkirim.');
    }

    public function downloadReference(string $order, string $reference): \Illuminate\Http\RedirectResponse
    {
        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        $ref = OrderReference::where('order_id', $orderModel->id)
            ->where('id', $reference)
            ->firstOrFail();

        if ($ref->type !== 'file' || !$ref->file_url) {
            abort(404, 'File tidak ditemukan.');
        }

        $path = ltrim($ref->file_url, '/');

        if (preg_match('#^https?://[^/]+/(.+)$#', $path, $m)) {
            $path = $m[1];
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $r2 */
        $r2 = Storage::disk('r2');

        $url = $r2->temporaryUrl(
            $path,
            now()->addMinutes(5),
            [
                'ResponseContentDisposition' => 'attachment; filename="' . ($ref->file_name ?? 'file') . '"',
            ]
        );

        return redirect()->away($url);
    }

    public function downloadAttachment(string $order, string $attachment): \Illuminate\Http\RedirectResponse
    {
        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        $att = MessageAttachment::whereHas('message.conversation', function ($q) use ($orderModel) {
                $q->where('conversation_id', $orderModel->conversation?->id);
            })
            ->where('id', $attachment)
            ->firstOrFail();

        $path = ltrim($att->file_url, '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $path, $m)) {
            $path = $m[1];
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $r2 */
        $r2 = Storage::disk('r2');
        $url = $r2->temporaryUrl(
            $path,
            now()->addMinutes(5),
            [
                'ResponseContentDisposition' => 'attachment; filename="' . ($att->file_name ?? 'file') . '"',
            ]
        );

        return redirect()->away($url);
    }

    public function previewReference(string $order, string $reference)
    {
        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        $ref = OrderReference::where('order_id', $orderModel->id)
            ->where('id', $reference)
            ->firstOrFail();

        if ($ref->type !== 'file' || !$ref->file_url) {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }

        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        if (!in_array(strtolower($ref->mime_type ?? ''), $imageMimes)) {
            return response()->json(['error' => 'Preview hanya untuk gambar.'], 404);
        }

        $path = ltrim($ref->file_url, '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $path, $m)) {
            $path = $m[1];
        }

        $url = Storage::disk('r2')->temporaryUrl($path, now()->addMinutes(15));

        return response()->json([
            'url'  => $url,
            'name' => $ref->file_name,
        ]);
    }

    public function previewAttachment(string $order, string $attachment)
    {
        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        $att = MessageAttachment::whereHas('message.conversation', function ($q) use ($orderModel) {
                $q->where('conversation_id', $orderModel->conversation?->id);
            })
            ->where('id', $attachment)
            ->firstOrFail();

        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        if (!in_array(strtolower($att->mime_type ?? ''), $imageMimes)) {
            return response()->json(['error' => 'Preview hanya untuk gambar.'], 404);
        }

        $path = ltrim($att->file_url, '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $path, $m)) {
            $path = $m[1];
        }

        $url = Storage::disk('r2')->temporaryUrl($path, now()->addMinutes(15));

        return response()->json([
            'url'  => $url,
            'name' => $att->file_name,
        ]);
    }

    public function approveDeliverable(string $order, string $deliverable)
    {
        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        $deliverableModel = Deliverable::where('order_id', $orderModel->id)
            ->where('id', $deliverable)
            ->firstOrFail();

        if ($deliverableModel->status !== 'pending') {
            return back()->withErrors(['deliverable' => 'Deliverable ini sudah diproses.']);
        }

        if ($orderModel->status === 'completed') {
            return back()->withErrors(['deliverable' => 'Pesanan sudah selesai.']);
        }

        DB::transaction(function () use ($orderModel, $deliverableModel) {
    $deliverableModel->update([
        'status'      => 'approved',
        'approved_at' => now(),
    ]);

    $orderModel->update([
        'status'       => 'completed',
        'completed_at' => now(),
    ]);

    OrderLog::create([
        'order_id' => $orderModel->id,
        'actor_id' => Auth::id(),
        'action'   => 'deliverable_approved',
        'status'   => 'completed',
        'note'     => 'Customer menyetujui hasil desain.',
    ]);
});


        return redirect()
            ->route('customer.pesanan.show', $orderModel->id)
            ->with('status', 'Hasil desain disetujui. Pesanan selesai!');
    }

    public function requestRevision(Request $request, string $order, string $deliverable)
    {
        $validated = $request->validate([
            'revision_note' => 'required|string|min:5|max:2000',
        ], [
            'revision_note.required' => 'Catatan revisi wajib diisi.',
            'revision_note.min'      => 'Catatan revisi minimal 5 karakter.',
        ]);

        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        $deliverableModel = Deliverable::where('order_id', $orderModel->id)
            ->where('id', $deliverable)
            ->firstOrFail();

        if ($deliverableModel->status !== 'pending') {
            return back()->withErrors(['deliverable' => 'Deliverable ini sudah diproses.']);
        }

        if ($orderModel->status === 'completed') {
            return back()->withErrors(['deliverable' => 'Pesanan sudah selesai, tidak bisa revisi.']);
        }

        DB::transaction(function () use ($orderModel, $deliverableModel, $validated) {
            $deliverableModel->update([
                'status'        => 'revision_requested',
                'revision_note' => $validated['revision_note'],
            ]);

            $orderModel->update([
                'status'         => 'revision_needed',
                'revision_count' => $orderModel->revision_count + 1,
            ]);

            OrderLog::create([
                'order_id' => $orderModel->id,
                'actor_id' => Auth::id(),
                'action'   => 'revision_requested',
                'status'   => 'revision_needed',
                'note'     => $validated['revision_note'],
            ]);
        });

        return redirect()
            ->route('customer.pesanan.show', $orderModel->id)
            ->with('status', 'Permintaan revisi terkirim ke designer.');
    }
}