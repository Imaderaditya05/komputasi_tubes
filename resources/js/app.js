import './bootstrap';
import { startAdminRealtime } from './admin-realtime';
import { startSiteRealtime } from './site-realtime';
import { initOrderTrackingMap } from './order-tracking-maps';
import { initAdminUsersPage } from './admin-users';
import { initIndonesianFormValidityMessages } from './form-validation-i18n';
import { bindStockUnavailableNotifyDelegation } from './stock-unavailable-notify';

document.addEventListener('DOMContentLoaded', () => {
    bindStockUnavailableNotifyDelegation();
    initIndonesianFormValidityMessages();
    startAdminRealtime();
    startSiteRealtime();
    initOrderTrackingMap();
    if (document.getElementById('rt-restaurants-grid')) {
        import('./admin-restaurants').then((m) => m.initAdminRestaurantsPage());
    }
    if (document.getElementById('admin-users-config')) {
        initAdminUsersPage();
    }
    if (document.querySelector('[data-order-track-page]')) {
        import('./order-tracking-page').then((m) => m.initOrderTrackingPage());
    }
    if (document.querySelector('[data-mitra-pickup-page]')) {
        import('./mitra-pickup-validation').then((m) => m.initMitraPickupPage());
    }
});

