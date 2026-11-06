@props([
    'deliverable',
    'order',
    'routePrefix' => null,
])

@php
    if (!$routePrefix) {
        $routePrefix = request()->routeIs('designer.*') ? 'designer' : 'customer';
    }

    $approveRoute = "{$routePrefix}.pesanan.deliverable.approve";
    $revisiRoute  = "{$routePrefix}.pesanan.deliverable.revisi";

    $limit           = \App\Models\Order::FREE_REVISION_LIMIT;
    $used            = $order->revision_count;
    $remaining       = max(0, $limit - $used);
    $hasFreeQuota    = $order->canRequestFreeRevision();
    $hasPaidQuota    = $order->canRequestPaidRevision();
    $needsPayment    = $order->needsPaidRevisionPayment();
    $isCustomerSide  = $routePrefix === 'customer';
@endphp

@if ($deliverable->status === 'pending')
    <div class="mt-4 pt-3 border-t border-slate-200 flex flex-wrap gap-2">

        {{-- Form hidden untuk submit approve --}}
        <form method="POST"
            id="approve-form-{{ $deliverable->id }}"
            action="{{ route($approveRoute, ['order' => $order->id, 'deliverable' => $deliverable->id]) }}"
            class="hidden">
            @csrf
        </form>

        {{-- Tombol Approve --}}
        <button type="button"
                onclick="openApproveModal('{{ $deliverable->id }}')"
                class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                    bg-[#D5FC55] text-neutral-900 text-sm font-bold
                    shadow-[0_2px_8px_-2px_rgba(213,252,85,0.5)]
                    hover:bg-[#c5ec45] hover:shadow-[0_4px_12px_-2px_rgba(213,252,85,0.6)]
                    hover:-translate-y-0.5 active:translate-y-0
                    transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            <span>Approve</span>
        </button>

        @if ($isCustomerSide)

            @if ($hasFreeQuota || $hasPaidQuota)
                <button type="button"
                        onclick="document.getElementById('revision-form-{{ $deliverable->id }}').classList.toggle('hidden')"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                               {{ $hasFreeQuota ? 'bg-[#FBC003]' : 'bg-[#6D28D9] text-white' }}
                               {{ $hasFreeQuota ? 'text-neutral-900' : '' }}
                               text-sm font-bold
                               shadow-[0_2px_8px_-2px_rgba(251,192,3,0.5)]
                               hover:-translate-y-0.5 active:translate-y-0
                               transition-all duration-200
                               {{ $hasFreeQuota ? 'hover:bg-[#e8b202] hover:shadow-[0_4px_12px_-2px_rgba(251,192,3,0.6)]' : 'hover:bg-[#5B21B6]' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:rotate-180">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <span>Minta Revisi</span>
                    @if ($hasFreeQuota)
                        <span class="text-[10px] opacity-75 font-normal">({{ $remaining }} sisa gratis)</span>
                    @else
                        <span class="text-[10px] opacity-90 font-normal">({{ $order->paid_revision_quota }}x berbayar)</span>
                    @endif
                </button>

            @elseif ($needsPayment)
                <button type="button"
                        onclick="openRevisionPaymentModal()"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                               bg-gradient-to-r from-rose-500 to-rose-600 text-white text-sm font-bold
                               shadow-[0_2px_8px_-2px_rgba(244,63,94,0.5)]
                               hover:from-rose-600 hover:to-rose-700 hover:shadow-[0_4px_12px_-2px_rgba(244,63,94,0.6)]
                               hover:-translate-y-0.5 active:translate-y-0
                               transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                    </svg>
                    <span>Beli Kuota Revisi</span>
                </button>
            @endif

        @endif
    </div>

    @if ($isCustomerSide && ($hasFreeQuota || $hasPaidQuota))
        <div id="revision-form-{{ $deliverable->id }}" class="hidden mt-3">
            <form method="POST"
                  action="{{ route($revisiRoute, ['order' => $order->id, 'deliverable' => $deliverable->id]) }}"
                  class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                @csrf

                <div class="flex items-start gap-2 mb-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-900">Jelaskan revisi yang Anda inginkan</p>
                        <p class="text-xs text-amber-700 mt-0.5">
                            @if ($hasFreeQuota)
                                Revisi ini menggunakan kuota gratis (sisa {{ $remaining }}x).
                            @else
                                Revisi ini menggunakan kuota berbayar (sisa {{ $order->paid_revision_quota }}x).
                            @endif
                        </p>
                    </div>
                </div>

                <textarea name="revision_note"
                          required
                          minlength="5"
                          maxlength="2000"
                          rows="3"
                          placeholder="Contoh: Tolong ubah warna background jadi lebih cerah, dan ganti font judul..."
                          class="w-full text-sm border border-amber-200 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none bg-white"></textarea>

                @error('revision_note')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 mt-3">
                    <button type="button"
                            onclick="document.getElementById('revision-form-{{ $deliverable->id }}').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-lg shadow-sm transition">
                        Kirim Revisi
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if ($hasFreeQuota && $remaining === 1)
        <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2">
            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <div class="text-xs text-amber-800">
                <p class="font-semibold mb-0.5">Sisa 1 revisi gratis</p>
                <p class="text-amber-700 leading-relaxed">
                    Gunakan dengan bijak. Setelah ini, revisi berikutnya memerlukan biaya tambahan.
                </p>
            </div>
        </div>
    @endif

    @if ($hasPaidQuota)
        <div class="mt-3 p-3 rounded-xl bg-violet-50 border border-violet-200 flex items-start gap-2">
            <svg class="w-4 h-4 text-violet-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            <div class="text-xs text-violet-800">
                <p class="font-semibold mb-0.5">Kuota berbayar aktif</p>
                <p class="text-violet-700 leading-relaxed">
                    Anda punya <strong>{{ $order->paid_revision_quota }}x revisi berbayar</strong> yang bisa digunakan.
                </p>
            </div>
        </div>
    @endif

    @if ($needsPayment)
        <div class="mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <div class="text-xs text-rose-800">
                <p class="font-semibold mb-0.5">Revisi gratis habis</p>
                <p class="text-rose-700 leading-relaxed">
                    Anda bisa <strong>Approve</strong> untuk menyelesaikan pesanan,
                    atau <strong>beli kuota revisi tambahan</strong> jika masih perlu revisi lagi.
                </p>
            </div>
        </div>
    @endif
@endif