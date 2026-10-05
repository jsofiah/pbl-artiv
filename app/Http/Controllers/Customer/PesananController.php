<?php

namespace App\Http\Controllers\Customer;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\OrderReference;
use App\Models\Product;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PesananController extends Controller
{
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

        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

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
                $q->where('order_id', $orderModel->id);
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
}