/**
 * Admin User Management: add / edit / delete dengan refresh tabel via API live.
 */

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function showUserToast(message, variant = 'success') {
    const host = document.getElementById('admin-users-toast');
    if (!host) return;
    const el = document.createElement('div');
    el.className =
        variant === 'error'
            ? 'pointer-events-auto rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-800 shadow-lg'
            : 'pointer-events-auto rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-lg';
    el.textContent = message;
    host.appendChild(el);
    window.setTimeout(() => el.remove(), 4200);
}

function errFromJson(data) {
    if (!data) return 'Permintaan gagal.';
    if (data.message) return data.message;
    if (data.errors) return Object.values(data.errors).flat().join(' ');
    return 'Terjadi kesalahan.';
}

function readCfg() {
    const el = document.getElementById('admin-users-config');
    const usersPath = '/admin/users';
    const livePath = '/admin/api/live/users';
    if (!el) {
        return { storeUrl: usersPath, usersBase: usersPath, liveUrl: livePath };
    }
    const normalize = (path) => String(path || '').replace(/\/+$/, '') || usersPath;
    return {
        storeUrl: normalize(el.dataset.storeUrl || usersPath),
        usersBase: normalize(el.dataset.usersBase || usersPath),
        liveUrl: normalize(el.dataset.liveUrl || livePath),
    };
}

function setTextIfPresent(id, text) {
    const node = document.getElementById(id);
    if (node) node.textContent = text;
}

async function refreshUsersFromLive() {
    const cfg = readCfg();
    if (!cfg?.liveUrl) return;
    const qs = window.location.search || '';
    const res = await fetch(`${cfg.liveUrl}${qs}`, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });
    if (!res.ok) return;
    const data = await res.json();
    if (data.stats) {
        setTextIfPresent('rt-user-total', new Intl.NumberFormat('id-ID').format(data.stats.total));
        setTextIfPresent('rt-user-customers', new Intl.NumberFormat('id-ID').format(data.stats.customers));
        setTextIfPresent('rt-user-sellers', new Intl.NumberFormat('id-ID').format(data.stats.sellers));
        setTextIfPresent('rt-user-active', new Intl.NumberFormat('id-ID').format(data.stats.active));
        const pendingMitra = Number(data.stats.pending_mitra) || 0;
        setTextIfPresent('rt-pending-mitra-count', new Intl.NumberFormat('id-ID').format(pendingMitra));
        const ban = document.getElementById('rt-pending-mitra-banner');
        if (ban && typeof pendingMitra === 'number') {
            ban.classList.toggle('hidden', pendingMitra <= 0);
        }
    }
    const tbody = document.getElementById('rt-users-tbody');
    if (tbody && data.tbody_html != null) {
        tbody.innerHTML = data.tbody_html;
    }
    const pag = document.getElementById('rt-users-pagination');
    if (pag && data.pagination_html != null) {
        pag.innerHTML = data.pagination_html;
    }
    if (data.updated_at && typeof window !== 'undefined') {
        const clock = document.getElementById('rt-live-clock');
        if (clock) {
            try {
                const d = new Date(data.updated_at);
                clock.textContent = d.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                });
            } catch {
                /* ignore */
            }
        }
    }
}

/**
 * @param {HTMLFormElement} form
 * @param {string} url
 */
async function submitJsonForm(form, url, { method = 'POST', extraFields = {} } = {}) {
    const token = csrfToken();
    const fd = new FormData(form);
    Object.entries(extraFields).forEach(([k, v]) => fd.set(k, v));

    const res = await fetch(url, {
        method,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token,
        },
        credentials: 'same-origin',
        body: fd,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        showUserToast(errFromJson(data), 'error');
        return false;
    }
    if (data.message) showUserToast(data.message, 'success');
    return true;
}

export function initAdminUsersPage() {
    if (!document.getElementById('admin-users-config')) {
        return;
    }
    const cfg = readCfg();
    const dlgAdd = document.getElementById('dlg-user-add');
    const dlgEdit = document.getElementById('dlg-user-edit');
    const formAdd = document.getElementById('form-user-add');
    const formEdit = document.getElementById('form-user-edit');
    const roleFilter = document.querySelector('[data-users-role-filter]');
    const filterForm = document.getElementById('form-users-filter');

    roleFilter?.addEventListener('change', () => filterForm?.requestSubmit());

    document.getElementById('btn-open-add-user')?.addEventListener('click', () => {
        formAdd?.reset();
        const roleSel = formAdd?.querySelector('select[name="role"]');
        if (roleSel) {
            roleSel.value = '';
        }
        const activeSel = formAdd?.querySelector('select[name="is_active"]');
        if (activeSel) activeSel.value = '1';
        dlgAdd?.showModal();
    });

    dlgAdd?.querySelectorAll('[data-close-add-user]').forEach((b) => {
        b.addEventListener('click', () => dlgAdd?.close());
    });
    dlgEdit?.querySelectorAll('[data-close-edit-user]').forEach((b) => {
        b.addEventListener('click', () => dlgEdit?.close());
    });

    formAdd?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const ok = await submitJsonForm(formAdd, cfg.storeUrl, { method: 'POST' });
        if (!ok) return;
        dlgAdd?.close();
        await refreshUsersFromLive();
    });

    formEdit?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const action = formEdit.getAttribute('action');
        if (!action) return;
        const ok = await submitJsonForm(formEdit, action, { method: 'POST' });
        if (!ok) return;
        dlgEdit?.close();
        await refreshUsersFromLive();
    });

    const tbody = document.getElementById('rt-users-tbody');

    function openEditFromButton(editBtn) {
        const id = editBtn.dataset.userId;
        if (!id || !formEdit || !dlgEdit) return;
        const row = {
            id,
            name: editBtn.dataset.userName || '',
            email: editBtn.dataset.userEmail || '',
            phone: editBtn.dataset.userPhone || '',
            role: editBtn.dataset.userRole || 'customer',
        };
        formEdit.action = `${cfg.usersBase}/${row.id}`;
        const nameEl = document.getElementById('u-edit-name');
        const emailEl = document.getElementById('u-edit-email');
        const phoneEl = document.getElementById('u-edit-phone');
        const roleWrap = document.getElementById('u-edit-role-wrap');
        const roleEl = document.getElementById('u-edit-role');
        if (nameEl) nameEl.value = row.name;
        if (emailEl) emailEl.value = row.email;
        if (phoneEl) phoneEl.value = row.phone;
        if (row.role === 'admin') {
            if (roleWrap) roleWrap.style.display = 'none';
            roleEl?.removeAttribute('name');
        } else {
            if (roleWrap) roleWrap.style.display = 'block';
            roleEl?.setAttribute('name', 'role');
            if (roleEl) {
                const r = row.role === 'seller' || row.role === 'mitra' ? row.role : 'customer';
                roleEl.value = r;
            }
        }
        dlgEdit.showModal();
    }

    async function submitDeleteUser(userId) {
        const token = csrfToken();
        const url = `${cfg.usersBase}/${userId}`;
        const fd = new FormData();
        fd.append('_token', token);
        fd.append('_method', 'DELETE');
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                credentials: 'same-origin',
                body: fd,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                showUserToast(errFromJson(data), 'error');
                return false;
            }
            if (data.message) showUserToast(data.message, 'success');
            await refreshUsersFromLive();
            return true;
        } catch {
            showUserToast('Jaringan bermasalah. Coba lagi.', 'error');
            return false;
        }
    }

    const dlgDelete = document.getElementById('dlg-user-delete');
    const deleteNameEl = document.getElementById('dlg-delete-user-name');
    const btnConfirmDelete = document.getElementById('btn-confirm-delete-user');
    let pendingDeleteUserId = /** @type {string | null} */ (null);

    function openDeleteConfirm(userId, displayName) {
        pendingDeleteUserId = userId;
        if (deleteNameEl) {
            deleteNameEl.textContent = displayName || userId;
        }
        dlgDelete?.showModal();
    }

    function closeDeleteDialog() {
        dlgDelete?.close();
    }

    dlgDelete?.addEventListener('close', () => {
        pendingDeleteUserId = null;
    });

    dlgDelete?.querySelectorAll('[data-close-delete-user]').forEach((b) => {
        b.addEventListener('click', () => closeDeleteDialog());
    });

    btnConfirmDelete?.addEventListener('click', async () => {
        if (!pendingDeleteUserId) return;
        const id = pendingDeleteUserId;
        btnConfirmDelete.disabled = true;
        const prevText = btnConfirmDelete.textContent;
        btnConfirmDelete.textContent = 'Menghapus…';
        try {
            const ok = await submitDeleteUser(id);
            if (ok) closeDeleteDialog();
        } finally {
            btnConfirmDelete.disabled = false;
            btnConfirmDelete.textContent = prevText || 'Hapus';
        }
    });

    tbody?.addEventListener('click', (e) => {
        const t = e.target;
        if (!(t instanceof Element)) return;

        const editBtn = t.closest('.edit-user');
        if (editBtn && tbody.contains(editBtn)) {
            e.preventDefault();
            e.stopPropagation();
            openEditFromButton(editBtn);
            return;
        }

        const delBtn = t.closest('.delete-user');
        if (delBtn && tbody.contains(delBtn)) {
            e.preventDefault();
            e.stopPropagation();
            const id = delBtn.dataset.userId;
            if (!id) return;
            const label = delBtn.dataset.label || delBtn.dataset.userName || id;
            openDeleteConfirm(id, label);
        }
    });
}
