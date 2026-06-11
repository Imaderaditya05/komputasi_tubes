/** Notifikasi saat pengguna mencoba Grab It / Add to Cart untuk menu tanpa stok. */
const DEFAULT_OOS_MESSAGE = 'Menu tidak dapat ditambahkan karena stok tidak tersedia.';

const SB_OOS_GUARD = '__sbStockOosNotifyBound';

export function showSbStockUnavailableToast(message) {
    const text = typeof message === 'string' && message.trim() !== '' ? message.trim() : DEFAULT_OOS_MESSAGE;
    document.getElementById('sb-stock-toast')?.remove();

    const el = document.createElement('div');
    el.id = 'sb-stock-toast';
    el.setAttribute('role', 'alert');
    el.textContent = text;
    Object.assign(el.style, {
        position: 'fixed',
        bottom: '1.5rem',
        left: '50%',
        zIndex: '99999',
        maxWidth: 'min(calc(100vw - 2rem), 24rem)',
        padding: '0.875rem 1rem',
        borderRadius: '1rem',
        border: '2px solid #fcd34d',
        background: '#fffbeb',
        color: '#78350f',
        fontSize: '0.875rem',
        fontWeight: '700',
        boxShadow: '0 10px 25px rgba(0,0,0,0.12)',
        opacity: '0',
        transform: 'translate(-50%, 8px)',
        transition: 'opacity 0.25s ease, transform 0.25s ease',
        pointerEvents: 'none',
        textAlign: 'center',
        lineHeight: '1.45',
    });
    document.body.appendChild(el);

    requestAnimationFrame(() => {
        el.style.opacity = '1';
        el.style.transform = 'translate(-50%, 0)';
    });

    window.setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translate(-50%, 8px)';
        window.setTimeout(() => el.remove(), 320);
    }, 4200);
}

/** Satu delegation global — tombol bermarkah data-sb-oos-notify. */
export function bindStockUnavailableNotifyDelegation() {
    if (typeof document === 'undefined') {
        return;
    }
    if (typeof globalThis !== 'undefined' && globalThis[SB_OOS_GUARD] === true) {
        return;
    }
    if (typeof globalThis !== 'undefined') {
        globalThis[SB_OOS_GUARD] = true;
    }

    document.addEventListener(
        'click',
        (e) => {
            const el = e.target.closest('[data-sb-oos-notify]');
            if (!el) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();

            const msg = el.getAttribute('data-sb-oos-message') ?? DEFAULT_OOS_MESSAGE;
            showSbStockUnavailableToast(msg);
        },
        true,
    );
}
