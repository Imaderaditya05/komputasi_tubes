<script>
(function () {
    function reduced() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }
    function fmt(n) {
        return new Intl.NumberFormat('id-ID').format(n);
    }
    /** easeOutQuint — smooth deceleration */
    function easeOutQuint(t) {
        return 1 - Math.pow(1 - t, 5);
    }
    function animateCount(el, target, decimals, ms) {
        var end = Number(target);
        if (!isFinite(end)) return;
        if (reduced()) {
            el.textContent = decimals > 0 ? String(end).replace('.', ',') : fmt(Math.round(end));
            return;
        }
        var t0 = performance.now();
        function tick(now) {
            var t = Math.min(1, (now - t0) / ms);
            var eased = easeOutQuint(t);
            var v = end * eased;
            if (decimals > 0) {
                var shown = t >= 1 ? end : v;
                el.textContent = shown.toFixed(decimals).replace(/\.?0+$/, '').replace('.', ',') || '0';
            } else {
                el.textContent = fmt(Math.round(t >= 1 ? end : v));
            }
            if (t < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    function revealIn(el) {
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                el.classList.add('sb-reveal--in');
            });
        });
    }

    function initReveals() {
        if (reduced()) {
            document.querySelectorAll('.sb-reveal').forEach(function (el) {
                el.classList.add('sb-reveal--in');
            });
            return;
        }
        var io = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (en) {
                    if (!en.isIntersecting) return;
                    io.unobserve(en.target);
                    revealIn(en.target);
                });
            },
            { threshold: 0.06, rootMargin: '0px 0px -5% 0px' }
        );

        document.querySelectorAll('.sb-reveal-group').forEach(function (group) {
            group.querySelectorAll('.sb-reveal').forEach(function (el, i) {
                el.style.transitionDelay = i * 0.1 + 's';
                io.observe(el);
            });
        });

        document.querySelectorAll('.sb-reveal').forEach(function (el) {
            if (el.closest('.sb-reveal-group')) return;
            el.style.transitionDelay = '0.05s';
            io.observe(el);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-sb-count]').forEach(function (el) {
            var raw = el.getAttribute('data-sb-count');
            var target = raw === null ? NaN : parseFloat(raw);
            var dec = parseInt(el.getAttribute('data-sb-decimals') || '0', 10);
            var ms = parseInt(el.getAttribute('data-sb-duration') || '1400', 10);
            if (!isFinite(target)) return;
            el.textContent = dec > 0 ? (0).toFixed(dec).replace('.', ',') : '0';
            animateCount(el, target, dec, ms);
        });
        initReveals();
    });
})();
</script>
