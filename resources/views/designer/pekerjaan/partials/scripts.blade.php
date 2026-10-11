@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const conversationId = '{{ $order->conversation?->id }}';

    const emojiToggle = document.getElementById('emoji-toggle');
    const emojiPicker = document.getElementById('emoji-picker');
    if (emojiToggle && emojiPicker) {
        emojiToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            emojiPicker.classList.toggle('hidden');
        });
    }

    document.addEventListener('click', (e) => {
        if (!emojiPicker || emojiPicker.classList.contains('hidden')) return;
        if (!emojiPicker.contains(e.target) && !emojiToggle?.contains(e.target)) {
            emojiPicker.classList.add('hidden');
        }
    });

    const container = document.getElementById('chat-container');
    if (container) container.scrollTop = container.scrollHeight;

    // Realtime
    if (!conversationId || !window.Echo) return;

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

    let attachmentsHtml = '';
    if (e.attachments && e.attachments.length) {
        // Counter kalau lebih dari 1
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

    const bubble = document.createElement('div');
    bubble.className = 'max-w-[60%]';
    bubble.innerHTML = `
        <p class="text-xs font-semibold text-slate-500 mb-1.5">
            ${escapeHtml(e.sender.full_name)}
            <span class="font-normal text-slate-400">${time} WIB</span>
        </p>
        <div class="bg-slate-50 rounded-2xl rounded-tl-sm px-4 py-3">
            ${e.body ? `<p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">${escapeHtml(e.body)}</p>` : ''}
            ${attachmentsHtml}
        </div>
    `;
    container.appendChild(bubble);
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function showFilePreview(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('file-name').textContent =
            file.name + ' (' + formatBytes(file.size) + ')';
        document.getElementById('file-preview').classList.remove('hidden');
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
    const picker = document.getElementById('emoji-picker');
    if (picker) picker.classList.add('hidden');
}

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
}

async function previewImage(previewUrl, downloadUrl, title) {
    const modal   = document.getElementById('image-preview-modal');
    const img     = document.getElementById('image-preview-img');
    const titleEl = document.getElementById('image-preview-title');
    const dl      = document.getElementById('image-preview-download');

    titleEl.textContent = title;
    img.src = '';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    try {
        const res = await fetch(previewUrl, { headers: { 'Accept': 'application/json' } });
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

function updateDeliverableInChat(e) {
    console.log('=== DELIVERABLE UPDATED (designer) ===', e);

    // ============ 1. UPDATE BADGE BUBBLE ============
    const bubble = document.querySelector(`[data-deliverable-id="${e.deliverable_id}"]`);
    if (bubble) {
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

        // Update status area (catatan revisi / approved)
        const statusWrap = bubble.querySelector('[data-deliverable-status]');
        if (statusWrap) {
            if (e.event === 'revision_requested') {
                statusWrap.innerHTML = `
                    <div class="mt-3 p-3 rounded-xl bg-orange-50 border border-orange-200">
                        <p class="text-xs font-bold text-orange-700 mb-1">Catatan Revisi dari Customer:</p>
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
                            Disetujui customer pada ${approvedAt} WIB
                        </p>
                    </div>
                `;
            }
        }
    }

    // ============ 2. UPDATE TOMBOL "KIRIM HASIL" & FORM ============
    updateDesignerButtons(e);
}

// ============ UPDATE TOMBOL BERDASARKAN ORDER STATUS ============
function updateDesignerButtons(e) {
    const orderStatus = e.order_status;
    if (!orderStatus) return;  // kalau event tidak kirim order_status, skip

    // Cek apakah boleh kirim deliverable
    const canSendDeliverable = ['in_progress', 'revision_needed'].includes(orderStatus);

    // Cek apakah boleh upload file
    const canUploadFile = !['completed', 'cancelled', 'refunded'].includes(orderStatus)
        && ['in_progress', 'revision_needed', 'pending', 'waiting_designer'].includes(orderStatus);

    // ============ TOMBOL KIRIM HASIL ============
    const kirimHasilBtn = document.getElementById('kirim-hasil-btn');
    const kirimHasilInfo = document.getElementById('kirim-hasil-info');
    const kirimHasilText = document.getElementById('kirim-hasil-text');

    if (kirimHasilBtn) {
        kirimHasilBtn.disabled = !canSendDeliverable;
        kirimHasilBtn.dataset.canSend = canSendDeliverable ? '1' : '0';

        if (canSendDeliverable) {
            kirimHasilBtn.className = 'inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition bg-[#6D28D9] text-white hover:bg-[#5B21B6] cursor-pointer';
            kirimHasilBtn.setAttribute('onclick', "document.getElementById('deliverable-modal').classList.remove('hidden')");
        } else {
            kirimHasilBtn.className = 'inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition bg-slate-200 text-slate-400 cursor-not-allowed';
            kirimHasilBtn.removeAttribute('onclick');
        }
    }

    if (kirimHasilInfo) {
        if (canSendDeliverable) {
            kirimHasilInfo.className = 'flex items-center gap-2 text-xs text-violet-700';
        } else {
            kirimHasilInfo.className = 'flex items-center gap-2 text-xs text-slate-400';
        }
    }

    if (kirimHasilText) {
        if (orderStatus === 'revision_needed') {
            kirimHasilText.textContent = 'Customer minta revisi. Kirim hasil perbaikan.';
        } else if (orderStatus === 'in_progress') {
            kirimHasilText.textContent = 'Sudah selesai mengerjakan?';
        } else if (orderStatus === 'deliverable_sent') {
            kirimHasilText.textContent = 'Menunggu review customer.';
        } else if (orderStatus === 'completed') {
            kirimHasilText.textContent = 'Pesanan selesai.';
        } else {
            kirimHasilText.textContent = 'Belum waktunya kirim hasil.';
        }
    }

    // ============ TOMBOL LAMPIRAN ============
    const lampiranBtn = document.getElementById('lampiran-btn');
    const attachmentInput = document.getElementById('attachment-input');

    if (lampiranBtn) {
        lampiranBtn.disabled = !canUploadFile;

        if (canUploadFile) {
            lampiranBtn.className = 'shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition hover:bg-slate-100 text-slate-500 hover:text-[#6D28D9] cursor-pointer';
            lampiranBtn.setAttribute('onclick', "document.getElementById('attachment-input').click()");
            lampiranBtn.title = 'Lampirkan file';
        } else {
            lampiranBtn.className = 'shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition text-slate-300 cursor-not-allowed';
            lampiranBtn.removeAttribute('onclick');
            lampiranBtn.title = 'Tidak bisa upload file sekarang';
        }
    }

    if (attachmentInput) {
        attachmentInput.disabled = !canUploadFile;
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('image-preview-modal');
        if (modal && !modal.classList.contains('hidden')) closeImagePreview();
    }
});

function updateDeliverablePreview(input) {
    const preview = document.getElementById('deliverable-files-preview');
    const list    = document.getElementById('deliverable-files-list');
    const counter = document.getElementById('deliverable-files-count');

    const files = input.files;

    if (!files || files.length === 0) {
        preview.classList.add('hidden');
        return;
    }

    preview.classList.remove('hidden');
    counter.textContent = `${files.length} file`;

    list.innerHTML = Array.from(files).map((file, i) => `
        <div class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2 text-xs">
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="w-5 h-5 rounded-full bg-violet-100 text-[#6D28D9] flex items-center justify-center text-[10px] font-bold shrink-0">
                    ${i + 1}
                </span>
                <span class="text-slate-700 truncate font-medium">${escapeHtml(file.name)}</span>
            </div>
            <span class="text-slate-400 shrink-0 ml-2">${formatBytes(file.size)}</span>
        </div>
    `).join('');
}
</script>
@endpush