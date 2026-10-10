<script>
document.addEventListener('DOMContentLoaded', () => {
    const conversationId = '{{ $order->conversation?->id }}';
    if (!conversationId) return;

    window.Echo.private(`chat.${conversationId}`)
        .listen('.message.sent', (e) => {
            if (e.sender.id === '{{ auth()->id() }}') return;
            appendMessage(e);
        });
});

function appendMessage(e) {
    const container = document.getElementById('chat-container');
    if (!container) return;

    const time = new Date(e.created_at).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });

    const bubble = document.createElement('div');
    bubble.className = 'max-w-[80%]';
    bubble.setAttribute('data-msg-date', e.created_at.split('T')[0]);

    bubble.innerHTML = `
        <p class="text-xs font-semibold text-slate-500 mb-1.5">
            ${escapeHtml(e.sender.name)}
            <span class="font-normal text-slate-400">${time} WIB</span>
        </p>
        <div class="bg-slate-50 rounded-2xl rounded-tl-sm px-4 py-3">
            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">${escapeHtml(e.body || '')}</p>
            ${renderAttachments(e)}
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

document.addEventListener('DOMContentLoaded', () => {
    const note = document.getElementById('revision-note');
    const counter = document.getElementById('revision-char-count');
    if (note && counter) {
        note.addEventListener('input', () => {
            counter.textContent = 2000 - note.value.length;
        });
    }
});
</script>