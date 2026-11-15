@props([
    'document',
    'show' => false,
    'dismissible' => true,
])

@php
    $version   = config("terms.{$document}_version", '1.0');
    $termsView = config("terms.{$document}_view")
        ?? ($document === 'designer_tos' ? 'terms.designer' : 'terms.customer');
@endphp


<div x-data="termsModal({
        documentName: '{{ $document }}',
        show: {{ $show ? 'true' : 'false' }},
        dismissible: {{ $dismissible ? 'true' : 'false' }},
     })"
     x-cloak
     x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
     @if($dismissible) @click.self="close()" @endif
     style="display: none;">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">

        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 shrink-0">
            <p class="font-bold text-slate-900 text-sm">
                Ketentuan Layanan
            </p>
            @if($dismissible)
                <button type="button" @click="close()"
                        class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto px-5 py-4">
            @include($termsView)
        </div>

        <div class="px-5 py-3 border-t border-slate-100 shrink-0">
            <form @submit.prevent="accept()">
                <label class="flex items-start gap-2 mb-3 cursor-pointer">
                    <input type="checkbox" x-model="agreed" class="mt-0.5 accent-[#6D28D9]">
                    <span class="text-sm text-slate-600">
                        Saya telah membaca dan menyetujui Ketentuan Layanan di atas.
                    </span>
                </label>

                <div class="flex items-center justify-between gap-2">
                    @if($dismissible)
                        <button type="button" @click="close()"
                                class="px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-full hover:bg-slate-50 transition">
                            Nanti
                        </button>
                    @else
                        <span></span>
                    @endif

                    <button type="submit"
                            :disabled="!agreed || loading"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#6D28D9] text-white text-sm font-semibold rounded-full
                                   hover:bg-[#5B21B6] disabled:opacity-40 disabled:cursor-not-allowed transition">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="loading ? 'Menyimpan...' : 'Setujui & Lanjutkan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    window.termsModal = function ({ documentName, show = false, dismissible = true }) {
        return {
            open: show,
            agreed: false,
            loading: false,
            docName: documentName,

            init() {
                if (show) this.open = true;

                window.addEventListener('open-terms-modal', (e) => {
                    if (e.detail?.document === this.docName) {
                        this.open = true;
                        this.agreed = false;
                    }
                });
            },

            close() {
                if (!dismissible) return;
                this.open = false;
                this.agreed = false;
            },

            async accept() {
                if (!this.agreed || this.loading) return;
                this.loading = true;

                try {
                    const res = await fetch('{{ route('terms.accept') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ document: this.docName, setuju: 1 }),
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        alert(data.message || 'Gagal menyimpan persetujuan.');
                        this.loading = false;
                        return;
                    }

                    this.open = false;
                    this.loading = false;

                    window.location.reload();

                } catch (e) {
                    console.error('[terms] Fetch error:', e);
                    alert('Terjadi kesalahan: ' + (e?.message || e));
                    this.loading = false;
                }
            },
        };
    };
</script>
@endpush
@endonce