<?php

namespace App\Http\Controllers\Customer;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\OrderLog;
use App\Models\OrderReference;
use App\Models\Notification;
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
            'refund',
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

            if ($orderModel->designer_id) {
                $this->createOrderSplit($orderModel);
            }
        });

        if ($orderModel->conversation) {
            broadcast(new \App\Events\DeliverableUpdated(
                $deliverableModel->fresh(),
                $orderModel->conversation->id,
                'approved'
            ))->toOthers();
        }

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

        if (!$orderModel->canRequestFreeRevision()) {
            return back()->withErrors([
                'deliverable' => 'Batas revisi gratis (' . \App\Models\Order::FREE_REVISION_LIMIT . 'x) sudah habis.',
            ]);
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
                'note'     => 'Customer meminta revisi hasil desain.',
            ]);
        });

        // Broadcast real-time
        if ($orderModel->conversation) {
            broadcast(new \App\Events\DeliverableUpdated(
                $deliverableModel->fresh(),
                $orderModel->conversation->id,
                'revision_requested'
            ))->toOthers();
        }

        return redirect()
            ->route('customer.pesanan.show', $orderModel->id)
            ->with('status', 'Permintaan revisi terkirim ke designer.');
    }

    private function createOrderSplit(Order $order): void
    {
        if (\App\Models\OrderSplit::where('order_id', $order->id)->exists()) {
            return;
        }

        $grossAmount = (float) $order->total_price;

        $isReassignment = $order->reassign_count > 0;

        $splitType = $isReassignment
            ? \App\Models\OrderSplit::TYPE_REASSIGNMENT
            : \App\Models\OrderSplit::TYPE_NORMAL;

        $commissionRate = $isReassignment
            ? \App\Models\OrderSplit::COMMISSION_REASSIGNMENT
            : \App\Models\OrderSplit::COMMISSION_NORMAL;

        $commissionAmount = round($grossAmount * $commissionRate / 100, 2);
        $designerEarning  = round($grossAmount - $commissionAmount, 2);

        \App\Models\OrderSplit::create([
            'order_id'          => $order->id,
            'designer_id'       => $order->designer_id,
            'split_type'        => $splitType,
            'commission_rate'   => $commissionRate,
            'gross_amount'      => $grossAmount,
            'commission_amount' => $commissionAmount,
            'designer_earning'  => $designerEarning,
        ]);
    }

    public function ajukanRefund(Request $request, string $order)
    {
        $request->validate([
            'bank_name'           => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:30',
            'bank_account_name'   => 'required|string|max:100',
        ]);

        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        if (!$orderModel->canCustomerRefund()) {
            return back()->with('error', 'Refund tidak tersedia untuk order ini.');
        }

        DB::transaction(function () use ($request, $orderModel) {
            $orderModel->update([
                'status'               => Order::STATUS_REFUND_REQUESTED,
                'customer_decision'    => Order::DECISION_REFUND,
                'customer_decision_at' => now(),
                'cancelled_at'         => now(),
            ]);

            $refund = OrderRefund::create([
                'order_id'            => $orderModel->id,
                'customer_id'         => Auth::id(),
                'bank_name'           => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name'   => $request->bank_account_name,
                'amount'              => $orderModel->total_price,
                'status'              => OrderRefund::STATUS_PENDING,
            ]);

            OrderLog::create([
                'order_id' => $orderModel->id,
                'actor_id' => Auth::id(),
                'action'   => OrderLog::ACTION_CUSTOMER_REFUND,
                'status'   => Order::STATUS_REFUND_REQUESTED,
                'note'     => 'Customer mengajukan refund karena tidak ingin menunggu designer pengganti.',
            ]);

            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type'    => Notification::TYPE_REFUND_REQUESTED,
                    'title'   => 'Pengajuan Refund Baru',
                    'body'    => "Customer mengajukan refund untuk order {$orderModel->order_code} sebesar Rp " . number_format($orderModel->total_price, 0, ',', '.') . ".",
                    'data'    => ['url' => route('admin.monitoring') . '?tab=refund'],
                ]);
            }
        });

        return back()->with('success', 'Pengajuan refund berhasil. Dana akan diproses admin dalam 1-3 hari kerja.');
    }

    public function decideReassignment(Request $request, string $order)
    {
        $request->validate([
            'decision' => 'required|in:refund,continue',
        ]);

        $orderModel = Order::where('customer_id', Auth::id())
            ->where('status', Order::STATUS_WAITING_CUSTOMER_DECISION)
            ->findOrFail($order);

        if ($request->decision === 'refund') {
            return redirect()
                ->route('customer.pesanan.show', $orderModel->id)
                ->with('open_refund_modal', true);
        }

        DB::transaction(function () use ($orderModel) {
            $orderModel->update([
                'status'               => Order::STATUS_WAITING_DESIGNER,
                'customer_decision'    => Order::DECISION_CONTINUE,
                'customer_decision_at' => now(),
            ]);

            OrderLog::create([
                'order_id' => $orderModel->id,
                'actor_id' => Auth::id(),
                'action'   => OrderLog::ACTION_CUSTOMER_CHOOSE_CONTINUE,
                'status'   => Order::STATUS_WAITING_DESIGNER,
                'note'     => 'Customer memilih order dilanjutkan oleh designer pengganti.',
            ]);

            $designers = \App\Models\User::where('role', 'designer')
                ->whereHas('designerStats', function ($q) {
                    $q->where('availability_status', 'available')
                    ->where(function ($q2) {
                        $q2->whereNull('suspended_until')
                            ->orWhere('suspended_until', '<=', now());
                    });
                })
                ->get();

            foreach ($designers as $d) {
                Notification::create([
                    'user_id' => $d->id,
                    'type'    => Notification::TYPE_NEW_ORDER,
                    'title'   => 'Order Urgent Tersedia',
                    'body'    => "Order {$orderModel->order_code} membutuhkan designer pengganti (Prioritas Urgent).",
                    'data'    => ['url' => route('designer.job-pool')],
                ]);
            }
        });

        return redirect()
            ->route('customer.pesanan.show', $orderModel->id)
            ->with('success', 'Order akan segera dialihkan ke designer pengganti.');
    }

    public function previewRefundProof(string $order)
    {
        $orderModel = Order::where('customer_id', Auth::id())->findOrFail($order);

        $refund = $orderModel->refund;

        if (!$refund || !$refund->proof_url) {
            return response()->json(['error' => 'Bukti refund tidak ditemukan.'], 404);
        }

        $path = ltrim($refund->proof_url, '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $path, $m)) {
            $path = $m[1];
        }

        $url = Storage::disk('r2')->temporaryUrl($path, now()->addMinutes(15));

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isPdf = $ext === 'pdf';

        return response()->json([
            'url'   => $url,
            'is_pdf'=> $isPdf,
            'name'  => 'Bukti-Refund-' . $orderModel->order_code . '.' . $ext,
        ]);
    }
}