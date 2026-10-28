<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationService
{
    // ======================= CHAT (customer <-> designer) =======================

    public function newChat(Message $message): ?Notification
    {
        if ($message->isSystem()) {
            return null;
        }

        $conversation = $message->conversation;
        $sender       = $message->sender;

        if (!$conversation || !$sender) {
            return null;
        }

        if ($sender->isDesigner()) {
            $recipientId = $conversation->customer_id;
            $url = route('customer.pesanan.show', $conversation->order_id) . '#chat-container';
        } elseif ($sender->isCustomer()) {
            $recipientId = $conversation->designer_id;
            $url = route('designer.pekerjaan.show', $conversation->order_id) . '#chat-container';
        } else {
            return null;
        }

        if (!$recipientId) {
            return null;
        }

        $body = match ($message->type) {
            Message::TYPE_FILE        => 'Mengirim sebuah file.',
            Message::TYPE_DELIVERABLE => 'Mengirim hasil desain untuk kamu cek.',
            default                   => Str::limit($message->body ?? '', 80),
        };

        $payload = [
            'title' => "Pesan baru dari {$sender->full_name}",
            'body'  => $body,
            'data'  => [
                'order_id'        => $conversation->order_id,
                'conversation_id' => $conversation->id,
                'sender_name'     => $sender->full_name,
                'sender_avatar'   => $sender->avatar_url,
                'url'             => $url,
            ],
        ];

        // Chat belum dibaca di percakapan yang sama: perbarui saja, tanpa email berulang
        $existing = Notification::where('user_id', $recipientId)
            ->where('type', Notification::TYPE_NEW_CHAT)
            ->where('data->conversation_id', $conversation->id)
            ->unread()
            ->first();

        if ($existing) {
            $existing->forceFill($payload + ['created_at' => now()])->save();
            return $existing;
        }

        return $this->push($payload + [
            'user_id' => $recipientId,
            'type'    => Notification::TYPE_NEW_CHAT,
        ]);
    }

    // ======================= ORDER =======================

    /** Order masuk Job Pool -> designer yang tersedia */
    public function newOrderAvailable(Order $order): void
    {
        // Pengaman: satu order hanya diumumkan sekali
        $alreadySent = Notification::where('type', Notification::TYPE_NEW_ORDER)
            ->where('data->order_id', $order->id)
            ->exists();

        if ($alreadySent) {
            return;
        }

        User::where('role', User::ROLE_DESIGNER)
            ->with('designerStats')
            ->get()
            ->filter(function (User $designer) {
                $stats = $designer->designerStats;

                if (!$stats) {
                    return true;
                }

                $suspended = $stats->suspended_until && $stats->suspended_until->isFuture();

                return ($stats->availability_status ?? 'available') === 'available' && !$suspended;
            })
            ->each(function (User $designer) use ($order) {
                $this->push([
                    'user_id' => $designer->id,
                    'type'    => Notification::TYPE_NEW_ORDER,
                    'title'   => 'Order baru tersedia',
                    'body'    => "Order {$order->order_code} menunggu diambil di Job Pool.",
                    'data'    => [
                        'order_id'   => $order->id,
                        'order_code' => $order->order_code,
                        'url'        => route('designer.job-pool.show', $order),
                    ],
                ]);
            });
    }

    /** Customer menyetujui desain -> customer dan designer */
    public function orderCompleted(Order $order): void
    {
        $this->push([
            'user_id' => $order->customer_id,
            'type'    => Notification::TYPE_ORDER_COMPLETED,
            'title'   => 'Pesanan selesai',
            'body'    => "Pesanan {$order->order_code} telah selesai. Beri ulasan untuk designer-mu!",
            'data'    => [
                'order_id'   => $order->id,
                'order_code' => $order->order_code,
                'url'        => route('customer.pesanan.show', $order),
            ],
        ]);

        if ($order->designer_id) {
            $this->push([
                'user_id' => $order->designer_id,
                'type'    => Notification::TYPE_ORDER_COMPLETED,
                'title'   => 'Hasil desain disetujui',
                'body'    => "Customer menyetujui hasil desain pesanan {$order->order_code}.",
                'data'    => [
                    'order_id'   => $order->id,
                    'order_code' => $order->order_code,
                    'url'        => route('designer.riwayat.show', $order),
                ],
            ]);
        }
    }

    /** Customer minta revisi -> designer */
    public function revisionRequested(Order $order): ?Notification
    {
        if (!$order->designer_id) {
            return null;
        }

        return $this->push([
            'user_id' => $order->designer_id,
            'type'    => Notification::TYPE_REVISION_REQUESTED,
            'title'   => 'Permintaan revisi',
            'body'    => "Customer meminta revisi untuk pesanan {$order->order_code}.",
            'data'    => [
                'order_id'   => $order->id,
                'order_code' => $order->order_code,
                'url'        => route('designer.pekerjaan.show', $order),
            ],
        ]);
    }

    /** Designer mengambil order -> customer */
    public function orderTaken(Order $order): ?Notification
    {
        $designer = $order->designer;

            if (!$designer) {
                 return null;
            }

            return $this->push([
                'user_id' => $order->customer_id,
                'type'    => Notification::TYPE_ORDER_TAKEN,
                'title'   => 'Pesanan diambil',
                'body'    => "{$designer->full_name} mengambil pesananmu {$order->order_code}.",
                'data'    => [
                     'order_id'   => $order->id,
                        'order_code' => $order->order_code,
                    'url'        => route('customer.pesanan.show', $order),
                ],
            ]);
        }
    



    // ======================= ADMIN =======================

    /** Bukti pembayaran masuk -> semua admin */
    public function paymentReceived(Payment $payment): void
    {
        $order = $payment->order;
        $kode  = $order?->order_code ?? '-';
        $jenis = $payment->isRevisionFee() ? 'biaya revisi' : 'pesanan';

        $this->toAdmins(
            Notification::TYPE_PAYMENT,
            'Pembayaran baru',
            "Pembayaran {$jenis} {$kode} menunggu verifikasi.",
            ['order_id' => $payment->order_id, 'payment_id' => $payment->id, 'url' => route('admin.monitoring')]
        );
    }

    /** Refund diajukan -> semua admin */
    public function refundRequested(OrderRefund $refund): void
    {
        $order  = Order::find($refund->order_id);
        $kode   = $order?->order_code ?? '-';
        $jumlah = number_format((float) $refund->amount, 0, ',', '.');

        $this->toAdmins(
            Notification::TYPE_REFUND_REQUESTED,
            'Pengajuan refund baru',
            "Refund pesanan {$kode} sebesar Rp {$jumlah} menunggu diproses.",
            ['order_id' => $refund->order_id, 'refund_id' => $refund->id, 'url' => route('admin.monitoring')]
        );
    }

    /** Aktivitas penting sistem -> semua admin */
    public function systemActivity(string $type, string $title, string $body, array $data = []): void
    {
        $this->toAdmins($type, $title, $body, $data + ['url' => route('admin.monitoring')]);
    }

    // ======================= INTERNAL =======================

    private function toAdmins(string $type, string $title, string $body, array $data): void
    {
        User::where('role', User::ROLE_ADMIN)->get()->each(function (User $admin) use ($type, $title, $body, $data) {
            $this->push([
                'user_id' => $admin->id,
                'type'    => $type,
                'title'   => $title,
                'body'    => $body,
                'data'    => $data,
            ]);
        });
    }

    /** Simpan notifikasi, lalu kirim email (customer dan designer saja) */
    private function push(array $attributes): Notification
    {
        $notification = Notification::create($attributes);

        $this->sendEmail($notification);

        return $notification;
    }

    private function sendEmail(Notification $notification): void
    {
        $user = User::find($notification->user_id);

        if (!$user || !$user->email || $user->isAdmin()) {
            return;
        }

        try {
            Mail::to($user->email)->send(new NotificationMail($notification));
        } catch (\Throwable $e) {
            report($e); // gagal kirim email tidak boleh menggagalkan proses utama
        }
    }
}