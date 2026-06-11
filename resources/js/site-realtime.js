const CUSTOMER_PICKUP_VALIDATION_FS = ['pending_confirmation', 'received', 'preparing', 'ready'];

function customerPickupFulfillmentAllows(fs) {
    return CUSTOMER_PICKUP_VALIDATION_FS.includes(fs);
}

/** Pembaruan berkala: badge keranjang + status pesanan (halaman riwayat). */
const CART_POLL_MS = 3000;
const ORDERS_POLL_MS = 5000;
const ORDER_TRACK_POLL_MS = 5000;
const CATALOG_POLL_MS = 10000;
const MITRA_POLL_MS = 4000;
const NOTIFICATIONS_POLL_MS = 4500;

let lastNotificationsFingerprint = '';
let notifDelegationBound = false;

/** Untuk countdown validasi pickup di halaman pelanggan (sinkron polling + tick lokal 1 Hz). */
let customerPickupServerSeconds = 0;

let customerPickupTickerId = 0;

function fmtPickupHms(totalSeconds) {
    const sec = Math.max(0, Math.floor(Number(totalSeconds) || 0));
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    const s = sec % 60;
    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(h)}:${pad(m)}:${pad(s)}`;
}

function bootstrapCustomerPickupCountdownDom() {
    const dd = document.getElementById('customer-pv-remain');
    if (!dd) return;

    const raw = dd.getAttribute('data-initial-pickup-seconds') ?? '';
    if (raw === '') return;
    const parsed = Number.parseInt(raw, 10);
    if (!Number.isFinite(parsed)) return;

    customerPickupServerSeconds = parsed;

    dd.textContent = fmtPickupHms(customerPickupServerSeconds);
}

function ensureCustomerPickupTicker() {
    if (customerPickupTickerId) return;
    const dd = document.getElementById('customer-pv-remain');
    if (!dd) return;

    customerPickupTickerId = window.setInterval(() => {
        const root = document.querySelector('[data-order-track-live]');
        const fulfillment = root?.getAttribute('data-fulfillment-status') ?? '';
        const pv = root?.getAttribute('data-pickup-validation-status') ?? '';

        if (pv === 'pickup_validation_pending' && customerPickupFulfillmentAllows(fulfillment) && customerPickupServerSeconds > 0) {
            customerPickupServerSeconds -= 1;
        }

        if (pv === 'pickup_validation_pending' && customerPickupFulfillmentAllows(fulfillment)) {
            dd.textContent = fmtPickupHms(customerPickupServerSeconds);
        }
    }, 1000);
}

function notificationsApiUrl() {
    const el = document.querySelector('[data-notifications-url]');
    const u = el ? el.getAttribute('data-notifications-url') : null;
    return typeof u === 'string' && u !== '' ? u : '';
}

function notifPatchPrefix() {
    const el = document.querySelector('[data-patch-read-prefix]');
    const p = el ? el.getAttribute('data-patch-read-prefix') : null;
    return typeof p === 'string' ? p : '';
}

function notifTrackTplPage() {
    const page = document.querySelector('[data-customer-notifications-page]');
    const t = page ? page.getAttribute('data-initial-track-tpl') : null;
    return typeof t === 'string' ? t : '';
}

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    const t = meta ? meta.getAttribute('content') : '';
    return typeof t === 'string' ? t : '';
}

/** Waktu relatif berbahasa Indonesia (Intl.RelativeTimeFormat). */
function formatRelativeTimeId(dateIso) {
    const ts = Date.parse(dateIso);
    if (!Number.isFinite(ts)) return '';

    const diffSecs = Math.round((ts - Date.now()) / 1000);
    const rtf = new Intl.RelativeTimeFormat('id', { numeric: 'auto' });

    const abs = Math.abs(diffSecs);
    const steps = [
        ['year', 60 * 60 * 24 * 365],
        ['month', 60 * 60 * 24 * 30],
        ['week', 60 * 60 * 24 * 7],
        ['day', 60 * 60 * 24],
        ['hour', 60 * 60],
        ['minute', 60],
        ['second', 1],
    ];

    for (const [unit, secs] of steps) {
        if (abs >= secs || unit === 'second') {
            return rtf.format(Math.trunc(diffSecs / secs), unit);
        }
    }

    return '';
}

function refreshNotificationRelativeTimes() {
    document.querySelectorAll('[data-created-at]').forEach((el) => {
        const iso = el.getAttribute('data-created-at');
        if (!iso) return;

        /** @type {HTMLElement} */
        const target = el;
        target.textContent = formatRelativeTimeId(iso);
    });
}

function mitraNotificationsApiUrl() {
    const bell = document.querySelector('[data-mitra-notif-live]');
    const u = bell ? bell.getAttribute('data-mitra-notifications-url') : null;
    return typeof u === 'string' && u !== '' ? u : '';
}

function applyMitraNotificationBellUnread(unread) {
    const bell = document.querySelector('[data-mitra-notif-live]');
    if (!bell) return;

    bell.classList.add('relative');

    const qty = Math.max(0, Math.floor(Number(unread) || 0));
    let badge = bell.querySelector('[data-mitra-notif-badge]');

    if (qty > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.setAttribute('data-mitra-notif-badge', '');
            badge.className =
                'absolute -right-0.5 -top-0.5 flex h-6 min-w-[1.35rem] items-center justify-center rounded-full bg-[#ef4444] px-1.5 text-[11px] font-black text-white ring-2 ring-white';
            bell.appendChild(badge);
        }
        badge.textContent = qty > 99 ? '99+' : String(qty);
    } else if (badge) {
        badge.remove();
    }
}

function applyCustomerNotificationBellUnread(unread) {
    const bell = document.querySelector('[data-customer-notif-live]');
    if (!bell) return;

    bell.classList.add('relative');

    const qty = Math.max(0, Math.floor(Number(unread) || 0));
    let badge = bell.querySelector('[data-customer-notif-badge]');

    if (qty > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.setAttribute('data-customer-notif-badge', '');
            badge.className =
                'absolute -right-0.5 -top-0.5 flex h-6 min-w-[1.35rem] items-center justify-center rounded-full bg-[#ef4444] px-1.5 text-[11px] font-black text-white ring-2 ring-white';
            bell.appendChild(badge);
        }
        badge.textContent = qty > 99 ? '99+' : String(qty);
    } else if (badge) {
        badge.remove();
    }
}

function notificationIconMarkup(typeRaw) {
    const t = String(typeRaw ?? '');
    if (t.includes('payment')) {
        return '<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>';
    }
    if (t.includes('order_created')) {
        return '<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>';
    }
    return '<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0h6zm-3-17v.01V0z" /></svg>';
}

function notificationsEmptyMarkup() {
    return `
<div class="rounded-3xl bg-white px-8 py-16 text-center shadow-md ring-1 ring-slate-100">
  <div class="mb-4 text-6xl" aria-hidden="true">🔔</div>
  <h2 class="mb-3 text-2xl font-black text-[#1e2939]">Belum ada notifikasi</h2>
  <p class="mb-8 text-[#6a7282]">Pembayaran, status pesanan, dan pembaruan penting akan muncul otomatis di sini.</p>
  <a href="/browse"
     class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#ff6900] to-[#f54900] px-8 py-3 text-base font-bold text-white shadow-lg transition hover:shadow-xl">
    Jelajahi Mystery Boxes
  </a>
</div>`;
}

function notificationsTrackHref(trackerTpl, publicOrderId) {
    const oid = String(publicOrderId ?? '');
    if (!trackerTpl || !oid) return '';

    /** @type {string} */
    let tpl = trackerTpl;
    if (tpl.includes('__ORDER__')) {
        tpl = tpl.split('__ORDER__').join(encodeURIComponent(oid));
    }

    return tpl;
}

function renderNotificationsListIntoRoot(rootEl, data) {
    const items = Array.isArray(data.notifications) ? data.notifications : [];

    /** @type {string} */
    let trackerTpl =
        typeof data.tracker_url_tpl === 'string' && data.tracker_url_tpl.includes('__ORDER__') ? data.tracker_url_tpl : '';
    const pageTpl = notifTrackTplPage();
    if (!trackerTpl && pageTpl.includes('__ORDER__')) {
        trackerTpl = pageTpl;
    }

    if (!items.length) {
        /** @type {HTMLElement} */
        rootEl.innerHTML = notificationsEmptyMarkup();
        return;
    }

    /** @type {string[]} */
    const cards = [];

    for (let i = 0; i < items.length; i += 1) {
        /** @type {any} */
        const n = items[i];
        const id = typeof n.id === 'number' || typeof n.id === 'string' ? String(n.id) : '';
        if (!id) continue;

        const title = escapeHtml(typeof n.title === 'string' ? n.title : '');
        const body = escapeHtml(typeof n.body === 'string' ? n.body : '');
        const oid = typeof n.public_order_id === 'string' ? n.public_order_id : null;
        const createdAt = typeof n.created_at === 'string' ? n.created_at : '';
        const isUnread = !(n.read || n.read_at);

        const iconWrap = isUnread ? 'bg-[#bbf7d0] text-[#14532d]' : 'bg-slate-100 text-[#475569]';

        /** @type {string} */
        const titleCls = isUnread ? 'text-[#0f172a]' : 'text-slate-700';
        /** @type {string} */
        const bodyCls = isUnread ? 'text-[#475569]' : 'text-[#64748b]';

        /** @type {string} */
        const track = oid ? notificationsTrackHref(trackerTpl, oid) : '';
        /** @type {string} */
        const trackMarkup = track
            ? `<a href="${escapeHtmlAttr(track)}" class="text-[#00a63e] hover:underline">Lacak pesanan</a>`
            : '';

        /** @type {string} */
        const markMarkup = isUnread
            ? `<button type="button" class="border-0 bg-transparent p-0 text-[#6366f1] hover:underline js-notif-mark-read" data-notification-id="${escapeHtmlAttr(
                  id,
              )}">Tandai dibaca</button>`
            : '';

        const oidMarkup = oid
            ? `<p class="mt-2 font-mono text-xs font-semibold text-[#00a63e]">${escapeHtmlAttr(oid)}</p>`
            : '';

        cards.push(`
<article class="rounded-3xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6"
         data-notification-id="${escapeHtmlAttr(id)}" data-notification-read="${isUnread ? '0' : '1'}">
  <div class="flex gap-4">
    <div class="${iconWrap} flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-2 ring-black/5" aria-hidden="true">
      ${notificationIconMarkup(typeof n.type === 'string' ? n.type : '')}
    </div>
    <div class="min-w-0 flex-1">
      <div class="mb-2 flex flex-wrap items-start justify-between gap-2">
        <h2 class="${titleCls} text-lg font-black">${title}</h2>
        <time datetime="${escapeHtmlAttr(createdAt)}"
              class="notif-relative-time shrink-0 text-xs font-semibold uppercase tracking-wide text-[#64748b]"
              data-created-at="${escapeHtmlAttr(createdAt)}">
          ${escapeHtml(formatRelativeTimeId(createdAt))}
        </time>
      </div>
      <p class="${bodyCls} text-sm leading-relaxed">${body}</p>
      ${oidMarkup}
      <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-bold">${[trackMarkup, markMarkup].filter(Boolean).join('')}</div>
    </div>
  </div>
</article>`);
    }

    rootEl.innerHTML = cards.join('');
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll('\'', '&#039;');
}

function escapeHtmlAttr(value) {
    return escapeHtml(String(value ?? '')).replaceAll('`', '&#096;');
}

function bindNotificationsDelegationOnce() {
    if (notifDelegationBound) return;
    notifDelegationBound = true;

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-notif-mark-read');
        if (!btn) return;

        e.preventDefault();

        /** @type {HTMLElement} */
        const b = btn;
        const id = b.getAttribute('data-notification-id') ?? '';

        /** @type {string} */
        const prefixRaw = notifPatchPrefix();

        /** @type {string} */
        const prefix =
            typeof prefixRaw === 'string' && prefixRaw !== ''
                ? prefixRaw.replace(/\/+$/, '')
                : '';

        if (!prefix || id === '') return;

        try {
            const res = await fetch(`${prefix}/${id}/read`, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: '{}',
            });
            if (!res.ok) return;
            await res.json().catch(() => null);
        } catch {
            return;
        }

        lastNotificationsFingerprint = '';
        await pollNotificationsOnce();
        await pollMitraNotificationsOnce();
    });
}

async function pollNotificationsOnce() {
    const url = notificationsApiUrl();
    if (!url) return;

    try {
        const res = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        /** @type {any} */
        const data = await res.json();

        const fp = typeof data.fingerprint === 'string' ? data.fingerprint : '';
        applyCustomerNotificationBellUnread(data.unread_count);

        /** @type {HTMLElement | null} */
        const pageRoot = document.querySelector('[data-customer-notifications-page]');

        /** @type {HTMLElement | null} */
        const listRoot = document.getElementById('customer-notifications-root');

        if (fp !== '' && fp === lastNotificationsFingerprint) {
            refreshNotificationRelativeTimes();
            return;
        }

        if (fp !== '') {
            lastNotificationsFingerprint = fp;
        }

        if (pageRoot && listRoot) {
            renderNotificationsListIntoRoot(listRoot, data);
        }

        refreshNotificationRelativeTimes();
    } catch {
        /* abaikan */
    }
}

async function pollMitraNotificationsOnce() {
    const url = mitraNotificationsApiUrl();
    if (!url) return;

    try {
        const res = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        /** @type {any} */
        const data = await res.json();

        applyMitraNotificationBellUnread(typeof data.unread_count === 'number' ? data.unread_count : 0);
    } catch {
        /* abaikan */
    }
}

function notificationsLoop() {
    if (document.hidden) {
        schedule(() => notificationsLoop(), NOTIFICATIONS_POLL_MS);
        return;
    }

    pollNotificationsOnce()
        .finally(() =>
            pollMitraNotificationsOnce().finally(() => schedule(() => notificationsLoop(), NOTIFICATIONS_POLL_MS)),
        );
}

function syncCustomerPickupFromLivePayload(data, trackRootEl) {
    const msgEl = document.getElementById('customer-pv-msg');
    const remainEl = document.getElementById('customer-pv-remain');

    ensureCustomerPickupTicker();

    if (!data || !remainEl || !trackRootEl) return;

    const method = typeof data.fulfillment_method === 'string' ? data.fulfillment_method : '';
    if (method !== 'pickup') return;

    const pickupStatus = typeof data.pickup_validation_status === 'string' ? data.pickup_validation_status : '';
    const fs = typeof data.fulfillment_status === 'string' ? data.fulfillment_status : '';
    trackRootEl.setAttribute('data-pickup-validation-status', pickupStatus);
    trackRootEl.setAttribute('data-fulfillment-status', fs);

    if (pickupStatus === 'pickup_validation_pending' && customerPickupFulfillmentAllows(fs) && data.pickup_live) {
        const sec = Number(data.pickup_live.pickup_validation_seconds_remaining ?? 0) || 0;
        customerPickupServerSeconds = sec;
        remainEl.textContent = fmtPickupHms(sec);
    } else {
        remainEl.textContent = '—';
        customerPickupServerSeconds = 0;
    }

    if (msgEl) {
        const fb = fulfillmentBadge(data.payment_status, fs, pickupStatus, method);
        msgEl.textContent = fb.label;
    }

    /** Perbarui badge besar di atas ringkasan pesanan */
    const topBadge = document.querySelector('[data-order-top-fulfillment-badge]');
    const payment = typeof data.payment_status === 'string' ? data.payment_status : null;

    const fbBadge = fulfillmentBadge(payment, fs, pickupStatus, method);
    if (topBadge) {
        topBadge.textContent = fbBadge.label;
        topBadge.className = fbBadge.cls;
    }
}

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
function fulfillmentBadge(paymentStatus, fulfillment, pickupValidationStatus, fulfillmentMethod) {
    const p = paymentStatus ?? null;
    const f = fulfillment ?? null;
    const pv = pickupValidationStatus ?? null;
    const m = fulfillmentMethod ?? null;

    if (p === 'PENDING' || (p === null && f === 'awaiting_payment')) {
        return { label: 'Menunggu pembayaran', cls: FULFILLMENT_BADGE_CLASS + 'bg-slate-100 text-slate-700 ring-slate-200' };
    }

    if (m === 'pickup' && customerPickupFulfillmentAllows(f ?? '')) {
        if (pv === 'pickup_validation_pending') {
            return { label: 'Menunggu validasi pickup', cls: FULFILLMENT_BADGE_CLASS + 'bg-amber-50 text-amber-900 ring-amber-200' };
        }

        if (pv === 'pickup_validation_expired') {
            return { label: 'Validasi pickup kedaluwarsa', cls: FULFILLMENT_BADGE_CLASS + 'bg-red-50 text-red-800 ring-red-200' };
        }

        if (pv === 'pickup_validated') {
            switch (f) {
                case 'ready':
                    return { label: 'Siap Diambil', cls: FULFILLMENT_BADGE_CLASS + 'bg-orange-50 text-orange-800 ring-orange-200' };
                case 'preparing':
                    return { label: 'Sedang disiapkan', cls: FULFILLMENT_BADGE_CLASS + 'bg-amber-50 text-amber-900 ring-amber-200' };
                case 'received':
                    return { label: 'Pesanan diterima', cls: FULFILLMENT_BADGE_CLASS + 'bg-emerald-50 text-emerald-800 ring-emerald-200' };
                case 'pending_confirmation':
                    return { label: 'Menunggu Konfirmasi', cls: FULFILLMENT_BADGE_CLASS + 'bg-slate-100 text-slate-700 ring-slate-200' };
                default:
                    return { label: 'Diproses', cls: FULFILLMENT_BADGE_CLASS + 'bg-slate-100 text-slate-700 ring-slate-200' };
            }
        }
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
                const fb = fulfillmentBadge(
                    o.payment_status,
                    o.fulfillment_status,
                    o.pickup_validation_status,
                    o.fulfillment_method,
                );
                fulfillmentEl.textContent = fb.label;
                fulfillmentEl.className = fb.cls;
                row.setAttribute('data-payment-status', o.payment_status ?? '');
                row.setAttribute('data-fulfillment-status', o.fulfillment_status ?? '');
                row.setAttribute('data-fulfillment-method', o.fulfillment_method ?? '');
                row.setAttribute('data-pickup-validation-status', o.pickup_validation_status ?? '');
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

function publishMitraDashboardData(menus, stats, sales = null) {
    if (typeof window === 'undefined') {
        return;
    }
    window.__mitraDashboardData = { menus, stats, sales };
    window.dispatchEvent(
        new CustomEvent('mitra-dashboard-snapshot', {
            detail: { menus, stats, sales },
        }),
    );
}

function applyMitraDashboardSnapshot(root, data) {
    const placeholder = root.getAttribute('data-placeholder-img') || '';
    if (data.hash) {
        root.setAttribute('data-mitra-fingerprint', data.hash);
    }

    const stats = data.stats ?? null;
    const menus = Array.isArray(data.menus) ? data.menus : [];
    const salesPayload = typeof data.sales === 'object' && data.sales !== null ? data.sales : null;

    if (stats) {
        const tb = root.querySelector('.stat-total-boxes');
        const ts = root.querySelector('.stat-total-stock');
        const rev = root.querySelector('.stat-revenue');
        const avg = root.querySelector('.stat-avg-savings');
        if (tb) {
            tb.textContent = String(stats.total_boxes);
            tb.setAttribute('data-value', String(stats.total_boxes));
        }
        if (ts) {
            ts.textContent = String(stats.total_stock);
            ts.setAttribute('data-value', String(stats.total_stock));
        }
        if (rev) {
            rev.textContent = formatRpId(stats.revenue_estimate);
            rev.setAttribute('data-value', String(stats.revenue_estimate));
        }
        if (avg) {
            avg.textContent = formatRpId(stats.avg_savings);
            avg.setAttribute('data-value', String(stats.avg_savings));
        }
    }

    const sal = salesPayload;
    if (sal && typeof sal === 'object') {
        root.setAttribute('data-mitra-sales-period', String(sal.period ?? '30d'));

        const pl = root.querySelector('.stat-sales-period-label');
        if (pl && typeof sal.period_label === 'string') {
            pl.textContent = sal.period_label;
        }

        const gross = root.querySelector('.stat-sales-gross');
        if (gross && typeof sal.gross_idr === 'number') {
            gross.textContent = formatRpId(sal.gross_idr);
            gross.setAttribute('data-value', String(sal.gross_idr));
        }

        const ords = root.querySelector('.stat-sales-orders');
        if (ords && typeof sal.order_count === 'number') {
            ords.textContent = String(sal.order_count);
            ords.setAttribute('data-value', String(sal.order_count));
        }

        const units = root.querySelector('.stat-sales-units');
        if (units && typeof sal.units_sold === 'number') {
            units.textContent = String(sal.units_sold);
            units.setAttribute('data-value', String(sal.units_sold));
        }

        const aov = root.querySelector('.stat-sales-aov');
        if (aov && typeof sal.avg_order_idr === 'number') {
            aov.textContent = formatRpId(sal.avg_order_idr);
            aov.setAttribute('data-value', String(sal.avg_order_idr));
        }

        const pco = root.querySelector('.stat-sales-pending-orders');
        if (pco && typeof sal.pending_gateway_count === 'number') {
            pco.textContent = String(sal.pending_gateway_count);
            pco.setAttribute('data-value', String(sal.pending_gateway_count));
        }

        const pcg = root.querySelector('.stat-sales-pending-gross');
        if (pcg && typeof sal.pending_gateway_idr === 'number') {
            pcg.textContent = formatRpId(sal.pending_gateway_idr);
            pcg.setAttribute('data-value', String(sal.pending_gateway_idr));
        }

        const comp = root.querySelector('.stat-sales-completed');
        if (comp && typeof sal.completed_orders === 'number') {
            comp.textContent = String(sal.completed_orders);
        }
    }

    const grid = document.getElementById('mystery-grid');
    const empty = document.getElementById('mystery-empty');

    if (!grid) {
        publishMitraDashboardData(menus, stats, salesPayload);
        return;
    }

    if (menus.length === 0) {
        grid.innerHTML = '';
        empty?.classList.remove('hidden');
        grid.classList.add('hidden');
        publishMitraDashboardData(menus, stats, salesPayload);
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
            savings_percent: typeof m.savings_percent === 'number' ? m.savings_percent : 0,
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

    publishMitraDashboardData(menus, stats, salesPayload);
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

    const period = root.getAttribute('data-mitra-sales-period') || '30d';
    let fetchUrl = url;
    try {
        const u = new URL(url, window.location.origin);
        u.searchParams.set('sales_period', period);
        fetchUrl = u.pathname + u.search + u.hash;
    } catch {
        fetchUrl =
            url.indexOf('?') >= 0
                ? `${url}&sales_period=${encodeURIComponent(period)}`
                : `${url}?sales_period=${encodeURIComponent(period)}`;
    }

    try {
        const res = await fetch(fetchUrl, {
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

    const lastFs = el.getAttribute('data-fulfillment-status') ?? '';
    const lastPv = el.getAttribute('data-pickup-validation-status') ?? '';

    try {
        const res = await fetch(`/api/live/order/${encodeURIComponent(id)}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        const nextFs = typeof data.fulfillment_status === 'string' ? data.fulfillment_status : '';
        const nextPv = typeof data.pickup_validation_status === 'string' ? data.pickup_validation_status : '';

        if (nextFs !== lastFs || nextPv !== lastPv) {
            window.location.reload();
            return;
        }

        syncCustomerPickupFromLivePayload(data, el);
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
    bindNotificationsDelegationOnce();

    const notificationsUrlLive = notificationsApiUrl();
    const mitraNotifLive = mitraNotificationsApiUrl();

    if (notificationsUrlLive || mitraNotifLive) {
        pollNotificationsOnce();
        pollMitraNotificationsOnce();
        schedule(() => notificationsLoop(), NOTIFICATIONS_POLL_MS);
        window.setInterval(() => {
            if (!document.hidden && document.querySelector('[data-customer-notifications-page]')) {
                refreshNotificationRelativeTimes();
            }
        }, 60000);
    }

    if (document.querySelector('[data-cart-live]')) {
        pollCart();
        schedule(() => cartLoop(), CART_POLL_MS);
    }
    if (document.querySelector('[data-orders-live]')) {
        pollOrders();
        schedule(() => ordersLoop(), ORDERS_POLL_MS);
    }
    if (document.querySelector('[data-order-track-live]')) {
        bootstrapCustomerPickupCountdownDom();
        ensureCustomerPickupTicker();
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
            pollNotificationsOnce();
            pollMitraNotificationsOnce();
        }
    });
}
