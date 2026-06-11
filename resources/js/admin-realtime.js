/**
 * Pembaruan data admin berkala (polling) — tanpa reload halaman.
 * Interval default 4 detik; dijeda saat tab tidak terlihat.
 */
const POLL_MS = 4000;
const BASE = '/admin/api/live';

function fmtClock(iso) {
    if (!iso) return '—';
    try {
        const d = new Date(iso);
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    } catch {
        return '—';
    }
}

function setText(id, text) {
    const el = document.getElementById(id);
    if (el && el.textContent !== text) {
        el.textContent = text;
        el.classList.add('animate-pulse');
        setTimeout(() => el.classList.remove('animate-pulse'), 600);
    }
}

function setHtml(id, html) {
    const el = document.getElementById(id);
    if (el && el.innerHTML !== html) {
        el.innerHTML = html;
    }
}

function tickClock(iso) {
    const el = document.getElementById('rt-live-clock');
    if (el) el.textContent = fmtClock(iso);
}

async function fetchJson(url) {
    const res = await fetch(url, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    });
    if (!res.ok) throw new Error(String(res.status));
    return res.json();
}

function page() {
    return document.body?.dataset?.adminLive || '';
}

function schedule(fn) {
    setTimeout(fn, POLL_MS);
}

async function runDashboard() {
    const data = await fetchJson(`${BASE}/dashboard`);
    if (data.stats) {
        setText('rt-stat-customers', new Intl.NumberFormat('id-ID').format(data.stats.total_customers));
        setText('rt-stat-transactions', new Intl.NumberFormat('id-ID').format(data.stats.total_transactions));
        setText('rt-stat-orders-today', new Intl.NumberFormat('id-ID').format(data.stats.orders_today));
        setText('rt-stat-revenue-today', data.stats.revenue_today);
    }
    if (data.recent_orders_html) {
        setHtml('rt-recent-orders', data.recent_orders_html);
    }
    tickClock(data.updated_at);
}

async function runTransactions() {
    const qs = window.location.search || '';
    const data = await fetchJson(`${BASE}/transactions${qs}`);
    if (data.summary) {
        setText('rt-trx-revenue', data.summary.revenue_short);
        setText('rt-trx-completed', new Intl.NumberFormat('id-ID').format(data.summary.completed));
        setText('rt-trx-pending', new Intl.NumberFormat('id-ID').format(data.summary.pending));
        setText('rt-trx-failed', new Intl.NumberFormat('id-ID').format(data.summary.failed));
    }
    if (data.tbody_html) {
        setHtml('rt-transactions-tbody', data.tbody_html);
    }
    tickClock(data.updated_at);
}

async function runRestaurants() {
    const qs = window.location.search || '';
    const banner = document.getElementById('rt-restaurants-fetch-banner');
    try {
        const data = await fetchJson(`${BASE}/restaurants${qs}`);
        if (banner) {
            banner.classList.add('hidden');
        }
        if (data.stats) {
            setText('rt-rest-total', new Intl.NumberFormat('id-ID').format(data.stats.total_restaurants));
            setText('rt-rest-boxes', new Intl.NumberFormat('id-ID').format(data.stats.total_boxes));
            setText('rt-rest-active', new Intl.NumberFormat('id-ID').format(data.stats.active));
            setText('rt-rest-pending', new Intl.NumberFormat('id-ID').format(data.stats.pending));
            setText('rt-rest-locked', new Intl.NumberFormat('id-ID').format(data.stats.locked));
        }
        if (data.grid_html) {
            setHtml('rt-restaurants-grid', data.grid_html);
        }
        tickClock(data.updated_at);
    } catch {
        if (banner) {
            banner.classList.remove('hidden');
        }
    }
}

async function runUsers() {
    const qs = window.location.search || '';
    const data = await fetchJson(`${BASE}/users${qs}`);
    if (data.stats) {
        setText('rt-user-total', new Intl.NumberFormat('id-ID').format(data.stats.total));
        setText('rt-user-customers', new Intl.NumberFormat('id-ID').format(data.stats.customers));
        setText('rt-user-sellers', new Intl.NumberFormat('id-ID').format(data.stats.sellers));
        setText('rt-user-active', new Intl.NumberFormat('id-ID').format(data.stats.active));
    }
    if (data.tbody_html) {
        setHtml('rt-users-tbody', data.tbody_html);
    }
    if (data.pagination_html !== undefined) {
        setHtml('rt-users-pagination', data.pagination_html);
    }
    tickClock(data.updated_at);
}

async function runSettings() {
    if (document.activeElement?.closest?.('[data-settings-live]')) return;
    const data = await fetchJson(`${BASE}/settings`);
    if (!data.settings) return;
    Object.entries(data.settings).forEach(([key, val]) => {
        const el = document.querySelector(`[data-setting-key="${key}"]`);
        if (!el) return;
        if (el.type === 'checkbox') {
            el.checked = Boolean(val);
        } else {
            el.value = val === null || val === undefined ? '' : String(val);
        }
    });
    tickClock(data.updated_at);
}

async function runImpact() {
    const data = await fetchJson(`${BASE}/impact`);
    setText('rt-impact-meals', data.meals_saved);
    const w = document.getElementById('rt-impact-waste');
    if (w && data.waste_line) w.textContent = data.waste_line;
    setText('rt-impact-users', data.active_users);
    setText('rt-impact-waste-kg', data.waste_kg_block);
    setText('rt-impact-waste-tons', data.waste_tons_line);
    if (data.monthly_trend_html) {
        setHtml('rt-impact-monthly-card', data.monthly_trend_html);
    }
    tickClock(data.updated_at);
}

const runners = {
    dashboard: runDashboard,
    transactions: runTransactions,
    restaurants: runRestaurants,
    users: runUsers,
    settings: runSettings,
    impact: runImpact,
};

function loop() {
    const p = page();
    const fn = runners[p];
    if (!fn) return;
    if (document.hidden) {
        schedule(loop);
        return;
    }
    fn()
        .catch(() => {})
        .finally(() => schedule(loop));
}

export function startAdminRealtime() {
    if (!document.body?.dataset?.adminLive) return;
    schedule(loop);
}
