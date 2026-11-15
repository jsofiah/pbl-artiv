<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\Deliverable;
use App\Models\GalleryItem;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageAttachmentTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: isImage() true untuk mime_type image/*, false untuk lainnya
    // ============================================================
    public function test_is_image_returns_true_for_image_mime(): void
    {
        $imageAttachment = $this->createAttachment([
            'mime_type' => 'image/png',
        ]);

        $this->assertTrue($imageAttachment->isImage());

        $pdfAttachment = $this->createAttachment([
            'mime_type' => 'application/pdf',
        ]);

        $this->assertFalse($pdfAttachment->isImage());

        $nullMime = $this->createAttachment([
            'mime_type' => null,
        ]);

        $this->assertFalse($nullMime->isImage());
    }

    // ============================================================
    // Test #2: humanSize() format bytes dengan benar
    // ============================================================
    public function test_human_size_formats_bytes_correctly(): void
    {
        // 500 bytes → "500 B"
        $small = $this->createAttachment(['file_size' => 500]);
        $this->assertEquals('500 B', $small->humanSize());

        // 1024 bytes → "1 KB"
        $kb = $this->createAttachment(['file_size' => 1024]);
        $this->assertEquals('1 KB', $kb->humanSize());

        // 1.5 MB (1572864 bytes) → "1.5 MB"
        $mb = $this->createAttachment(['file_size' => 1572864]);
        $this->assertEquals('1.5 MB', $mb->humanSize());

        // 2 GB (2147483648 bytes) → "2 GB"
        $gb = $this->createAttachment(['file_size' => 2147483648]);
        $this->assertEquals('2 GB', $gb->humanSize());

        // Null file_size → "0 B"
        $nullSize = $this->createAttachment(['file_size' => null]);
        $this->assertEquals('0 B', $nullSize->humanSize());
    }

    // ============================================================
    // Test #3: Relasi message() mengembalikan Message
    // ============================================================
    public function test_message_relation_returns_correct_message(): void
    {
        $attachment = $this->createAttachment();

        $relatedMessage = $attachment->message;

        $this->assertInstanceOf(Message::class, $relatedMessage);
        $this->assertEquals($attachment->message_id, $relatedMessage->id);
    }

    // ============================================================
    // Test #4: Relasi galleryItem() mengembalikan GalleryItem
    // ============================================================
    public function test_gallery_item_relation_returns_gallery_item(): void
    {
        $attachment = $this->createAttachment();
        $customer = $attachment->message->conversation->customer;
        $admin = $this->createUser(User::ROLE_ADMIN);

        GalleryItem::create([
            'product_id'            => $attachment->message->conversation->order->product_id,
            'order_id'              => $attachment->message->conversation->order_id,
            'message_attachment_id' => $attachment->id,
            'title'                 => 'Hasil Karya',
            'image_url'             => 'gallery/hasil.png',
            'is_active'             => true,
            'created_by'            => $admin->id,
        ]);

        $relatedGallery = $attachment->galleryItem;

        $this->assertInstanceOf(GalleryItem::class, $relatedGallery);
        $this->assertEquals($attachment->id, $relatedGallery->message_attachment_id);
    }

    // ============================================================
    // Test #5: Scope galleryCandidate() return attachment yang memenuhi syarat
    // ============================================================
    public function test_scope_gallery_candidate_returns_eligible_attachment(): void
    {
        // 1. Bikin data berantai: order completed, deliverable approved, image attachment
        $attachment = $this->createAttachment([
            'mime_type' => 'image/jpeg',
        ], [
            'order_status' => Order::STATUS_COMPLETED,
            'deliverable_status' => Deliverable::STATUS_APPROVED,
        ]);

        $candidates = MessageAttachment::galleryCandidate()->get();

        $this->assertCount(1, $candidates);
        $this->assertEquals($attachment->id, $candidates->first()->id);
    }

    // ============================================================
    // Test #6: Scope galleryCandidate() exclude attachment non-image
    // ============================================================
    public function test_scope_gallery_candidate_excludes_non_image(): void
    {
        $this->createAttachment([
            'mime_type' => 'application/pdf',
        ], [
            'order_status' => Order::STATUS_COMPLETED,
            'deliverable_status' => Deliverable::STATUS_APPROVED,
        ]);

        $candidates = MessageAttachment::galleryCandidate()->get();

        $this->assertCount(0, $candidates);
    }

    // ============================================================
    // Test #7: Scope galleryCandidate() exclude kalau order belum completed
    // ============================================================
    public function test_scope_gallery_candidate_excludes_incomplete_order(): void
    {
        $this->createAttachment([
            'mime_type' => 'image/jpeg',
        ], [
            'order_status' => Order::STATUS_IN_PROGRESS,
            'deliverable_status' => Deliverable::STATUS_APPROVED,
        ]);

        $candidates = MessageAttachment::galleryCandidate()->get();

        $this->assertCount(0, $candidates);
    }

    // ============================================================
    // Test #8: Scope galleryCandidate() exclude kalau deliverable belum approved
    // ============================================================
    public function test_scope_gallery_candidate_excludes_pending_deliverable(): void
    {
        $this->createAttachment([
            'mime_type' => 'image/jpeg',
        ], [
            'order_status' => Order::STATUS_COMPLETED,
            'deliverable_status' => Deliverable::STATUS_PENDING,
        ]);

        $candidates = MessageAttachment::galleryCandidate()->get();

        $this->assertCount(0, $candidates);
    }

    // ============================================================
    // Test #9: Scope galleryCandidate() exclude attachment yang sudah di-gallery
    // ============================================================
    public function test_scope_gallery_candidate_excludes_attachment_with_gallery(): void
    {
        // Bikin attachment yang memenuhi syarat
        $attachment = $this->createAttachment([
            'mime_type' => 'image/jpeg',
        ], [
            'order_status' => Order::STATUS_COMPLETED,
            'deliverable_status' => Deliverable::STATUS_APPROVED,
        ]);

        // Tapi sudah punya GalleryItem
        $admin = $this->createUser(User::ROLE_ADMIN);
        GalleryItem::create([
            'product_id'            => $attachment->message->conversation->order->product_id,
            'order_id'              => $attachment->message->conversation->order_id,
            'message_attachment_id' => $attachment->id,
            'title'                 => 'Sudah di-gallery',
            'image_url'             => 'gallery/existing.png',
            'is_active'             => true,
            'created_by'            => $admin->id,
        ]);

        $candidates = MessageAttachment::galleryCandidate()->get();

        $this->assertCount(0, $candidates);
    }

    // ============================================================
    // Test #10: Cast file_size ke integer
    // ============================================================
    public function test_file_size_is_cast_to_integer(): void
    {
        $attachment = $this->createAttachment([
            'file_size' => '1024', // string
        ]);

        $attachment->refresh();

        $this->assertIsInt($attachment->file_size);
        $this->assertEquals(1024, $attachment->file_size);
    }

    // ============================================================
    // HELPER
    // ============================================================
    private function createUser(string $role): User
    {
        return User::create([
            'username'  => $role . '_' . uniqid(),
            'email'     => $role . '_' . uniqid() . '@test.com',
            'full_name' => 'Test ' . ucfirst($role),
            'phone'     => '08123456789',
            'role'      => $role,
            'password'  => bcrypt('password'),
        ]);
    }

    /**
     * Bikin data berantai User → Product → Order → Conversation → Message → MessageAttachment
     */
    private function createAttachment(
        array $attachmentOverrides = [],
        array $contextOverrides = []
    ): MessageAttachment {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $designer = $this->createUser(User::ROLE_DESIGNER);

        $product = Product::create([
            'name'          => 'Test Product ' . uniqid(),
            'description'   => 'Deskripsi test',
            'thumbnail_url' => 'products/test.png',
            'price'         => 50000,
            'is_active'     => true,
        ]);

        $order = Order::create([
            'order_code'  => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => $contextOverrides['order_status'] ?? Order::STATUS_COMPLETED,
        ]);

        $conversation = Conversation::create([
            'order_id'    => $order->id,
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'status'      => Conversation::STATUS_ACTIVE,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $designer->id,
            'type'            => Message::TYPE_DELIVERABLE,
            'body'            => 'Ini hasil desain',
        ]);

        Deliverable::create([
            'order_id'    => $order->id,
            'message_id'  => $message->id,
            'status'      => $contextOverrides['deliverable_status'] ?? Deliverable::STATUS_APPROVED,
            'revision_note' => null,
            'approved_at' => ($contextOverrides['deliverable_status'] ?? Deliverable::STATUS_APPROVED) === Deliverable::STATUS_APPROVED ? now() : null,
        ]);

        return MessageAttachment::create(array_merge([
            'message_id' => $message->id,
            'file_url'   => 'chat/test.png',
            'file_name'  => 'test.png',
            'file_size'  => 1024,
            'mime_type'  => 'image/png',
            'file_type'  => 'image',
        ], $attachmentOverrides));
    }
}