@php
    $customer = $order->customer;
    $conv     = $order->conversation;

    $isChatOpen   = $order->isChatOpen();
    $chatClosesAt = $order->chatClosesAt();

    $canSendDeliverable = in_array($order->status, ['in_progress', 'revision_needed']);
    $canUploadFile = $isChatOpen && in_array($order->status, [
        'in_progress',
        'revision_needed',
        'pending',
        'waiting_designer',
    ]);
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-[calc(100vh-200px)] min-h-[500px]">

    {{-- Header chat --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <x-shared.ui.avatar :user="$customer" size="md" />
            <div>
                <p class="font-bold text-slate-900 leading-tight">
                    {{ $customer->full_name ?? 'Customer' }}
                </p>
                <p class="text-xs text-slate-500">Customer</p>
            </div>
        </div>
    </div>

    {{-- Isi chat --}}
    <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden px-6 py-5 space-y-4" id="chat-container">
        @php $lastDate = null; @endphp

        @forelse ($conv?->messages ?? [] as $msg)
            @php
                $currentDate = $msg->created_at->toDateString();
                $showDivider = $lastDate !== $currentDate;
                $lastDate = $currentDate;
                $isMe = $msg->sender_id === auth()->id();
            @endphp

            @if ($showDivider)
                <x-shared.chat.date-divider :date="$msg->created_at" />
            @endif

            <x-shared.chat.bubble :msg="$msg" :order="$order" :isMe="$isMe" />
        @empty
            <div class="flex items-center justify-center h-full">
                <p class="text-sm text-slate-400">Belum ada pesan.</p>
            </div>
        @endforelse
    </div>

    {{-- Error notif --}}
    <div class="px-4">
        @if ($errors->any())
            <x-shared.ui.error-notif :errors="$errors" />
        @endif
    </div>

    @if ($isChatOpen)

        {{-- Grace period banner --}}
        @if ($order->status === 'completed' && $chatClosesAt)
            <x-shared.chat.grace-banner :chatClosesAt="$chatClosesAt" />
        @endif

        {{-- Kirim Hasil Bar --}}
        <x-designer.pekerjaan.kirim-hasil-bar
            :can-send="$canSendDeliverable"
            :status="$order->status" />

        {{-- Form chat --}}
        @include('designer.pekerjaan.partials.chat-form', [
            'isChatOpen' => $isChatOpen,
            'canUploadFile' => $canUploadFile,
        ])

    @else
        <x-shared.chat.closed-notice :status="$order->status" />
    @endif

</div>