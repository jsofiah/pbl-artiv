<script>
document.addEventListener('DOMContentLoaded', () => {
    const conversationId = '{{ $order->conversation?->id }}';
    if (!conversationId) return;

    window.Echo.private(`chat.${conversationId}`)
        .listen('.message.sent', (e) => {
            if (e.sender.id === '{{ auth()->id() }}') return;
            appendMessage(e);
        })
        .listen('.deliverable.updated', (e) => {
            updateDeliverableInChat(e);
        });
});

function appendMessage(e) {
    const container = document.getElementById('chat-container');
    if (!container) return;

    const time = new Date(e.created_at).toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit'
    });

    // ============ HANDLE SYSTEM MESSAGE ============
    if (e.type === 'system') {
        const isApproval = e.body.toLowerCase().includes('menyetujui');
        const styles = isApproval
            ? { bg: 'bg-emerald-50', border: 'border-emerald-200', title: 'text-emerald-700', body: 'text-emerald-800', label: '✅ Hasil Disetujui' }
            : { bg: 'bg-orange-50', border: 'border-orange-200', title: 'text-orange-700', body: 'text-orange-800', label: '⚠️ Revisi Diminta' };

        const notice = document.createElement('div');
        notice.className = 'max-w-[80%]';
        notice.innerHTML = `
            <p class="text-xs font-semibold text-slate-500 mb-1.5">
                ${escapeHtml(e.sender.name)}
                <span class="font-normal text-slate-400">${time} WIB</span>
            </p>
            <div class="${styles.bg} border ${styles.border} rounded-2xl rounded-tl-sm px-4 py-3">
                <p class="text-xs font-bold ${styles.title} mb-1">${styles.label}</p>
                <p class="text-sm ${styles.body} whitespace-pre-line">${escapeHtml(e.body)}</p>
            </div>
        `;
        container.appendChild(notice);
        container.scrollTop = container.scrollHeight;
        return;
    }

    // ============ HANDLE PESAN BIASA ============
    const isDeliverable = e.type === 'deliverable';
    const isMe = e.sender.id === '{{ auth()->id() }}';

    let attachmentsHtml = '';
    if (e.attachments && e.attachments.length) {
        if (e.attachments.length > 1) {
            attachmentsHtml += `
                <div class="mt-3 flex items-center gap-2 text-xs ${isMe ? 'text-violet-100' : 'text-slate-500'}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                    </svg>
                    <span class="font-semibold">${e.attachments.length} file</span>
                </div>
            `;
        }

        attachmentsHtml += e.attachments.map(att => {
            const isImage = /image\/(jpeg|jpg|png|gif|webp)/.test(att.mime_type || '');
            return `
                <div class="mt-3 ${isMe ? 'bg-white/10 border-white/20' : 'bg-white border-slate-200'} border rounded-xl p-2.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 max-w-full overflow-hidden">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-lg ${isMe ? 'bg-white/20' : 'bg-slate-100'} shrink-0 flex items-center justify-center">
                            <svg class="w-4 h-4 ${isMe ? 'text-white' : 'text-slate-400'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-6 4h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L17 7.5V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold ${isMe ? 'text-white' : 'text-slate-800'} truncate">${escapeHtml(att.file_name)}</p>
                            <p class="text-xs ${isMe ? 'text-violet-100' : 'text-slate-400'}">${formatBytes(att.file_size)}</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    let deliverableBadgeHtml = '';
    if (isDeliverable) {
        deliverableBadgeHtml = `
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-0.5 rounded-md ${isMe ? 'bg-lime-300 text-lime-900' : 'bg-violet-600 text-white'} text-[10px] font-bold">
                    DELIVERABLE
                </span>
                ${e.deliverable ? `
                    <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 text-[10px] font-bold">
                        Menunggu Review
                    </span>
                ` : ''}
            </div>
        `;
    }

    let actionsHtml = '';
    if (!isMe && isDeliverable && e.deliverable && e.deliverable.status === 'pending') {
        const eventData = {
            deliverable_id: e.deliverable.id,
            status: e.deliverable.status,
            revision_count: e.revision_count ?? 0,
            revision_limit: e.revision_limit ?? 3,
            remaining: e.remaining ?? 0,
            can_request_revision: e.can_request_revision ?? true,
        };

        actionsHtml = `<div data-deliverable-actions>${buildDeliverableActionsHtml(eventData)}</div>`;
    }

    const bubble = document.createElement('div');
    bubble.className = isMe ? 'flex flex-col items-end' : 'max-w-[80%]';

    const bubbleCls = isMe
        ? 'bg-[#6D28D9] text-white rounded-2xl rounded-tr-sm px-4 py-3'
        : `bg-slate-50 rounded-2xl rounded-tl-sm px-4 py-3 ${isDeliverable ? 'border-2 border-violet-200' : ''}`;

    bubble.innerHTML = `
        <p class="text-xs font-semibold text-slate-500 mb-1.5">
            ${isMe ? `${time} WIB <span class="font-normal text-slate-400">Anda</span>` : `${escapeHtml(e.sender.name)} <span class="font-normal text-slate-400">${time} WIB</span>`}
        </p>
        <div class="${bubbleCls}">
            ${deliverableBadgeHtml}
            ${e.body ? `<p class="text-sm ${isMe ? 'text-white' : 'text-slate-700'} leading-relaxed whitespace-pre-line">${escapeHtml(e.body)}</p>` : ''}
            ${attachmentsHtml}
            ${actionsHtml}
        </div>
    `;

    container.appendChild(bubble);
    container.scrollTop = container.scrollHeight;
}

function approveDeliverable(url) {
    if (!confirm('Setujui hasil desain ini? Pesanan akan ditandai selesai.')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';

    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function renderAttachments(e) {
    if (!e.attachments || e.attachments.length === 0) return '';

    return e.attachments.map(att => `
        <div class="mt-3 bg-white border border-slate-200 rounded-xl p-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">${escapeHtml(att.file_name)}</p>
                <p class="text-xs text-slate-400">${formatBytes(att.file_size)}</p>
            </div>
        </div>
    `).join('');
}

function showFilePreview(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const preview = document.getElementById('file-preview');
        const nameEl = document.getElementById('file-name');
        nameEl.textContent = file.name + ' (' + formatBytes(file.size) + ')';
        preview.classList.remove('hidden');
    }
}

function clearFileInput() {
    document.getElementById('attachment-input').value = '';
    document.getElementById('file-preview').classList.add('hidden');
}

function insertEmoji(emoji) {
    const input = document.getElementById('chat-input');
    const start = input.selectionStart;
    const end = input.selectionEnd;
    input.value = input.value.substring(0, start) + emoji + input.value.substring(end);
    input.selectionStart = input.selectionEnd = start + emoji.length;
    input.focus();
}

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
}

document.addEventListener('click', (e) => {
    const picker = document.getElementById('emoji-picker');
    const btn = e.target.closest('button[title="Emoji"]');
    if (picker && !picker.contains(e.target) && !btn) {
        picker.classList.add('hidden');
    }
});

async function previewImage(previewUrl, downloadUrl, title) {
    const modal = document.getElementById('image-preview-modal');
    const img   = document.getElementById('image-preview-img');
    const titleEl = document.getElementById('image-preview-title');
    const dl    = document.getElementById('image-preview-download');

    titleEl.textContent = title;
    img.src = '';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    try {
        const res = await fetch(previewUrl, {
            headers: { 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Gagal load preview');
        const data = await res.json();
        img.src = data.url;
        dl.href = downloadUrl;
    } catch (err) {
        console.error(err);
        alert('Gagal memuat gambar.');
        closeImagePreview();
    }
}

function closeImagePreview() {
    const modal = document.getElementById('image-preview-modal');
    const img   = document.getElementById('image-preview-img');
    modal.classList.add('hidden');
    img.src = '';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('image-preview-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeImagePreview();
        }
    }
});

function openRevisionModal(deliverableId) {
    const modal = document.getElementById('revision-modal');
    const form  = document.getElementById('revision-form');

    form.action = `{{ route('customer.pesanan.deliverable.revisi', ['order' => $order->id, 'deliverable' => '__ID__']) }}`
        .replace('__ID__', deliverableId);

    modal.classList.remove('hidden');
    document.getElementById('revision-note').focus();
    document.getElementById('revision-char-count').textContent = '2000';
    document.getElementById('revision-note').value = '';
}

function closeRevisionModal() {
    document.getElementById('revision-modal').classList.add('hidden');
}

function updateDeliverableInChat(e) {
    const bubble = document.querySelector(`[data-deliverable-id="${e.deliverable_id}"]`);
    if (!bubble) return;

    const badge = bubble.querySelector('[data-deliverable-badge]');
    if (badge) {
        if (e.status === 'approved') {
            badge.textContent = 'Disetujui';
            badge.className = 'inline-flex items-center gap-1.5 border font-semibold rounded-md text-[10px] px-2 py-0.5 bg-emerald-500 text-white border-transparent';
        } else if (e.status === 'revision_requested') {
            badge.textContent = 'Revisi Diminta';
            badge.className = 'inline-flex items-center gap-1.5 border font-semibold rounded-md text-[10px] px-2 py-0.5 bg-amber-400 text-amber-900 border-transparent';
        }
    }

    const actionsWrap = bubble.querySelector('[data-deliverable-actions]');
    if (actionsWrap) {
        if (e.status !== 'pending') {
            actionsWrap.innerHTML = '';
        } else {
            actionsWrap.innerHTML = buildDeliverableActionsHtml(e);
        }
    }


    const statusWrap = bubble.querySelector('[data-deliverable-status]');
    if (statusWrap) {
        if (e.event === 'revision_requested') {
            statusWrap.innerHTML = `
                <div class="mt-3 p-3 rounded-xl bg-orange-50 border border-orange-200">
                    <p class="text-xs font-bold text-orange-700 mb-1">Catatan Revisi Anda:</p>
                    <p class="text-sm text-orange-800 whitespace-pre-line">${escapeHtml(e.revision_note || '')}</p>
                </div>
            `;
        } else if (e.event === 'approved') {
            const approvedAt = e.approved_at
                ? new Date(e.approved_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                : '';
            statusWrap.innerHTML = `
                <div class="mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold text-emerald-700">
                        Anda menyetujui hasil desain ini pada ${approvedAt} WIB
                    </p>
                </div>
            `;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const note = document.getElementById('revision-note');
    const counter = document.getElementById('revision-char-count');
    if (note && counter) {
        note.addEventListener('input', () => {
            counter.textContent = 2000 - note.value.length;
        });
    }
});

function buildDeliverableActionsHtml(data) {
    const remaining = data.remaining ?? 0;
    const canRequest = data.can_request_revision ?? (remaining > 0);
    const isLastChance = remaining === 0;
    const deliverableId = data.deliverable_id;
    const orderId = getOrderId();

    let html = `
        <div class="mt-4 pt-3 border-t border-slate-200 flex flex-wrap gap-2">
            <form method="POST"
                  action="/customer/pesanan/${orderId}/deliverable/${deliverableId}/approve"
                  onsubmit="return confirm('Setujui hasil desain ini? Pesanan akan ditandai selesai.')">
                <input type="hidden" name="_token" value="${getCsrfToken()}">
                <button type="submit"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                               bg-[#D5FC55] text-neutral-900 text-sm font-bold
                               shadow-[0_2px_8px_-2px_rgba(213,252,85,0.5)]
                               hover:bg-[#c5ec45] hover:shadow-[0_4px_12px_-2px_rgba(213,252,85,0.6)]
                               hover:-translate-y-0.5 active:translate-y-0
                               transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span>Approve</span>
                </button>
            </form>
    `;

    if (canRequest) {
        html += `
            <button type="button"
                    onclick="openRevisionModal('${deliverableId}')"
                    class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                           bg-[#FBC003] text-neutral-900 text-sm font-bold
                           shadow-[0_2px_8px_-2px_rgba(251,192,3,0.5)]
                           hover:bg-[#e8b202] hover:shadow-[0_4px_12px_-2px_rgba(251,192,3,0.6)]
                           hover:-translate-y-0.5 active:translate-y-0
                           transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                <span>Minta Revisi</span>
                <span class="text-[10px] opacity-75 font-normal">(${remaining} sisa)</span>
            </button>
        `;
    }

    html += `</div>`;

    if (remaining === 1 && canRequest) {
        html += `
            <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
                <div class="text-xs text-amber-800">
                    <p class="font-semibold mb-0.5">Sisa 1 revisi gratis</p>
                    <p class="text-amber-700 leading-relaxed">
                        Gunakan dengan bijak. Setelah revisi berikutnya, Anda hanya bisa menyetujui hasil desain.
                    </p>
                </div>
            </div>
        `;
    }

    if (isLastChance) {
        html += `
            <div class="mt-3 p-3 rounded-xl bg-red-50 border border-red-200 flex items-start gap-2">
                <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
                <div class="text-xs text-red-800">
                    <p class="font-semibold mb-0.5">Revisi gratis sudah habis</p>
                    <p class="text-red-700 leading-relaxed">
                        Ini adalah hasil akhir dari desainer. Anda hanya bisa <strong>Approve</strong> untuk menyelesaikan pesanan.
                        Jika masih butuh revisi tambahan, silakan hubungi admin.
                    </p>
                </div>
            </div>
        `;
    }

    return html;
}

function getOrderId() {
    const match = window.location.pathname.match(/\/customer\/pesanan\/([^/]+)/);
    return match ? match[1] : '{{ $order->id }}';
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}';
}

let currentApproveDeliverableId = null;

function openApproveModal(deliverableId) {
    currentApproveDeliverableId = deliverableId;
    document.getElementById('approve-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeApproveModal() {
    currentApproveDeliverableId = null;
    document.getElementById('approve-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function submitApprove() {
    if (!currentApproveDeliverableId) return;

    const form = document.getElementById('approve-form-' + currentApproveDeliverableId);
    if (form) {
        form.submit();
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('approve-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeApproveModal();
        }
    }
});

async function previewRefundProof(previewUrl) {
    const modal   = document.getElementById('modal-refund-proof');
    const img     = document.getElementById('refund-proof-img');
    const pdf     = document.getElementById('refund-proof-pdf');
    const loading = document.getElementById('refund-proof-loading');
    const nameEl  = document.getElementById('refund-proof-name');

    if (!modal) return;

    // Reset
    img.classList.add('hidden');
    pdf.classList.add('hidden');
    pdf.src = '';
    img.src = '';
    loading.classList.remove('hidden');
    nameEl.textContent = '';

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    try {
        const res = await fetch(previewUrl, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Gagal load bukti');
        const data = await res.json();

        loading.classList.add('hidden');
        nameEl.textContent = data.name || '';

        if (data.is_pdf) {
            pdf.src = data.url;
            pdf.classList.remove('hidden');
        } else {
            img.src = data.url;
            img.classList.remove('hidden');
        }
    } catch (err) {
        console.error(err);
        loading.innerHTML = '<span class="text-rose-500 text-sm">Gagal memuat bukti transfer.</span>';
    }
}

function closeRefundProof() {
    const modal = document.getElementById('modal-refund-proof');
    const img   = document.getElementById('refund-proof-img');
    const pdf   = document.getElementById('refund-proof-pdf');
    if (!modal) return;
    modal.classList.add('hidden');
    img.src = '';
    pdf.src = '';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modal-refund-proof');
        if (modal && !modal.classList.contains('hidden')) closeRefundProof();
    }
});
</script>