<div id="app-confirm-modal" class="hidden fixed inset-0 z-[200] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative overflow-hidden">
        <div id="app-confirm-icon" class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 bg-rose-100 text-rose-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>

        <h3 id="app-confirm-title" class="text-lg font-bold text-slate-900 text-center mb-1.5">Konfirmasi</h3>
        <p id="app-confirm-message" class="text-sm text-slate-500 text-center leading-relaxed mb-6"></p>

        <div class="flex gap-2">
            <button type="button" id="app-confirm-cancel"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Batal
            </button>
            <button type="button" id="app-confirm-ok"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
window.appConfirm = function (message, onConfirm, options = {}) {
    const modal   = document.getElementById('app-confirm-modal');
    const title   = document.getElementById('app-confirm-title');
    const msgEl   = document.getElementById('app-confirm-message');
    const iconEl  = document.getElementById('app-confirm-icon');
    const okBtn   = document.getElementById('app-confirm-ok');
    const cancel  = document.getElementById('app-confirm-cancel');

    if (!modal) return;

    title.textContent = options.title || 'Konfirmasi';
    msgEl.textContent = message;
    okBtn.textContent = options.okText || 'Ya, Lanjutkan';
    cancel.textContent = options.cancelText || 'Batal';

    const variant = options.variant || 'danger';
    const variants = {
        danger:  { bg: 'bg-rose-600 hover:bg-rose-700',    icon: 'bg-rose-100 text-rose-600' },
        primary: { bg: 'bg-[#6D28D9] hover:bg-[#5B21B6]',  icon: 'bg-violet-100 text-violet-600' },
        success: { bg: 'bg-emerald-600 hover:bg-emerald-700', icon: 'bg-emerald-100 text-emerald-600' },
    };
    const v = variants[variant] || variants.danger;

    okBtn.className = `flex-1 px-4 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm transition ${v.bg}`;
    iconEl.className = `w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 ${v.icon}`;

    modal.classList.remove('hidden');

    const newOk = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOk, okBtn);
    const newCancel = cancel.cloneNode(true);
    cancel.parentNode.replaceChild(newCancel, cancel);

    newOk.addEventListener('click', () => {
        modal.classList.add('hidden');
        if (typeof onConfirm === 'function') onConfirm();
    });
    newCancel.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    modal.onclick = (e) => {
        if (e.target === modal) modal.classList.add('hidden');
    };
};

window.appAlert = function (message, options = {}) {
    window.appConfirm(message, null, {
        ...options,
        title: options.title || 'Informasi',
        okText: options.okText || 'Mengerti',
        cancelText: 'Tutup',
        variant: options.variant || 'primary',
    });
};
</script>