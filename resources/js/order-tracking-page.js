const DEMO_INTERVAL_MS = 25_000;

function readCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function initDemoAutoAdvance(root) {
    if (root.getAttribute('data-demo-auto') !== '1') return;
    const url = root.getAttribute('data-demo-advance-url');
    if (!url) return;

    const token = readCsrfToken();
    if (!token) return;

    let intervalId = 0;

    const tick = async () => {
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                body: new URLSearchParams({ _token: token }),
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data?.completed) {
                window.clearInterval(intervalId);
            }
            window.location.reload();
        } catch {
            /* abaikan — polling live tetap bisa memperbarui */
        }
    };

    intervalId = window.setInterval(tick, DEMO_INTERVAL_MS);
}

function initCourierChat(root) {
    const url = root.getAttribute('data-courier-chat-url');
    if (!url) return;

    const openBtn = document.getElementById('courier-chat-open');
    const backdrop = document.getElementById('courier-chat-backdrop');
    const closeBtn = document.getElementById('courier-chat-close');
    const panel = document.getElementById('courier-chat-panel');
    const messagesEl = document.getElementById('courier-chat-messages');
    const input = document.getElementById('courier-chat-input');
    const sendBtn = document.getElementById('courier-chat-send');

    if (!openBtn || !backdrop || !closeBtn || !messagesEl || !input || !sendBtn) return;

    const token = readCsrfToken();
    /** @type {{ role: 'user' | 'assistant', content: string }[]} */
    let transcript = [];

    const appendBubble = (role, text) => {
        const wrap = document.createElement('div');
        wrap.className =
            role === 'user'
                ? 'flex justify-end'
                : 'flex justify-start';

        const bubble = document.createElement('div');
        bubble.className =
            role === 'user'
                ? 'max-w-[85%] rounded-2xl bg-[#00a63e] px-3 py-2 text-sm font-semibold text-white shadow-sm'
                : 'max-w-[85%] rounded-2xl bg-slate-100 px-3 py-2 text-sm font-semibold text-[#1e2939] shadow-sm ring-1 ring-slate-200';
        bubble.textContent = text;

        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    };

    const setBusy = (busy) => {
        sendBtn.disabled = busy;
        input.disabled = busy;
        sendBtn.classList.toggle('opacity-60', busy);
    };

    const open = () => {
        backdrop.classList.remove('hidden');
        backdrop.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        if (transcript.length === 0) {
            const hi =
                'Halo! Saya bot kurir otomatis untuk pesanan ini. Silakan tanya apa saja—status, estimasi, cara pickup/delivery, atau hal lain terkait pesanan Anda; saya bantu sebisanya.';
            transcript.push({ role: 'assistant', content: hi });
            appendBubble('assistant', hi);
        }
        input.focus();
    };

    const close = () => {
        backdrop.classList.add('hidden');
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        open();
    });
    closeBtn.addEventListener('click', () => close());
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) close();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !backdrop.classList.contains('hidden')) close();
    });

    const send = async () => {
        const text = input.value.trim();
        if (!text || !token) return;
        input.value = '';
        transcript.push({ role: 'user', content: text });
        appendBubble('user', text);
        setBusy(true);
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ messages: transcript }),
                credentials: 'same-origin',
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const err = typeof data.error === 'string' ? data.error : 'Gagal mengirim pesan ke bot. Coba lagi.';
                appendBubble('assistant', err);
                transcript.push({ role: 'assistant', content: err });
                return;
            }
            const reply = typeof data.reply === 'string' ? data.reply : 'Maaf, tidak ada balasan.';
            transcript.push({ role: 'assistant', content: reply });
            appendBubble('assistant', reply);
        } catch {
            const err = 'Koneksi bermasalah. Periksa internet lalu coba lagi.';
            transcript.push({ role: 'assistant', content: err });
            appendBubble('assistant', err);
        } finally {
            setBusy(false);
            input.focus();
        }
    };

    sendBtn.addEventListener('click', (e) => {
        e.preventDefault();
        send();
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            send();
        }
    });

    if (panel) {
        panel.addEventListener('click', (e) => e.stopPropagation());
    }
}

function initRestaurantChat(root) {
    const url = root.getAttribute('data-restaurant-chat-url');
    if (!url) return;

    const openBtn = document.getElementById('restaurant-chat-open');
    const backdrop = document.getElementById('restaurant-chat-backdrop');
    const closeBtn = document.getElementById('restaurant-chat-close');
    const panel = document.getElementById('restaurant-chat-panel');
    const messagesEl = document.getElementById('restaurant-chat-messages');
    const input = document.getElementById('restaurant-chat-input');
    const sendBtn = document.getElementById('restaurant-chat-send');

    if (!openBtn || !backdrop || !closeBtn || !messagesEl || !input || !sendBtn) return;

    const token = readCsrfToken();
    /** @type {{ role: 'user' | 'assistant', content: string }[]} */
    let transcript = [];

    const appendBubble = (role, text) => {
        const wrap = document.createElement('div');
        wrap.className =
            role === 'user'
                ? 'flex justify-end'
                : 'flex justify-start';

        const bubble = document.createElement('div');
        bubble.className =
            role === 'user'
                ? 'max-w-[85%] rounded-2xl bg-[#00a63e] px-3 py-2 text-sm font-semibold text-white shadow-sm'
                : 'max-w-[85%] rounded-2xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-[#1e2939] shadow-sm ring-1 ring-emerald-100';
        bubble.textContent = text;

        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    };

    const setBusy = (busy) => {
        sendBtn.disabled = busy;
        input.disabled = busy;
        sendBtn.classList.toggle('opacity-60', busy);
    };

    const open = () => {
        backdrop.classList.remove('hidden');
        backdrop.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        if (transcript.length === 0) {
            const hi =
                'Halo! Saya bot admin toko otomatis. Silakan tanya apa saja seputar restoran, pesanan, lokasi, atau SurpriseBite—saya bantu sebisanya.';
            transcript.push({ role: 'assistant', content: hi });
            appendBubble('assistant', hi);
        }
        input.focus();
    };

    const close = () => {
        backdrop.classList.add('hidden');
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        open();
    });
    closeBtn.addEventListener('click', () => close());
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) close();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !backdrop.classList.contains('hidden')) close();
    });

    const send = async () => {
        const text = input.value.trim();
        if (!text || !token) return;
        input.value = '';
        transcript.push({ role: 'user', content: text });
        appendBubble('user', text);
        setBusy(true);
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ messages: transcript }),
                credentials: 'same-origin',
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const err = typeof data.error === 'string' ? data.error : 'Gagal mengirim pesan ke bot. Coba lagi.';
                appendBubble('assistant', err);
                transcript.push({ role: 'assistant', content: err });
                return;
            }
            const reply = typeof data.reply === 'string' ? data.reply : 'Maaf, tidak ada balasan.';
            transcript.push({ role: 'assistant', content: reply });
            appendBubble('assistant', reply);
        } catch {
            const err = 'Koneksi bermasalah. Periksa internet lalu coba lagi.';
            transcript.push({ role: 'assistant', content: err });
            appendBubble('assistant', err);
        } finally {
            setBusy(false);
            input.focus();
        }
    };

    sendBtn.addEventListener('click', (e) => {
        e.preventDefault();
        send();
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            send();
        }
    });

    if (panel) {
        panel.addEventListener('click', (e) => e.stopPropagation());
    }
}

function initOrderRatingStars() {
    const root = document.querySelector('[data-order-rating-form]');
    if (!root) return;
    const input = document.getElementById('order-rating-input');
    const stars = root.querySelectorAll('[data-order-rating-star]');
    const form = root.querySelector('form');

    if (!input || !stars.length || !form) return;

    const apply = (n) => {
        const val = Number.isFinite(n) && n >= 1 && n <= 5 ? n : 0;
        input.value = val > 0 ? String(val) : '';
        stars.forEach((btn) => {
            const v = parseInt(btn.getAttribute('data-order-rating-star') || '0', 10);
            const on = val > 0 && v <= val;
            btn.classList.toggle('text-amber-400', on);
            btn.classList.toggle('text-slate-300', !on);
        });
    };

    const initial = parseInt(String(input.value || ''), 10);
    if (initial >= 1 && initial <= 5) {
        apply(initial);
    }

    stars.forEach((btn) => {
        btn.addEventListener('click', () => {
            const n = parseInt(btn.getAttribute('data-order-rating-star') || '0', 10);
            apply(n);
        });
    });

    form.addEventListener('submit', (e) => {
        const v = parseInt(String(input.value || ''), 10);
        if (!v || v < 1 || v > 5) {
            e.preventDefault();
            window.alert('Silakan pilih rating bintang 1–5.');
        }
    });
}

export function initOrderTrackingPage() {
    const root = document.querySelector('[data-order-track-page]');
    if (!root) return;
    initDemoAutoAdvance(root);
    initRestaurantChat(root);
    initCourierChat(root);
    initOrderRatingStars();
}
