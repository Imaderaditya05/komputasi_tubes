{{-- Smooth motion system — mirrored in resources/css/app.css for Vite --}}
<style>
    :root {
        --sb-ease-out: cubic-bezier(0.16, 1, 0.3, 1);
        --sb-ease-soft: cubic-bezier(0.33, 1, 0.68, 1);
        --sb-ease-spring: cubic-bezier(0.34, 1.2, 0.64, 1);
        /* Hover: smooth “modern UI” deceleration */
        --sb-hover: cubic-bezier(0.23, 1, 0.32, 1);
    }

    html:has(body.sb-marketing-canvas) {
        scroll-behavior: smooth;
    }

    .sb-marketing-canvas {
        background-color: #fafafa;
        background-image:
            radial-gradient(ellipse 55% 90% at 0% 15%, rgba(34, 197, 94, 0.11), transparent 55%),
            radial-gradient(ellipse 50% 85% at 100% 70%, rgba(251, 146, 60, 0.1), transparent 52%),
            radial-gradient(ellipse 40% 50% at 50% 100%, rgba(16, 185, 129, 0.06), transparent 50%);
        background-attachment: fixed;
    }

    @keyframes sb-fade-up {
        from {
            opacity: 0;
            transform: translate3d(0, 1.75rem, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes sb-scale-in {
        from {
            opacity: 0;
            transform: scale(0.92);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes sb-soft-float {
        0%,
        100% {
            transform: translate3d(0, 0, 0);
        }
        50% {
            transform: translate3d(0, -10px, 0);
        }
    }

    @keyframes sb-gradient-flow {
        0%,
        100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }

    @keyframes sb-shine-sweep {
        0%,
        65% {
            transform: translate3d(-120%, 0, 0) skewX(-12deg);
        }
        100% {
            transform: translate3d(220%, 0, 0) skewX(-12deg);
        }
    }

    @keyframes sb-orb-drift {
        0%,
        100% {
            transform: translate3d(0, 0, 0) scale(1);
        }
        33% {
            transform: translate3d(22px, -14px, 0) scale(1.07);
        }
        66% {
            transform: translate3d(-18px, 16px, 0) scale(0.95);
        }
    }

    @keyframes sb-logo-pulse {
        0%,
        100% {
            box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.45);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 16px 36px -4px rgba(249, 115, 22, 0.55);
            transform: scale(1.045);
        }
    }

    @keyframes sb-card-glimmer {
        0%,
        100% {
            opacity: 0;
            transform: translate3d(-100%, 0, 0) rotate(12deg);
        }
        48% {
            opacity: 0;
        }
        50% {
            opacity: 0.28;
        }
        52% {
            opacity: 0;
        }
        100% {
            transform: translate3d(200%, 0, 0) rotate(12deg);
        }
    }

    .sb-hero-sheen {
        position: relative;
        isolation: isolate;
    }
    .sb-hero-sheen::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        z-index: 0;
        background: linear-gradient(
            125deg,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.14) 35%,
            rgba(253, 199, 0, 0.18) 50%,
            rgba(255, 255, 255, 0.1) 65%,
            rgba(255, 255, 255, 0) 100%
        );
        background-size: 240% 240%;
        animation: sb-gradient-flow 20s ease-in-out infinite;
        pointer-events: none;
        will-change: background-position;
    }
    .sb-hero-sheen > * {
        position: relative;
        z-index: 1;
    }

    .sb-orb-drift {
        animation: sb-orb-drift 20s var(--sb-ease-soft) infinite;
        will-change: transform;
    }
    .sb-orb-drift--alt {
        animation: sb-orb-drift 26s var(--sb-ease-soft) infinite reverse;
    }

    .sb-btn-shine {
        position: relative;
        overflow: hidden;
    }
    .sb-btn-shine::after {
        content: '';
        position: absolute;
        inset: -20%;
        background: linear-gradient(105deg, transparent 35%, rgba(255, 255, 255, 0.5) 50%, transparent 65%);
        animation: sb-shine-sweep 5.5s var(--sb-ease-soft) infinite;
        pointer-events: none;
    }

    .sb-stat-shine {
        position: relative;
        overflow: hidden;
    }
    .sb-stat-shine::after {
        content: '';
        position: absolute;
        top: -50%;
        left: 0;
        width: 45%;
        height: 200%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.22), transparent);
        animation: sb-card-glimmer 8s var(--sb-ease-soft) infinite;
        pointer-events: none;
    }

    .sb-reveal {
        opacity: 0;
        transform: translate3d(0, 2.25rem, 0) scale(0.97);
        filter: blur(8px);
        transition:
            opacity 1.15s var(--sb-ease-out),
            transform 1.1s var(--sb-ease-out),
            filter 0.95s var(--sb-ease-soft);
        will-change: opacity, transform, filter;
    }
    .sb-reveal.sb-reveal--in {
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
        filter: blur(0);
        will-change: auto;
    }

    .sb-img-zoom {
        transition: transform 1.05s var(--sb-hover);
        will-change: transform;
    }
    .group:hover .sb-img-zoom {
        transform: scale(1.08);
    }

    /* White / surface cards — soft lift + brand rim glow */
    @media (prefers-reduced-motion: no-preference) {
        .sb-hover-lift {
            transition:
                transform 0.55s var(--sb-hover),
                box-shadow 0.55s var(--sb-hover),
                filter 0.45s ease;
            transform: translate3d(0, 0, 0);
        }
        .sb-hover-lift:hover {
            transform: translate3d(0, -10px, 0) scale(1.012);
            box-shadow:
                0 4px 8px -2px rgba(0, 0, 0, 0.06),
                0 20px 40px -12px rgba(0, 0, 0, 0.12),
                0 0 0 1px rgba(0, 166, 62, 0.14),
                0 16px 48px -12px rgba(0, 166, 62, 0.18);
            filter: brightness(1.02);
        }

        /* Orange / warm CTA on green hero */
        .sb-hover-lift--warm:hover {
            box-shadow:
                0 6px 20px -4px rgba(245, 73, 0, 0.55),
                0 20px 44px -10px rgba(255, 105, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.2) inset;
            filter: brightness(1.06) saturate(1.05);
            transform: translate3d(0, -10px, 0) scale(1.035);
        }

        /* White pill on orange section */
        .sb-hover-lift--light:hover {
            box-shadow:
                0 8px 24px -4px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.65),
                0 20px 50px -12px rgba(0, 166, 62, 0.35);
            filter: brightness(1.03);
            transform: translate3d(0, -8px, 0) scale(1.04);
        }

        /* Gradient stat tiles (home + impact) */
        .sb-hover-stat {
            transition:
                transform 0.55s var(--sb-hover),
                box-shadow 0.55s var(--sb-hover),
                filter 0.5s ease;
        }
        .sb-hover-stat:hover {
            transform: translate3d(0, -10px, 0) scale(1.02);
            box-shadow:
                0 12px 28px -6px rgba(0, 0, 0, 0.28),
                0 0 0 1px rgba(255, 255, 255, 0.22) inset,
                0 24px 56px -12px rgba(0, 0, 0, 0.22);
            filter: brightness(1.09) saturate(1.07);
        }

        .sb-btn-shine {
            transition:
                transform 0.45s var(--sb-hover),
                filter 0.4s ease,
                box-shadow 0.45s var(--sb-hover);
        }
        .sb-btn-shine:hover {
            transform: translate3d(0, -3px, 0) scale(1.03);
            filter: brightness(1.05);
        }

        .sb-hover-header-btn {
            transition:
                transform 0.42s var(--sb-hover),
                box-shadow 0.42s var(--sb-hover),
                filter 0.35s ease;
        }
        .sb-hover-header-btn:hover {
            transform: translate3d(0, -3px, 0) scale(1.045);
            box-shadow: 0 14px 32px -8px rgba(0, 166, 62, 0.5);
            filter: brightness(1.07);
        }

        .sb-hover-header-outline {
            transition:
                transform 0.42s var(--sb-hover),
                box-shadow 0.42s var(--sb-hover),
                background-color 0.35s ease;
        }
        .sb-hover-header-outline:hover {
            transform: translate3d(0, -3px, 0) scale(1.04);
            box-shadow: 0 12px 28px -8px rgba(0, 166, 62, 0.22);
        }

        .sb-hover-icon-btn {
            transition: transform 0.4s var(--sb-hover), background-color 0.3s ease;
        }
        .sb-hover-icon-btn:hover {
            transform: scale(1.08);
        }
        .sb-hover-icon-btn:active {
            transform: scale(0.96);
        }

        .sb-hover-chip {
            transition:
                transform 0.4s var(--sb-hover),
                box-shadow 0.4s var(--sb-hover),
                background-color 0.3s ease,
                color 0.25s ease;
        }
        .sb-hover-chip:hover {
            transform: translate3d(0, -4px, 0) scale(1.04);
            box-shadow: 0 12px 28px -10px rgba(0, 166, 62, 0.28);
        }

        .sb-nav-item {
            position: relative;
            transition:
                transform 0.45s var(--sb-hover),
                color 0.35s ease,
                text-shadow 0.4s ease;
        }
        .sb-nav-item::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -8px;
            height: 3px;
            border-radius: 999px;
            background: linear-gradient(90deg, #00a63e, #00bc7d);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.5s var(--sb-hover);
            opacity: 0.95;
            pointer-events: none;
        }
        .sb-nav-item:hover {
            transform: translate3d(0, -4px, 0);
            text-shadow: 0 8px 24px rgba(0, 166, 62, 0.2);
        }
        .sb-nav-item:hover::after {
            transform: scaleX(1);
        }
        .sb-nav-item--active::after {
            transform: scaleX(1);
        }

        .sb-animate-up {
            animation: sb-fade-up 1.05s var(--sb-ease-out) both;
        }
        .sb-animate-scale {
            animation: sb-scale-in 0.95s var(--sb-ease-out) both;
        }
        .sb-delay-1 {
            animation-delay: 0.12s;
        }
        .sb-delay-2 {
            animation-delay: 0.24s;
        }
        .sb-delay-3 {
            animation-delay: 0.36s;
        }
        .sb-delay-4 {
            animation-delay: 0.48s;
        }
        .sb-delay-5 {
            animation-delay: 0.6s;
        }

        .sb-float-slow {
            animation: sb-soft-float 6.5s ease-in-out infinite;
        }
        .sb-logo-pulse {
            animation: sb-logo-pulse 3.8s ease-in-out infinite;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        html:has(body.sb-marketing-canvas) {
            scroll-behavior: auto;
        }
        .sb-animate-up,
        .sb-animate-scale {
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
        .sb-float-slow,
        .sb-orb-drift,
        .sb-orb-drift--alt,
        .sb-logo-pulse {
            animation: none !important;
        }
        .sb-hero-sheen::before,
        .sb-btn-shine::after,
        .sb-stat-shine::after {
            animation: none !important;
            opacity: 0 !important;
        }
        .sb-reveal {
            opacity: 1 !important;
            transform: none !important;
            filter: none !important;
            will-change: auto !important;
        }
        .group:hover .sb-img-zoom {
            transform: none;
        }
        .sb-hover-lift:hover,
        .sb-hover-lift--warm:hover,
        .sb-hover-lift--light:hover,
        .sb-hover-stat:hover,
        .sb-btn-shine:hover,
        .sb-hover-header-btn:hover,
        .sb-hover-header-outline:hover,
        .sb-hover-icon-btn:hover,
        .sb-hover-chip:hover {
            transform: none !important;
            filter: none !important;
            box-shadow: none !important;
        }
        .sb-nav-item,
        .sb-nav-item::after {
            transform: none !important;
            transition: none !important;
            text-shadow: none !important;
        }
        .sb-nav-item--active::after {
            opacity: 0.95 !important;
            transform: scaleX(1) !important;
        }
    }
</style>
