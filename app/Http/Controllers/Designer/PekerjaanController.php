<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\OrderReference;
use App\Models\Deliverable;
use App\Models\OrderLog;
use App\Events\MessageSent;
use App\Services\BlacklistFilter;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PekerjaanController extends Controller
{
    private const STATUS_DIKERJAKAN = [
        'pending',
        'waiting_designer',
        'in_progress',
        'deliverable_sent',
        'revision_needed',
    ];

    private const STATUS_SELESAI = ['completed'];

    public function index(Request $request): View
    {
        $kataKunci = trim((string) $request->query('q', ''));
        $status    = $request->query('status', 'semua');

        $baseQuery = Order::where('designer_id', Auth::id());

        $query = (clone $baseQuery)->with(['product', 'productTier', 'customer']);

        if ($status === 'dikerjakan') {
            $query->whereIn('status', self::STATUS_DIKERJAKAN);
        } elseif ($status === 'selesai') {
            $query->whereIn('status', self::STATUS_SELESAI);
        }

        if ($kataKunci !== '') {
            $query->where(function ($q) use ($kataKunci) {
                $q->where('order_code', 'ilike', "%{$kataKunci}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'ilike', "%{$kataKunci}%"))
                  ->orWhereHas('customer', fn ($c) => $c->where('full_name', 'ilike', "%{$kataKunci}%"));
            });
        }

        $pekerjaan = $query->orderByDesc('created_at')
            ->paginate(6)
            ->withQueryString();

        $stats = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN (?, ?, ?, ?, ?) THEN 1 ELSE 0 END) as dikerjakan,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as selesai
            ', [
                ...self::STATUS_DIKERJAKAN,
                ...self::STATUS_SELESAI,
            ])
            ->first();

        return view('designer.pekerjaan.daftar-pekerjaan', [
            'pekerjaan'       => $pekerjaan,
            'kataKunci'       => $kataKunci,
            'status'          => $status,
            'totalSemua'      => (int) $stats->total,
            'totalDikerjakan' => (int) $stats->dikerjakan,
            'totalSelesai'    => (int) $stats->selesai,
        ]);
    }

    public function show(string $order)
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
        ])
            ->where('designer_id', Auth::id())
            ->findOrFail($order);

        return response()
            ->view('designer.pekerjaan.detail-pekerjaan', compact('order'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

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

        $orderModel = Order::where('designer_id', Auth::id())->findOrFail($order);

        if (!$orderModel->isChatOpen()) {
            return back()->withErrors([
                'isi' => 'Percakapan sudah ditutup karena pesanan telah selesai.',
            ]);
        }

        $conversation = $orderModel->conversation;

        if (!$conversation) {
            $conversation = Conversation::create([
                'order_id'    => $orderModel->id,
                'customer_id' => $orderModel->customer_id,
                'designer_id' => $orderModel->designer_id,
                'status'      => 'active',
            ]);
        }

        $messageType = $request->hasFile('attachment') ? 'file' : 'text';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => Auth::id(),
            'type'            => $messageType,
            'body'            => $request->isi ?? '',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            /** @var R2StorageService $storage */
            $storage = app(R2StorageService::class);
            $path = $storage->upload($file, 'attachments');

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_url'   => $path,
                'file_name'  => $file->getClientOriginalName(),
                'file_size'  => $file->getSize(),
                'mime_type'  => $file->getMimeType(),
                'file_type'  => $file->getClientOriginalExtension(),
            ]);
        }

        $message->load('attachments');
        $message->load('attachments', 'deliverable');

        broadcast(new MessageSent($message, Auth::user()))->toOthers();

        $conversation->touch();

        return redirect()
            ->route('designer.pekerjaan.show', $orderModel->id)
            ->with('status', 'Pesan terkirim.')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function downloadAttachment(string $order, string $attachment)
    {
        $orderModel = Order::where('designer_id', Auth::id())->findOrFail($order);

        $att = MessageAttachment::whereHas('message.conversation', function ($q) use ($orderModel) {
                $q->where('order_id', $orderModel->id);
            })
            ->where('id', $attachment)
            ->firstOrFail();

        return $this->streamFileFromR2($att->file_url, $att->file_name ?? 'file');
    }

    public function downloadReference(string $order, string $reference)
    {
        $orderModel = Order::where('designer_id', Auth::id())->findOrFail($order);

        $ref = OrderReference::where('order_id', $orderModel->id)
            ->where('id', $reference)
            ->firstOrFail();

        if ($ref->type !== 'file' || !$ref->file_url) {
            abort(404, 'File tidak ditemukan.');
        }

        return $this->streamFileFromR2($ref->file_url, $ref->file_name ?? 'file');
    }

    private function streamFileFromR2(string $path, string $filename)
    {
        $response = Http::withOptions([
            'stream' => true,
            // 'verify' => false, // ⚠️ hanya untuk dev, hapus di production
        ])->get(\App\Helpers\R2Helper::url($path));

        if ($response->failed()) {
            abort(404, 'Gagal mengambil file.');
        }

        return response()->streamDownload(function () use ($response) {
            echo $response->body();
        }, $filename);
    }

        public function previewReference(string $order, string $reference)
    {
        $orderModel = Order::where('designer_id', Auth::id())->findOrFail($order);

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
        $orderModel = Order::where('designer_id', Auth::id())->findOrFail($order);

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

    public function kirimDeliverable(Request $request, string $order)
    {
        $request->validate([
            'isi'           => 'nullable|string|max:2000',
            'attachments'   => 'required|array|min:1',
            'attachments.*' => 'file|max:25600|mimes:png,jpg,jpeg,gif,webp,pdf,zip,doc,docx,psd,ai',
        ], [
            'attachments.required' => 'Minimal 1 file harus diunggah.',
            'attachments.array'    => 'File tidak valid.',
            'attachments.*.max'    => 'Setiap file maksimal 25 MB.',
            'attachments.*.mimes'  => 'Format file tidak didukung.',
        ]);

        $files = $request->file('attachments');

        // Blacklist check — gabungkan isi + semua nama file
        $textToCheck = $request->isi ?? '';
        foreach ($files as $file) {
            $textToCheck .= ' ' . $file->getClientOriginalName();
        }
        $filter = BlacklistFilter::check($textToCheck);
        if ($filter['blocked']) {
            return back()
                ->withErrors(['isi' => $filter['reason'] . ' (' . $filter['keyword'] . ')'])
                ->withInput();
        }

        $orderModel = Order::where('designer_id', Auth::id())->findOrFail($order);

        if (!$orderModel->isChatOpen()) {
            return back()->withErrors(['isi' => 'Percakapan sudah ditutup.'])->withInput();
        }

        // Cek status order — hanya boleh kirim deliverable kalau in_progress / revision_needed
        if (!in_array($orderModel->status, ['in_progress', 'revision_needed'])) {
            return back()->withErrors([
                'attachments' => 'Tidak bisa kirim hasil saat order berstatus ' . $orderModel->status . '.',
            ])->withInput();
        }

        // Cek apakah sudah ada deliverable pending
        if ($orderModel->deliverables()->where('status', 'pending')->exists()) {
            return back()->withErrors([
                'attachments' => 'Sudah ada deliverable yang menunggu review.',
            ])->withInput();
        }

        $conversation = $orderModel->conversation;

        if (!$conversation) {
            $conversation = Conversation::create([
                'order_id'    => $orderModel->id,
                'customer_id' => $orderModel->customer_id,
                'designer_id' => $orderModel->designer_id,
                'status'      => 'active',
            ]);
        }

        $fileCount = count($files);

        DB::transaction(function () use ($request, $orderModel, $conversation, $files) {
            // 1. Buat message dengan type 'deliverable'
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => Auth::id(),
                'type'            => 'deliverable',
                'body'            => $request->isi ?? 'Hasil desain',
            ]);

            // 2. Upload semua file
            /** @var R2StorageService $storage */
            $storage = app(R2StorageService::class);

            foreach ($files as $file) {
                $path = $storage->upload($file, 'deliverables');

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_url'   => $path,
                    'file_name'  => $file->getClientOriginalName(),
                    'file_size'  => $file->getSize(),
                    'mime_type'  => $file->getMimeType(),
                    'file_type'  => $file->getClientOriginalExtension(),
                ]);
            }

            // 3. Buat record deliverable
            Deliverable::create([
                'order_id'   => $orderModel->id,
                'message_id' => $message->id,
                'status'     => 'pending',
            ]);

            // 4. Update order status
            $orderModel->update(['status' => 'deliverable_sent']);

            // 5. Log
            OrderLog::create([
                'order_id' => $orderModel->id,
                'actor_id' => Auth::id(),
                'action'   => 'deliverable_sent',
                'status'   => 'deliverable_sent',
                'note'     => 'Designer mengirim ' . count($files) . ' file hasil desain.',
            ]);

            // 6. Load relasi + broadcast
            $message->load('attachments', 'deliverable');
            broadcast(new MessageSent($message, Auth::user()))->toOthers();

            $conversation->touch();
        });

        return redirect()
            ->route('designer.pekerjaan.show', $orderModel->id)
            ->with('status', $fileCount . ' file hasil desain terkirim. Menunggu review customer.')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}