/**
 * Validasi cepat di browser — pesan "tidak boleh kosong" / "tidak valid" dalam bahasa Indonesia.
 * Server-side Laravel tetap sumber utama; ini melengkapi input HTML5 tanpa bahasa Inggris bawaan browser.
 */

export function initIndonesianFormValidityMessages() {
    document.addEventListener(
        'input',
        (e) => {
            const el = e.target;
            if (
                el instanceof HTMLInputElement ||
                el instanceof HTMLTextAreaElement ||
                el instanceof HTMLSelectElement
            ) {
                el.setCustomValidity('');
            }
        },
        true,
    );

    document.addEventListener(
        'invalid',
        (e) => {
            const t = e.target;
            if (
                !(t instanceof HTMLInputElement) &&
                !(t instanceof HTMLTextAreaElement) &&
                !(t instanceof HTMLSelectElement)
            ) {
                return;
            }

            let msg = '';

            if (t.validity.valueMissing) {
                msg = 'Data tidak boleh kosong. Kolom ini wajib diisi.';
            } else if (t.validity.badInput) {
                msg = 'Data tidak valid.';
            } else if (t.validity.typeMismatch) {
                msg =
                    t instanceof HTMLInputElement && t.type === 'email'
                        ? 'Masukkan alamat email yang valid.'
                        : 'Format data tidak valid.';
            } else if (t.validity.patternMismatch) {
                msg = 'Format isian tidak valid.';
            } else if (t.validity.tooShort) {
                const n = typeof t.minLength === 'number' ? t.minLength : '';
                msg = `Isian tidak valid — minimal ${n} karakter.`;
            } else if (t.validity.tooLong) {
                const n = typeof t.maxLength === 'number' ? t.maxLength : '';
                msg = `Isian tidak valid — maksimal ${n} karakter.`;
            } else if (t.validity.rangeUnderflow || t.validity.rangeOverflow || t.validity.stepMismatch) {
                msg = 'Nilai tidak valid.';
            }

            if (msg) {
                t.setCustomValidity(msg);
            }
        },
        true,
    );
}
