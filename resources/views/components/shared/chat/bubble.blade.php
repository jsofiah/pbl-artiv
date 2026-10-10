@props([
    'msg',
    'order',
    'isMe' => false,
])

@php
    $sender      = $msg->sender;
    $deliverable = $msg->deliverable;
    $isDeliverable = $msg->type === 'deliverable';
@endphp

<div @class([
        'flex flex-col items-end' => $isMe,
        'max-w-[80%]'             => !$isMe,
    ])
    data-msg-date="{{ $msg->created_at->toDateString() }}"
>
    {{-- Header: nama + waktu --}}
    <p class="text-xs font-semibold text-slate-500 mb-1.5">
        @if ($isMe)
            {{ $msg->created_at->format('H:i') }} WIB
            <span class="font-normal text-slate-400">Anda</span>
        @else
            {{ $sender->full_name ?? 'Designer' }}
            <span class="font-normal text-slate-400">{{ $msg->created_at->format('H:i') }} WIB</span>
        @endif
    </p>

    {{-- Bubble --}}
    <div @class([
            'rounded-2xl px-4 py-3',
            'bg-[#6D28D9] text-white rounded-tr-sm' => $isMe,
            'bg-slate-50 rounded-tl-sm'             => !$isMe,
            'border-2 border-violet-200'            => $isDeliverable && !$isMe,
        ])
    >

        {{-- Body --}}
        <p @class([
                'text-sm leading-relaxed whitespace-pre-line',
                'text-white'     => $isMe,
                'text-slate-700' => !$isMe,
            ])
        >{{ $msg->body }}</p>

        {{-- Attachments --}}
        @foreach ($msg->attachments as $att)
            <x-shared.chat.attachment-card
                :att="$att"
                :order="$order"
                :theme="$isMe ? 'dark' : 'light'"
            />
        @endforeach

        {{-- Deliverable actions (hanya kalau bukan pesan sendiri & deliverable) --}}
        @if (!$isMe && $isDeliverable && $deliverable)
            <x-shared.chat.deliverable-actions
                :deliverable="$deliverable"
                :order="$order"
            />
        @endif
    </div>
</div>