@props([
    'msg',
    'order',
    'isMe' => false,
])

@php
    $sender        = $msg->sender;
    $deliverable   = $msg->deliverable;
    $isDeliverable = $msg->type === 'deliverable';
    $isDesignerPage = request()->routeIs('designer.*');
@endphp

<div @class([
        'flex flex-col items-end ' => $isMe,
        'max-w-[80%]'             => !$isMe,
    ])
    data-msg-date="{{ $msg->created_at->toDateString() }}"
    @if ($isDeliverable && $deliverable) data-deliverable-id="{{ $deliverable->id }}" @endif
>
    <p class="text-xs font-semibold text-slate-500 mb-1.5">
        @if ($isMe)
            {{ $msg->created_at->format('H:i') }} WIB
            <span class="font-normal text-slate-400">Anda</span>
        @else
            {{ $sender->full_name ?? ($isDesignerPage ? 'Customer' : 'Designer') }}
            <span class="font-normal text-slate-400">{{ $msg->created_at->format('H:i') }} WIB</span>
        @endif
    </p>

    <div @class([
            'rounded-2xl px-4 py-3 min-w-0 break-all',
            'bg-[#6D28D9] text-white rounded-tr-sm' => $isMe,
            'bg-slate-50 rounded-tl-sm'             => !$isMe,
            'border-2 border-violet-200'            => $isDeliverable && !$isMe,
        ])
    >
        @if ($isDeliverable)
            <x-shared.chat.deliverable-badge :deliverable="$deliverable" :isMe="$isMe" />
        @endif

        <p @class([
                'text-sm leading-relaxed whitespace-pre-line break-words',
                'text-white'     => $isMe,
                'text-slate-700' => !$isMe,
            ])
        >{{ $msg->body }}</p>

        @if ($msg->attachments->count() > 1)
            <div class="mt-3 flex items-center gap-2 text-xs {{ $isMe ? 'text-violet-100' : 'text-slate-500' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                </svg>
                <span class="font-semibold">{{ $msg->attachments->count() }} file</span>
            </div>
        @endif

        @foreach ($msg->attachments as $att)
            <x-shared.chat.attachment-card
                :att="$att"
                :order="$order"
                :theme="$isMe ? 'dark' : 'light'"
            />
        @endforeach

        {{-- Tombol Approve/Revisi: HANYA customer --}}
        @if (!$isMe && $isDeliverable && $deliverable && !$isDesignerPage)
            <div data-deliverable-actions>
                <x-shared.chat.deliverable-actions
                    :deliverable="$deliverable"
                    :order="$order"
                />
            </div>
        @endif

        {{-- Status: customer DAN designer --}}
        @if ($isDeliverable && $deliverable)
            <div data-deliverable-status>
                <x-shared.chat.deliverable-status
                    :deliverable="$deliverable"
                    :isDesigner="$isDesignerPage"
                    :order="$order"
                />
            </div>
        @endif
    </div>
</div>