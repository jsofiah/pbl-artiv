@if ($order->brief_note)
    <x-shared.modals.base
        id="brief-modal"
        title="Brief Pekerjaan"
        :subtitle="($order->product->name ?? '-') . ' · ' . $order->order_code"
        size="lg">

        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
            {{ $order->brief_note }}
        </p>

        <x-slot:footer>
            <x-shared.ui.button
                variant="primary"
                onclick="document.getElementById('brief-modal').classList.add('hidden')">
                Tutup
            </x-shared.ui.button>
        </x-slot:footer>
    </x-shared.modals.base>
@endif

@include('designer.pekerjaan.partials.modal-kirim-deliverable')
@include('designer.pekerjaan.partials.modal-image-preview')