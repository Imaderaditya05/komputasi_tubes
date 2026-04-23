/**
 * Pembaruan berkala: badge keranjang + status pesanan (halaman riwayat).
 */
const CART_POLL_MS = 3000;
const ORDERS_POLL_MS = 5000;
const ORDER_TRACK_POLL_MS = 5000;
const CATALOG_POLL_MS = 10000;
const MITRA_POLL_MS = 4000;

function schedule(fn, ms) {
    setTimeout(fn, ms);
}

async function pollCart() {
    const cartBtn = document.querySelector('[data-cart-live]');
    if (!cartBtn) return;

    try {
        const res = await fetch('/api/live/cart', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        const qty = Number(data.quantity) || 0;
        let badge = cartBtn.querySelector('[data-cart-badge]');
        if (qty > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.setAttribute('data-cart-badge', '');
                badge.className =
                    'absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[#f97316] px-1 text-[10px] font-black text-white ring-2 ring-white';
                cartBtn.appendChild(badge);
            }
            badge.textContent = qty > 99 ? '99+' : String(qty);
        } else if (badge) {
            badge.remove();
        }
    } catch {
        /* abaikan */
    }
}

const STATUS_LABEL = {
    PAID: 'Lunas',
    PENDING: 'Menunggu bayar',
    PENDING_COD: 'COD — menunggu',
    CHALLENGE: 'Verifikasi',
    DENIED: 'Ditolak',
    EXPIRED: 'Kedaluwarsa',
    CANCELED: 'Dibatalkan',
};

const STATUS_CLASS =
    'inline-flex rounded-full px-3 py-1 text-xs font-black ring-1 ';

function statusClasses(status) {
    switch (status) {
        case 'PAID':
            return STATUS_CLASS + 'bg-emerald-50 text-emerald-800 ring-emerald-200';
        case 'PENDING':
        case 'PENDING_COD':
            return STATUS_CLASS + 'bg-amber-50 text-amber-900 ring-amber-200';
        case 'DENIED':
        case 'EXPIRED':
        case 'CANCELED':
            return STATUS_CLASS + 'bg-red-50 text-red-800 ring-red-200';
        default:
            return STATUS_CLASS + 'bg-slate-100 text-slate-800 ring-slate-200';
    }
}

const FULFILLMENT_BADGE_CLASS =
    'inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-black ring-1 ';

/** Selaras dengan OrderHistoryController::formatFulfillmentBadge */
function fulfillmentBadge(paymentStatus, fulfillment) {
    const p = paymentStatus ?? null;
    const f = fulfillment ?? null;
    if (p === 'PENDING' || (p === null && f === 'awaiting_payment')) {
        return { label: 'Menunggu pembayaran', cls: FULFILLMENT_BADGE_CLASS + 'bg-slate-100 text-slate-700 ring-slate-200' };
    }
    switch (f) {
        case 'awaiting_payment':
            return { label: 'Menunggu pembayaran', cls: FULFILLMENT_BADGE_CLASS + 'bg-slate-100 text-slate-700 ring-slate-200' };
        case 'pending_confirmation':
            return { label: 'Menunggu Konfirmasi', cls: FULFILLMENT_BADGE_CLASS + 'bg-slate-100 text-slate-700 ring-slate-200' };
        case 'received':
            return { label: 'Pesanan diterima', cls: FULFILLMENT_BADGE_CLASS + 'bg-emerald-50 text-emerald-800 ring-emerald-200' };
        case 'preparing':
            return { label: 'Sedang disiapkan', cls: FULFILLMENT_BADGE_CLASS + 'bg-amber-50 text-amber-900 ring-amber-200' };
        case 'ready':
            return { label: 'Siap Diambil', cls: FULFILLMENT_BADGE_CLASS + 'bg-orange-50 text-orange-800 ring-orange-200' };
        case 'completed':
            return { label: 'Selesai', cls: FULFILLMENT_BADGE_CLASS + 'bg-emerald-50 text-emerald-800 ring-emerald-200' };
        default:
            return { label: 'Diproses', cls: FULFILLMENT_BADGE_CLASS + 'bg-slate-100 text-slate-700 ring-slate-200' };
    }
}

async function pollOrders() {
    const root = document.querySelector('[data-orders-live]');
    if (!root) return;

    try {
        const res = await fetch('/api/live/orders', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        const list = Array.isArray(data.orders) ? data.orders : [];
        const byId = new Map(list.map((o) => [o.public_order_id, o]));

        root.querySelectorAll('[data-order-row]').forEach((row) => {
            const id = row.getAttribute('data-order-row');
            const o = byId.get(id);
            if (!o) return;

            const fulfillmentEl = row.querySelector('[data-order-fulfillment-badge]');
            if (fulfillmentEl) {
                const fb = fulfillmentBadge(o.payment_status, o.fulfillment_status);
                fulfillmentEl.textContent = fb.label;
                fulfillmentEl.className = fb.cls;
                row.setAttribute('data-payment-status', o.payment_status ?? '');
                row.setAttribute('data-fulfillment-status', o.fulfillment_status ?? '');
                return;
            }

            const el = row.querySelector('[data-order-status]');
            if (!el) return;
            const st = o.payment_status || 'PENDING';
            const label = STATUS_LABEL[st] || st || '—';
            el.textContent = label;
            el.className = statusClasses(st);
        });
    } catch {
        /* abaikan */
    }
}

async function pollCatalogHash() {
    const root = document.querySelector('[data-browse-live]');
    if (!root) return;

    try {
        const res = await fetch('/api/live/catalog-hash', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        const next = data.hash || '';
        const last = root.getAttribute('data-catalog-hash') || '';
        if (!next) return;
        if (!last) {
            root.setAttribute('data-catalog-hash', next);
            return;
        }
        if (last !== next) {
            window.location.reload();
        }
    } catch {
        /* abaikan */
    }
}

function formatRpId(n) {
    const v = Math.round(Number(n) || 0);
    return `Rp ${v.toLocaleString('id-ID')}`;
}

function applyMitraDashboardSnapshot(root, data) {
    const placeholder = root.getAttribute('data-placeholder-img') || '';
    if (data.hash) {
        root.setAttribute('data-mitra-fingerprint', data.hash);
    }

    const stats = data.stats;
    if (stats) {
        const tb = root.querySelector('.stat-total-boxes');
        const ts = root.querySelector('.stat-total-stock');
        const rev = root.querySelector('.stat-revenue');
        const avg = root.querySelector('.stat-avg-savings');
        if (tb) tb.textContent = String(stats.total_boxes);
        if (ts) ts.textContent = String(stats.total_stock);
        if (rev) {
            rev.textContent = formatRpId(stats.revenue_estimate);
            rev.setAttribute('data-value', String(stats.revenue_estimate));
        }
        if (avg) {
            avg.textContent = formatRpId(stats.avg_savings);
            avg.setAttribute('data-value', String(stats.avg_savings));
        }
    }

    const grid = document.getElementById('mystery-grid');
    const empty = document.getElementById('mystery-empty');
    const menus = Array.isArray(data.menus) ? data.menus : [];

    if (!grid) {
        return;
    }

    if (menus.length === 0) {
        grid.innerHTML = '';
        empty?.classList.remove('hidden');
        grid.classList.add('hidden');
        return;
    }

    empty?.classList.add('hidden');
    grid.classList.remove('hidden');

    const cards = grid.querySelectorAll('.mystery-card');
    if (cards.length !== menus.length) {
        window.location.reload();
        return;
    }

    for (const m of menus) {
        const card = grid.querySelector(`.mystery-card[data-menu-id="${m.id}"]`);
        if (!card) {
            window.location.reload();
            return;
        }
        const menuJson = {
            id: m.id,
            name: m.name,
            price: m.price,
            original_price: m.original_price,
            category: m.category,
            description: m.description,
            stock: m.stock,
            pickup_time: m.pickup_time,
            image_url: m.image_url,
        };
        card.dataset.menu = JSON.stringify(menuJson);

        const badge = card.querySelector('[data-field="stock-badge"]');
        if (badge) {
            badge.textContent = `${m.stock} Tersisa`;
            badge.setAttribute('data-stock', String(m.stock));
        }

        const imgEl = card.querySelector('[data-field="image"]');
        if (imgEl) {
            imgEl.src = m.image_url || placeholder;
        }

        const nameEl = card.querySelector('[data-field="name"]');
        if (nameEl) nameEl.textContent = m.name;

        const catEl = card.querySelector('[data-field="category"]');
        if (catEl) {
            if (m.category) {
                catEl.textContent = m.category;
                catEl.classList.remove('hidden');
            } else {
                catEl.textContent = '';
                catEl.classList.add('hidden');
            }
        }

        const subEl = card.querySelector('[data-field="subtitle"]');
        if (subEl) {
            const sub = m.description || m.category || '';
            subEl.textContent = sub;
        }

        const priceEl = card.querySelector('[data-field="price"]');
        if (priceEl) {
            priceEl.textContent = formatRpId(m.price);
            priceEl.setAttribute('data-raw', String(m.price));
        }

        const origEl = card.querySelector('[data-field="original"]');
        if (origEl) {
            origEl.textContent = formatRpId(m.original_price);
            origEl.setAttribute('data-raw', String(m.original_price));
        }

        const hematEl = card.querySelector('[data-field="hemat"]');
        if (hematEl) {
            const pct = typeof m.savings_percent === 'number' ? m.savings_percent : 0;
            hematEl.textContent = `${pct}%`;
        }

        const pickupEl = card.querySelector('[data-field="pickup"]');
        if (pickupEl) {
            pickupEl.textContent = m.pickup_time || '—';
        }
    }
}

function applyMitraManageSnapshot(root, data) {
    if (data.hash) {
        root.setAttribute('data-mitra-fingerprint', data.hash);
    }
    const menusEl = root.querySelector('[data-mitra-manage-menus-count]');
    const ordersEl = root.querySelector('[data-mitra-manage-orders-count]');
    if (menusEl && typeof data.menus_count === 'number') {
        menusEl.textContent = String(data.menus_count);
    }
    if (ordersEl && typeof data.orders_count === 'number') {
        ordersEl.textContent = String(data.orders_count);
    }
}

async function pollMitraDashboardOnce() {
    const root = document.querySelector('[data-mitra-dashboard-live]');
    if (!root) return;

    const url = root.getAttribute('data-mitra-live-url');
    if (!url) return;

    try {
        const res = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        const last = root.getAttribute('data-mitra-fingerprint') || '';
        const next = data.hash || '';
        if (!next) return;
        if (!last) {
            root.setAttribute('data-mitra-fingerprint', next);
            applyMitraDashboardSnapshot(root, data);
            return;
        }
        if (last !== next) {
            applyMitraDashboardSnapshot(root, data);
        }
    } catch {
        /* abaikan */
    }
}

async function pollMitraManageOnce() {
    const root = document.querySelector('[data-mitra-manage-live]');
    if (!root) return;

    const url = root.getAttribute('data-mitra-live-url');
    if (!url) return;

    try {
        const res = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        const last = root.getAttribute('data-mitra-fingerprint') || '';
        const next = data.hash || '';
        if (!next) return;
        if (!last) {
            root.setAttribute('data-mitra-fingerprint', next);
            applyMitraManageSnapshot(root, data);
            return;
        }
        if (last !== next) {
            applyMitraManageSnapshot(root, data);
        }
    } catch {
        /* abaikan */
    }
}

function mitraDashboardLoop() {
    if (document.hidden) {
        schedule(() => mitraDashboardLoop(), MITRA_POLL_MS);
        return;
    }
    pollMitraDashboardOnce().finally(() => schedule(() => mitraDashboardLoop(), MITRA_POLL_MS));
}

function mitraManageLoop() {
    if (document.hidden) {
        schedule(() => mitraManageLoop(), MITRA_POLL_MS);
        return;
    }
    pollMitraManageOnce().finally(() => schedule(() => mitraManageLoop(), MITRA_POLL_MS));
}

function catalogLoop() {
    if (document.hidden) {
        schedule(() => catalogLoop(), CATALOG_POLL_MS);
        return;
    }
    pollCatalogHash().finally(() => schedule(() => catalogLoop(), CATALOG_POLL_MS));
}

async function pollOrderTrack() {
    const el = document.querySelector('[data-order-track-live]');
    if (!el) return;
    const id = el.getAttribute('data-public-order-id');
    if (!id) return;
    const last = el.getAttribute('data-fulfillment-status') ?? '';

    try {
        const res = await fetch(`/api/live/order/${encodeURIComponent(id)}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        const next = data.fulfillment_status ?? '';
        if (next !== last) {
            window.location.reload();
        }
    } catch {
        /* abaikan */
    }
}

function cartLoop() {
    if (document.hidden) {
        schedule(() => cartLoop(), CART_POLL_MS);
        return;
    }
    pollCart().finally(() => schedule(() => cartLoop(), CART_POLL_MS));
}

function ordersLoop() {
    if (document.hidden) {
        schedule(() => ordersLoop(), ORDERS_POLL_MS);
        return;
    }
    pollOrders().finally(() => schedule(() => ordersLoop(), ORDERS_POLL_MS));
}

function orderTrackLoop() {
    if (document.hidden) {
        schedule(() => orderTrackLoop(), ORDER_TRACK_POLL_MS);
        return;
    }
    pollOrderTrack().finally(() => schedule(() => orderTrackLoop(), ORDER_TRACK_POLL_MS));
}

export function refreshMitraDashboard() {
    return pollMitraDashboardOnce();
}

export function startSiteRealtime() {
    if (document.querySelector('[data-cart-live]')) {
        pollCart();
        schedule(() => cartLoop(), CART_POLL_MS);
    }
    if (document.querySelector('[data-orders-live]')) {
        pollOrders();
        schedule(() => ordersLoop(), ORDERS_POLL_MS);
    }
    if (document.querySelector('[data-order-track-live]')) {
        pollOrderTrack();
        schedule(() => orderTrackLoop(), ORDER_TRACK_POLL_MS);
    }

    if (document.querySelector('[data-browse-live]')) {
        pollCatalogHash();
        schedule(() => catalogLoop(), CATALOG_POLL_MS);
    }

    if (document.querySelector('[data-mitra-dashboard-live]')) {
        pollMitraDashboardOnce();
        schedule(() => mitraDashboardLoop(), MITRA_POLL_MS);
    }

    if (document.querySelector('[data-mitra-manage-live]')) {
        pollMitraManageOnce();
        schedule(() => mitraManageLoop(), MITRA_POLL_MS);
    }

    if (typeof window !== 'undefined') {
        window.refreshMitraDashboard = refreshMitraDashboard;
    }

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            pollCart();
            pollOrders();
            pollOrderTrack();
            pollCatalogHash();
            pollMitraDashboardOnce();
            pollMitraManageOnce();
        }
    });
}
