@extends($layout)

@section('title', 'Notifikasi')

@section('content')
@php
    $tabs = ['all' => 'Semua', 'unread' => 'Belum dibaca'];
    if (auth()->user()->isAdmin()) {
        $tabs += ['refund' => 'Refund', 'payment' => 'Pembayaran'];
    }
@endphp

<div class="mx-auto max-w-3xl">
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Notifikasi</h1>
            <p class="text-sm text-slate-500">{{ $unreadCount }} belum dibaca</p>
        </div>
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifikasi.read-all') }}">
                @csrf
                <button type="submit" class="rounded-full border border-[#6D28D9] px-4 py-2 text-sm font-semibold text-[#6D28D9] hover:bg-violet-50">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('notifikasi.index', ['filter' => $key]) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium transition
                      {{ $filter === $key ? 'bg-[#6D28D9] text-white' : 'bg-white text-slate-600 hover:bg-violet-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        @forelse ($items as $n)
            @php [$iconClass, $iconPath] = $n->uiIcon(); @endphp
            <a href="{{ route('notifikasi.read', $n) }}"
               class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 transition last:border-b-0 hover:bg-slate-50 {{ $n->isRead() ? '' : 'bg-violet-50/60' }}">

                <span class="mt-4 h-2 w-2 shrink-0 rounded-full {{ $n->isRead() ? 'bg-transparent' : 'bg-slate-900' }}"></span>

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $iconClass }}">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm {{ $n->isRead() ? 'font-medium' : 'font-bold' }} text-slate-900">{{ $n->title }}</p>
                    @if ($n->body)
                        <p class="mt-0.5 text-sm text-slate-600">{{ $n->body }}</p>
                    @endif
                    <p class="mt-1 text-xs text-slate-400">
                        {{ $n->created_at->locale('id')->translatedFormat('d M Y, H:i') }}
                        · {{ $n->created_at->locale('id')->diffForHumans() }}
                    </p>
                </div>
            </a>
        @empty
            <div class="px-5 py-16 text-center text-sm text-slate-500">Belum ada notifikasi.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
@endsection