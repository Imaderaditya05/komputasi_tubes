/**
 * Halaman Restaurant Management: modal validasi mitra, tolak (locked), edit detail.
 */

const BADGE_BY_STATUS = {
    active: {
        label: '✓ Unlocked',
        classes: 'bg-emerald-500 text-white shadow',
    },
    pending: {
        label: 'Pending',
        classes: 'bg-amber-500 text-white shadow',
    },
    locked: {
        label: '🔒 Locked',
        classes: 'bg-red-500 text-white shadow',
    },
};

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function mitraAccessUrl(base, id) {
    return `${base.replace(/\/$/, '')}/${id}/mitra-access`;
}

function showToast(message, variant = 'success') {
    const host = document.getElementById('admin-mitra-toast');
    if (!host) return;
    const el = document.createElement('div');
    el.className =
        variant === 'error'
            ? 'rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-800 shadow-lg'
            : 'rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-lg';
    el.textContent = message;
    host.appendChild(el);
    window.setTimeout(() => {
        el.remove();
    }, 4200);
}

function applyMitraStatusToCard(card, status) {
    if (!card) return;
    const b = BADGE_BY_STATUS[status] || BADGE_BY_STATUS.pending;
    const badge = card.querySelector('[data-mitra-badge]');
    if (badge) {
        badge.textContent = b.label;
        badge.className = `mitra-status-badge absolute right-3 top-3 rounded-full px-3 py-1 text-xs font-black shadow ${b.classes}`;
    }
    card.setAttribute('data-mitra-status', status);
    const vBtn = card.querySelector('.btn-validasi-mitra');
    if (vBtn) vBtn.setAttribute('data-current-status', status);
}

async function patchMitraStatus(base, id, status, submitBtn) {
    const token = csrfToken();
    const url = mitraAccessUrl(base, id);
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.dataset.labelDefault = submitBtn.dataset.labelDefault || submitBtn.textContent;
        submitBtn.textContent = 'Menyimpan…';
    }
    try {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ status }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg =
                data?.message ||
                (data?.errors && Object.values(data.errors).flat().join(' ')) ||
                `Gagal menyimpan (HTTP ${res.status}).`;
            showToast(msg, 'error');
            return { ok: false };
        }
        if (data?.message) {
            showToast(data.message, 'success');
        }
        return { ok: true, status: data?.restaurant?.status || status };
    } catch {
        showToast('Jaringan bermasalah. Coba lagi.', 'error');
        return { ok: false };
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            if (submitBtn.dataset.labelDefault) {
                submitBtn.textContent = submitBtn.dataset.labelDefault;
            }
        }
    }
}

function openEditDialog(adminBase, d) {
    const f = document.getElementById('form-edit');
    if (!f) return;
    const base = adminBase.replace(/\/$/, '');
    f.action = `${base}/${d.id}`;
    const set = (id, v) => {
        const el = document.getElementById(id);
        if (el) el.value = v ?? '';
    };
    set('edit-name', d.name || '');
    set('edit-owner_name', d.owner_name || '');
    set('edit-location', d.location || '');
    set('edit-address_line', d.address_line || '');
    set('edit-image_url', d.image_url || '');
    set('edit-description', d.description || '');
    set('edit-rating', d.rating ?? '');
    set('edit-reviews', d.reviews ?? '');
    set('edit-box_title', d.box_title || '');
    set('edit-box_price', d.box_price || '');
    const st = document.getElementById('edit-status');
    if (st) st.value = d.status || 'pending';
    document.getElementById('dlg-edit')?.showModal();
}

export function initAdminRestaurantsPage() {
    const grid = document.getElementById('rt-restaurants-grid');
    if (!grid) return;

    const adminBase = grid.getAttribute('data-admin-rest-base') || '';

    const dlg = document.getElementById('dlg-mitra-validasi');
    const dlgReject = document.getElementById('dlg-mitra-reject-lock');
    const rejectNameEl = document.getElementById('mitra-reject-name');
    const btnRejectConfirm = document.getElementById('mitra-reject-confirm');
    /** @type {{ id: string, patchBase: string, cardKey: string, triggerBtn: HTMLButtonElement | null } | null} */
    let pendingReject = null;

    const form = document.getElementById('form-mitra-validasi');
    const subtitle = document.getElementById('mitra-modal-subtitle');
    const select = document.getElementById('mitra-modal-status');
    const hiddenId = document.getElementById('mitra-modal-restaurant-id');
    const hiddenPatchBase = document.getElementById('mitra-modal-patch-base');
    const hiddenCardKey = document.getElementById('mitra-modal-card-key');
    const submitBtn = document.getElementById('mitra-modal-submit');

    dlgReject?.addEventListener('close', () => {
        pendingReject = null;
    });
    dlgReject?.querySelectorAll('[data-close-mitra-reject]').forEach((b) => {
        b.addEventListener('click', () => dlgReject?.close());
    });
    btnRejectConfirm?.addEventListener('click', async () => {
        if (!pendingReject) return;
        const { id, patchBase, cardKey, triggerBtn } = pendingReject;
        btnRejectConfirm.disabled = true;
        const prevLabel = btnRejectConfirm.textContent;
        btnRejectConfirm.textContent = 'Menyimpan…';
        if (triggerBtn) triggerBtn.disabled = true;
        try {
            const r = await patchMitraStatus(patchBase, id, 'locked', null);
            if (r.ok) {
                const card = grid.querySelector(`[data-admin-mitra-card][data-card-key="${cardKey}"]`);
                applyMitraStatusToCard(card, r.status || 'locked');
                dlgReject?.close();
            }
        } finally {
            btnRejectConfirm.disabled = false;
            btnRejectConfirm.textContent = prevLabel || 'Kunci akses';
            if (triggerBtn) triggerBtn.disabled = false;
        }
    });

    grid.addEventListener('click', (e) => {
        const validasi = e.target.closest('.btn-validasi-mitra');
        if (validasi && grid.contains(validasi)) {
            const id = validasi.getAttribute('data-restaurant-id');
            const name = validasi.getAttribute('data-restaurant-name') || '';
            const cur = validasi.getAttribute('data-current-status') || 'pending';
            const patchBase = validasi.getAttribute('data-patch-base') || '';
            const cardKey = validasi.getAttribute('data-card-key') || '';
            if (subtitle) subtitle.textContent = name;
            if (hiddenId) hiddenId.value = id || '';
            if (hiddenPatchBase) hiddenPatchBase.value = patchBase;
            if (hiddenCardKey) hiddenCardKey.value = cardKey;
            if (select) select.value = ['active', 'pending', 'locked'].includes(cur) ? cur : 'pending';
            dlg?.showModal();
            return;
        }

        const reject = e.target.closest('.btn-reject-mitra');
        if (reject && grid.contains(reject)) {
            e.preventDefault();
            e.stopPropagation();
            const id = reject.getAttribute('data-restaurant-id');
            const rname = reject.getAttribute('data-restaurant-name') || 'mitra ini';
            const patchBase = reject.getAttribute('data-patch-base') || '';
            const cardKey = reject.getAttribute('data-card-key') || '';
            if (!id || !patchBase) return;
            pendingReject = { id, patchBase, cardKey, triggerBtn: reject };
            if (rejectNameEl) rejectNameEl.textContent = rname;
            dlgReject?.showModal();
            return;
        }

        const edit = e.target.closest('.edit-btn');
        if (edit && grid.contains(edit)) {
            try {
                const d = JSON.parse(edit.getAttribute('data-json') || '{}');
                openEditDialog(adminBase, d);
            } catch {
                showToast('Data edit tidak valid.', 'error');
            }
        }
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = hiddenId?.value;
        const status = select?.value;
        const patchBase = hiddenPatchBase?.value || '';
        const cardKey = hiddenCardKey?.value || '';
        if (!id || !status || !patchBase) return;
        const card = cardKey
            ? grid.querySelector(`[data-admin-mitra-card][data-card-key="${cardKey}"]`)
            : grid.querySelector(`[data-admin-mitra-card][data-restaurant-id="${id}"]`);
        const result = await patchMitraStatus(patchBase, id, status, submitBtn);
        if (result.ok) {
            applyMitraStatusToCard(card, result.status || status);
            dlg?.close();
        }
    });

    document.getElementById('mitra-modal-cancel')?.addEventListener('click', () => dlg?.close());
}
