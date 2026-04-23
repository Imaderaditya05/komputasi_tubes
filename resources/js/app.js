import './bootstrap';
import { startAdminRealtime } from './admin-realtime';
import { startSiteRealtime } from './site-realtime';
import { initOrderTrackingMap } from './order-tracking-maps';

document.addEventListener('DOMContentLoaded', () => {
    startAdminRealtime();
    startSiteRealtime();
    initOrderTrackingMap();
});

