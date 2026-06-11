const POLL_MS = 6000;

function formatIsoOrMsInTimezone(isoOrMs, timeZone) {
    const ms =
        typeof isoOrMs === 'number'
            ? isoOrMs
            : isoOrMs
              ? Date.parse(String(isoOrMs))
              : Number.NaN;
    if (Number.isNaN(ms)) return null;

    try {
        const opts = {
            timeZone: timeZone || undefined,
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        };

        return new Intl.DateTimeFormat('en-GB', opts).format(new Date(ms));
    } catch {
        return null;
    }
}

function fmtHMS(totalSeconds) {
    const sec = Math.max(0, Math.floor(Number(totalSeconds) || 0));
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    const s = sec % 60;
    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(h)}:${pad(m)}:${pad(s)}`;
}

function patchStatusLabel(el, pickupStatus, fulfillmentStatus) {
    const PENDING = 'pickup_validation_pending';
    const EXP = 'pickup_validation_expired';
    const OK = 'pickup_validated';

    const map = {
        [PENDING]: 'Menunggu validasi pickup',
        [EXP]: 'Kedaluwarsa',
        [OK]: fulfillmentStatus === 'completed' ? 'Berhasil · selesai' : 'Tervalidasi',
    };

    const text = pickupStatus ? map[pickupStatus] ?? String(pickupStatus) : 'Belum aktif';
    if (el) el.textContent = text;
}

function showAlert(panel, variant, msg) {
    if (!panel) return;
    panel.textContent = msg;
    panel.classList.remove(
        'hidden',
        'bg-emerald-50',
        'text-emerald-900',
        'ring-emerald-100',
        'bg-red-50',
        'text-red-900',
        'ring-red-100',
        'bg-slate-50',
        'text-slate-900',
        'ring-slate-200',
    );
    panel.classList.add('rounded-2xl', 'px-4', 'py-3', 'ring-1');

    if (variant === 'ok') {
        panel.classList.add('bg-emerald-50', 'text-emerald-900', 'ring-emerald-100');
    } else if (variant === 'err') {
        panel.classList.add('bg-red-50', 'text-red-900', 'ring-red-100');
    } else {
        panel.classList.add('bg-slate-50', 'text-slate-900', 'ring-slate-200');
    }
}

export function initMitraPickupPage() {
    const root = document.querySelector('[data-mitra-pickup-page]');
    if (!root) return;

    const appTimezone = root.getAttribute('data-app-timezone') || '';
    let activePickupFulfillments = [];
    try {
        activePickupFulfillments = JSON.parse(root.getAttribute('data-active-pickup-phases') || '[]');
    } catch {
        activePickupFulfillments = [];
    }
    if (!Array.isArray(activePickupFulfillments) || activePickupFulfillments.length === 0) {
        activePickupFulfillments = [
            'pending_confirmation',
            'received',
            'preparing',
            'ready',
        ];
    }
    const fulfillmentAllowsPickupPhase = (fs) => activePickupFulfillments.includes(fs);

    const liveUrl = root.getAttribute('data-live-url') || '';
    const pickupForm = document.getElementById('mitra-pickup-form');
    const baselinePvInitial = root.getAttribute('data-baseline-status') || '';
    const baselineFsInitial = root.getAttribute('data-baseline-fulfillment') || '';
    let baselinePv = baselinePvInitial;
    let baselineFulfillment = baselineFsInitial;

    const statusDd = document.getElementById('mitra-pv-status');
    const remainDd = document.getElementById('mitra-pv-remaining');
    const serverClockEl = document.getElementById('mitra-pv-server-time');
    const startedTextEl = document.getElementById('mitra-pv-started-text');
    const deadlineTextEl = document.getElementById('mitra-pv-deadline-text');
    const fulfillmentValueEl = document.getElementById('mitra-pv-fulfillment-value');
    const alertPanel = document.getElementById('mitra-pv-alert');

    /** @type {{ ms: number, perf: number } | null} */
    let serverClockAnchor = null;

    /** @type {HTMLButtonElement | null} */
    const submitBtn =
        pickupForm?.querySelector('button[type="submit"]') ??
        /** @type {HTMLButtonElement | null} */ (document.getElementById('mitra_pickup_submit'));

    let pendingShowForm = root.getAttribute('data-initial-show-form') === '1';
    let latestPv = baselinePvInitial;
    let latestFs = baselineFsInitial;

    let serverSecondsRemain = Number.parseInt(root.getAttribute('data-initial-seconds') || '0', 10);

    let countdownId;
    /** @type {undefined|boolean} */
    let pollTimerId;

    function applySeconds(live, fs) {
        if (!remainDd || !live) return;

        if (live.pickup_validation_status === 'pickup_validation_pending' && fulfillmentAllowsPickupPhase(fs)) {
            const sec =
                typeof live.pickup_validation_seconds_remaining === 'number'
                    ? live.pickup_validation_seconds_remaining
                    : serverSecondsRemain;
            serverSecondsRemain = sec;
            remainDd.textContent =
                serverSecondsRemain > 0 ? fmtHMS(serverSecondsRemain) : '—';

            return;
        }

        remainDd.textContent = '—';
        serverSecondsRemain = 0;
    }

    /** Sync label tanggal dari respons server (polling). */
    function applyTimestampFields(live) {
        if (fulfillmentValueEl && typeof live.fulfillment_status === 'string' && live.fulfillment_status) {
            fulfillmentValueEl.textContent = live.fulfillment_status;
        }

        if (startedTextEl && live.pickup_validation_started_at) {
            const t = formatIsoOrMsInTimezone(String(live.pickup_validation_started_at), appTimezone);
            if (t) startedTextEl.textContent = t;
        }

        if (deadlineTextEl && live.pickup_validation_deadline_at) {
            const t = formatIsoOrMsInTimezone(String(live.pickup_validation_deadline_at), appTimezone);
            if (t) deadlineTextEl.textContent = t;
        }
    }

    function renderServerClock() {
        if (!serverClockEl) return;
        if (!serverClockAnchor) return;

        const nowMs =
            serverClockAnchor.ms +
            Math.max(
                0,
                typeof performance?.now === 'function' ? performance.now() - serverClockAnchor.perf : 0,
            );
        const t = formatIsoOrMsInTimezone(nowMs, appTimezone);

        if (t) serverClockEl.textContent = t;
    }

    /** Form HTML selalu ada bila bisa submit dari server — sembunyikan jika polling mengatakan tidak pending */
    function syncFormVisibility(live, fs) {
        pendingShowForm =
            fulfillmentAllowsPickupPhase(fs) && live?.pickup_validation_status === 'pickup_validation_pending';

        if (!pickupForm) return;

        if (pendingShowForm && serverSecondsRemain > 0) {
            pickupForm.classList.remove('hidden');
            if (submitBtn) submitBtn.disabled = false;
            return;
        }

        pickupForm.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = true;
    }

    function tickLocalCountdown() {
        if (
            latestPv === 'pickup_validation_pending'
            && fulfillmentAllowsPickupPhase(latestFs)
            && serverSecondsRemain > 0
        ) {
            serverSecondsRemain -= 1;
        }

        renderServerClock();

        if (
            latestPv === 'pickup_validation_pending'
            && fulfillmentAllowsPickupPhase(latestFs)
            && remainDd
        ) {
            remainDd.textContent =
                serverSecondsRemain > 0 ? fmtHMS(serverSecondsRemain) : '—';
            if (
                serverSecondsRemain <= 0 &&
                pickupForm &&
                ! pickupForm.classList.contains('hidden') &&
                submitBtn
            ) {
                submitBtn.disabled = true;
            }
        }
    }

    async function pollOnce() {
        if (!liveUrl) return null;
        try {
            const res = await fetch(liveUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            if (!res.ok) return null;
            return await res.json();
        } catch {
            return null;
        }
    }

    function scheduleLoop() {
        if (pollTimerId) window.clearTimeout(pollTimerId);
        pollTimerId = window.setTimeout(loop, POLL_MS);
    }

    async function loop() {
        const envelope = await pollOnce();
        if (!envelope?.pickup_live) {
            scheduleLoop();
            return;
        }

        const live = envelope.pickup_live;
        const pv = typeof live.pickup_validation_status === 'string' ? live.pickup_validation_status : '';
        const fsResp = typeof live.fulfillment_status === 'string' ? live.fulfillment_status : '';

        if (pv !== baselinePv || fsResp !== baselineFulfillment) {
            window.location.reload();
            return;
        }

        latestPv = pv;
        latestFs = fsResp;

        const iso = typeof live.server_time_iso === 'string' ? live.server_time_iso : '';
        if (iso) {
            const parsed = Date.parse(iso);

            if (!Number.isNaN(parsed)) {
                serverClockAnchor = {
                    ms: parsed,
                    perf: typeof performance?.now === 'function' ? performance.now() : 0,
                };
                renderServerClock();
            }
        }

        patchStatusLabel(statusDd, pv || null, fsResp || null);
        applyTimestampFields(live);
        applySeconds(live, fsResp);
        syncFormVisibility(live, fsResp);

        if (pendingShowForm && serverSecondsRemain <= 0 && submitBtn) {
            submitBtn.disabled = true;
            showAlert(
                alertPanel,
                'err',
                'Batas waktu habis — submit tidak tersedia. Status telah disinkronkan dari server.',
            );
        }

        scheduleLoop();
    }

    const bootstrapIso = root.getAttribute('data-page-server-iso') || '';

    if (bootstrapIso) {
        const bt = Date.parse(bootstrapIso);

        if (!Number.isNaN(bt)) {
            serverClockAnchor = {
                ms: bt,
                perf: typeof performance?.now === 'function' ? performance.now() : 0,
            };
            renderServerClock();
        }
    }

    countdownId = window.setInterval(tickLocalCountdown, 1000);

    if (remainDd && pickupForm && ! pickupForm.classList.contains('hidden')) {
        const ini = Number.parseInt(root.getAttribute('data-initial-seconds') || '0', 10);

        remainDd.textContent = ini > 0 ? fmtHMS(ini) : '—';
    }

    if (submitBtn && pendingShowForm && serverSecondsRemain > 0) {
        submitBtn.disabled = false;
    }

    pickupForm?.addEventListener('submit', () => {
        if (submitBtn) submitBtn.disabled = true;
    });

    loop();

    window.addEventListener('beforeunload', () => {
        if (countdownId) window.clearInterval(countdownId);
        if (pollTimerId) window.clearTimeout(pollTimerId);
    });
}
