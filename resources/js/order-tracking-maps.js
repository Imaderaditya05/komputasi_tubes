/**
 * Peta lacak pesanan: delivery (rute + kurir realtime / simulasi) atau pickup.
 * Membutuhkan GOOGLE_MAPS_API_KEY + Maps JavaScript API, Directions API, Geocoding API.
 */

function loadGoogleMaps(key) {
    return new Promise((resolve, reject) => {
        if (window.google?.maps?.Map) {
            resolve();
            return;
        }
        const cbName = `__sbGmaps_${Date.now()}`;
        window[cbName] = () => {
            resolve();
            delete window[cbName];
        };
        const s = document.createElement('script');
        s.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&libraries=geometry&callback=${cbName}`;
        s.async = true;
        s.onerror = () => reject(new Error('maps_load'));
        document.head.appendChild(s);
    });
}

function parsePayload() {
    const el = document.getElementById('order-track-map-data');
    if (!el) return null;
    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function geocode(geocoder, address) {
    const trimmed = (address || '').trim();
    if (!trimmed) {
        return Promise.reject(new Error('EMPTY'));
    }
    const stripped = trimmed.replace(/,\s*indonesia\s*$/i, '').trim();
    const variants = trimmed === stripped ? [trimmed] : [trimmed, stripped];

    const attempts = [];
    for (const addr of variants) {
        attempts.push(
            { address: addr, region: 'id', language: 'id', componentRestrictions: { country: 'id' } },
            { address: addr, region: 'id', language: 'id' },
        );
    }

    return (async () => {
        for (const req of attempts) {
            try {
                return await new Promise((resolve, reject) => {
                    geocoder.geocode(req, (results, status) => {
                        if (status === 'OK' && results[0]) {
                            resolve(results[0].geometry.location);
                        } else {
                            reject(new Error(status));
                        }
                    });
                });
            } catch {
                /* coba variasi berikutnya */
            }
        }
        throw new Error('GEOCODE_FAIL');
    })();
}

/**
 * Selaras dengan PHP OrderMapLocationService::coordinatesPlausibleIndonesia.
 * Tanpa ini, (0,0) atau koordinat di luar ID bisa lolos → peta biru (laut) / Null Island.
 */
function isPlausibleIndonesia(lat, lng) {
    const la = Number(lat);
    const ln = Number(lng);
    if (!Number.isFinite(la) || !Number.isFinite(ln)) return false;
    if (Math.abs(la) < 1e-6 && Math.abs(ln) < 1e-6) return false;
    return la >= -11.5 && la <= 6.5 && ln >= 94.0 && ln <= 141.5;
}

function isValidRestaurantLatLng(lat, lng) {
    return isPlausibleIndonesia(lat, lng);
}

function geocodeResultPlausible(location) {
    if (!location) return false;
    const la = typeof location.lat === 'function' ? location.lat() : Number(location.lat);
    const ln = typeof location.lng === 'function' ? location.lng() : Number(location.lng);
    return isPlausibleIndonesia(la, ln);
}

function buildGeocodeQueries(payload) {
    const seen = new Set();
    const out = [];
    const push = (q) => {
        const s = (q || '').trim();
        if (!s || seen.has(s)) return;
        seen.add(s);
        out.push(s);
    };
    push(payload.mapQuery);
    if (Array.isArray(payload.mapQueryAlternates)) {
        payload.mapQueryAlternates.forEach((q) => push(q));
    }
    if (payload.restaurantName) {
        push(`${payload.restaurantName}, Indonesia`);
    }
    return out;
}

function pathToArray(path) {
    if (!path) return [];
    if (typeof path.getArray === 'function') return path.getArray();
    if (Array.isArray(path)) return path;
    const out = [];
    const n = path.length ?? 0;
    for (let i = 0; i < n; i++) {
        out.push(path.getAt ? path.getAt(i) : path[i]);
    }
    return out;
}

function flattenRoutePath(route) {
    if (!route?.legs?.[0]) return [];
    const pts = [];
    route.legs[0].steps.forEach((step) => {
        pathToArray(step.path).forEach((p) => pts.push(p));
    });
    if (pts.length > 0) return pts;
    return pathToArray(route.overview_path);
}

function pointAlongPath(path, t) {
    if (!path || path.length < 2) return path[0];
    const { spherical } = window.google.maps.geometry;
    let total = 0;
    const segLens = [];
    for (let i = 0; i < path.length - 1; i++) {
        const d = spherical.computeDistanceBetween(path[i], path[i + 1]);
        segLens.push(d);
        total += d;
    }
    if (total <= 0) return path[0];
    let target = t * total;
    for (let i = 0; i < path.length - 1; i++) {
        const seg = segLens[i];
        if (target <= seg) {
            return spherical.interpolate(path[i], path[i + 1], target / seg);
        }
        target -= seg;
    }
    return path[path.length - 1];
}

/**
 * Polling lokasi toko + kurir dari server (realtime).
 */
function startMapTrackingPoll(payload, ctx) {
    const url = payload.trackingPollUrl;
    if (!url) return () => {};

    const pollMs = Number(payload.pollIntervalMs) || 3000;

    const tick = async () => {
        try {
            const res = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const d = await res.json();

            if (d.restaurant_lat != null && d.restaurant_lng != null && ctx.restaurantMarker) {
                const la = Number(d.restaurant_lat);
                const ln = Number(d.restaurant_lng);
                if (isPlausibleIndonesia(la, ln)) {
                    const n = new window.google.maps.LatLng(la, ln);
                    ctx.restaurantMarker.setPosition(n);
                    ctx.restaurantLatLng = n;
                }
            }

            if (d.courier_lat != null && d.courier_lng != null && ctx.driverMarker) {
                ctx.driverMarker.setPosition(new window.google.maps.LatLng(d.courier_lat, d.courier_lng));
                ctx.driverMarker.setTitle('Kurir');
                if (ctx.hint && payload.mode === 'delivery') {
                    ctx.hint.textContent = 'Lokasi kurir diperbarui dari mitra (realtime).';
                }
                if (ctx.animTimer != null) {
                    clearInterval(ctx.animTimer);
                    ctx.animTimer = null;
                }
            }
        } catch {
            /* abaikan */
        }
    };

    const pollTimer = setInterval(tick, pollMs);
    tick();

    return () => {
        clearInterval(pollTimer);
        if (ctx.animTimer != null) {
            clearInterval(ctx.animTimer);
            ctx.animTimer = null;
        }
    };
}

export async function initOrderTrackingMap() {
    const payload = parsePayload();
    const root = document.getElementById('order-track-map');
    if (!payload?.showUi || !root) return;

    const hint = document.getElementById('order-track-map-hint');

    if (!payload.enabled || !payload.apiKey) {
        if (hint) hint.textContent = 'Tambahkan GOOGLE_MAPS_API_KEY di .env untuk menampilkan peta.';
        return;
    }

    try {
        await loadGoogleMaps(payload.apiKey);
    } catch {
        if (hint) hint.textContent = 'Gagal memuat Google Maps.';
        return;
    }

    const geocoder = new window.google.maps.Geocoder();

    let restaurantLatLng = null;
    let approximateRestaurant = false;

    if (isValidRestaurantLatLng(payload.restaurantLat, payload.restaurantLng)) {
        restaurantLatLng = new window.google.maps.LatLng(
            Number(payload.restaurantLat),
            Number(payload.restaurantLng),
        );
    } else {
        const queries = buildGeocodeQueries(payload);
        for (const q of queries) {
            try {
                const candidate = await geocode(geocoder, q);
                if (geocodeResultPlausible(candidate)) {
                    restaurantLatLng = candidate;
                    break;
                }
            } catch {
                /* coba query berikutnya */
            }
        }
        if (!restaurantLatLng) {
            const fLat = Number(payload.fallbackLat);
            const fLng = Number(payload.fallbackLng);
            const lat = Number.isFinite(fLat) && Math.abs(fLat) <= 90 ? fLat : -6.2088;
            const lng = Number.isFinite(fLng) && Math.abs(fLng) <= 180 ? fLng : 106.8456;
            restaurantLatLng = new window.google.maps.LatLng(lat, lng);
            approximateRestaurant = true;
        }
    }

    const map = new window.google.maps.Map(root, {
        center: restaurantLatLng,
        zoom: 14,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
    });

    const restaurantMarker = new window.google.maps.Marker({
        position: restaurantLatLng,
        map,
        title: payload.restaurantName || 'Restoran',
        label: { text: '🏪', color: 'white', fontSize: '14px' },
    });

    const directionsService = new window.google.maps.DirectionsService();
    const directionsRenderer = new window.google.maps.DirectionsRenderer({
        map,
        suppressMarkers: true,
        polylineOptions: { strokeColor: '#00a63e', strokeWeight: 5 },
    });

    if (payload.mode === 'pickup') {
        if (hint) {
            hint.textContent = approximateRestaurant
                ? 'Lokasi restoran perkiraan (pusat Jakarta). Izinkan lokasi untuk rute dari posisi Anda, atau lengkapi koordinat di profil mitra.'
                : 'Rute ke restoran (dari lokasi Anda jika diizinkan).';
        }

        if (payload.trackingPollUrl) {
            startMapTrackingPoll(payload, {
                restaurantMarker,
                restaurantLatLng,
                hint,
                driverMarker: null,
                mode: 'pickup',
            });
        }

        const goUserToRestaurant = (origin) => {
            directionsService.route(
                {
                    origin,
                    destination: restaurantLatLng,
                    travelMode: window.google.maps.TravelMode.DRIVING,
                    region: 'id',
                },
                (result, status) => {
                    if (status === 'OK' && result.routes[0]) {
                        directionsRenderer.setDirections(result);
                        result.routes[0].bounds && map.fitBounds(result.routes[0].bounds);
                    } else {
                        map.setCenter(restaurantLatLng);
                        map.setZoom(15);
                    }
                },
            );
        };

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const u = new window.google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
                    new window.google.maps.Marker({
                        position: u,
                        map,
                        title: 'Anda',
                        label: { text: '📍', fontSize: '14px' },
                    });
                    goUserToRestaurant(u);
                },
                () => {
                    map.setCenter(restaurantLatLng);
                    map.setZoom(15);
                    if (hint) hint.textContent = 'Izinkan lokasi untuk melihat rute dari posisi Anda, atau gunakan penanda restoran.';
                },
                { enableHighAccuracy: true, timeout: 8000 },
            );
        } else {
            map.setCenter(restaurantLatLng);
            map.setZoom(15);
        }
        return;
    }

    /* delivery */
    const destAddr = payload.deliveryAddress;
    if (!destAddr) {
        if (hint) hint.textContent = 'Alamat pengiriman kosong.';
        return;
    }

    if (hint) {
        if (approximateRestaurant) {
            hint.textContent = payload.liveTracking
                ? 'Lokasi restoran perkiraan (pusat Jakarta). Memuat rute — posisi kurir diperbarui otomatis saat mitra mengirim GPS.'
                : 'Lokasi restoran perkiraan (pusat Jakarta). Memuat rute ke alamat Anda.';
        } else {
            hint.textContent = payload.liveTracking
                ? 'Memuat rute — posisi kurir diperbarui otomatis saat mitra mengirim GPS.'
                : 'Rute dari restoran ke alamat Anda.';
        }
    }

    let destLatLng;
    try {
        destLatLng = await geocode(geocoder, destAddr);
    } catch {
        if (hint) hint.textContent = 'Alamat tujuan tidak ditemukan di peta.';
        return;
    }

    directionsService.route(
        {
            origin: restaurantLatLng,
            destination: destLatLng,
            travelMode: window.google.maps.TravelMode.DRIVING,
            region: 'id',
        },
        (result, status) => {
            if (status !== 'OK' || !result.routes[0]) {
                map.setCenter(restaurantLatLng);
                map.setZoom(13);
                new window.google.maps.Marker({
                    position: destLatLng,
                    map,
                    title: 'Tujuan',
                    label: { text: '🏠', fontSize: '14px' },
                });
                const fallbackBounds = new window.google.maps.LatLngBounds();
                fallbackBounds.extend(restaurantLatLng);
                fallbackBounds.extend(destLatLng);
                map.fitBounds(fallbackBounds);
                return;
            }

            directionsRenderer.setDirections(result);
            const route = result.routes[0];
            map.fitBounds(route.bounds);

            const path = flattenRoutePath(route);
            if (path.length < 2) {
                return;
            }

            new window.google.maps.Marker({
                position: destLatLng,
                map,
                title: 'Tujuan pengiriman',
                label: { text: '🏠', fontSize: '14px' },
            });

            let startPos = path[0];
            if (
                payload.courierLat != null &&
                payload.courierLng != null &&
                !Number.isNaN(Number(payload.courierLat)) &&
                !Number.isNaN(Number(payload.courierLng))
            ) {
                startPos = new window.google.maps.LatLng(payload.courierLat, payload.courierLng);
            }

            const driverMarker = new window.google.maps.Marker({
                map,
                position: startPos,
                title: payload.courierLat != null ? 'Kurir' : 'Kurir (perkiraan)',
                icon: {
                    path: window.google.maps.SymbolPath.CIRCLE,
                    scale: 9,
                    fillColor: '#ff6900',
                    fillOpacity: 1,
                    strokeColor: '#fff',
                    strokeWeight: 2,
                },
            });

            const ctx = {
                restaurantMarker,
                restaurantLatLng,
                driverMarker,
                hint,
                animTimer: null,
                mode: 'delivery',
            };

            let cleanupPoll = () => {};

            if (payload.liveTracking && payload.trackingPollUrl) {
                cleanupPoll = startMapTrackingPoll(payload, ctx);
            }

            if (!payload.liveTracking) {
                const fs = payload.fulfillmentStatus;
                const endT = fs === 'completed' ? 1 : 0.85;
                driverMarker.setPosition(pointAlongPath(path, endT));
                return;
            }

            const hasLiveCourier =
                payload.courierLat != null &&
                payload.courierLng != null &&
                !Number.isNaN(Number(payload.courierLat)) &&
                !Number.isNaN(Number(payload.courierLng));

            if (!hasLiveCourier) {
                if (hint) {
                    hint.textContent =
                        'Perkiraan posisi kurir di rute — mitra dapat memperbarui GPS di halaman “Lacak delivery”.';
                }
                let animT = 0;
                const speed = 0.0008;
                ctx.animTimer = setInterval(() => {
                    animT += speed;
                    if (animT > 1) animT = 0;
                    driverMarker.setPosition(pointAlongPath(path, animT));
                }, 80);
            } else if (hint) {
                hint.textContent = 'Lokasi kurir (realtime dari mitra).';
            }

            window.addEventListener('beforeunload', () => {
                cleanupPoll();
            });
        },
    );
}
