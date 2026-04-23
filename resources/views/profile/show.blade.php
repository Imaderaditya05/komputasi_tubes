<x-layouts.app :title="'Profil Akun • SurpriseBite'" variant="marketing">
    <div class="mx-auto max-w-lg pb-16 pt-6 sm:pt-10">
        <h1 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Profil akun</h1>
        <p class="mt-2 text-sm text-[#6a7282]">Informasi login dan akses cepat ke fitur SurpriseBite.</p>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-8">
            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div class="flex flex-col items-center gap-4 border-b border-slate-100 pb-6 sm:flex-row sm:items-start">
                    <div class="relative shrink-0">
                        <img
                            id="profile-avatar-preview"
                            src=""
                            alt=""
                            class="hidden h-24 w-24 cursor-pointer rounded-full object-cover ring-4 ring-emerald-100"
                            title="Klik untuk pratinjau besar"
                            role="button"
                            tabindex="0"
                        />
                        <div id="profile-avatar-current">
                            @if ($user->avatar_url)
                                <img
                                    id="profile-avatar-current-photo"
                                    src="{{ $user->avatar_url }}"
                                    alt=""
                                    class="h-24 w-24 cursor-pointer rounded-full object-cover ring-4 ring-emerald-100"
                                    title="Klik untuk pratinjau besar"
                                    role="button"
                                    tabindex="0"
                                />
                            @else
                                <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-[#00a63e] to-[#00bc7d] text-2xl font-black text-white ring-4 ring-emerald-100">
                                    {{ strtoupper(mb_substr(trim($user->name) !== '' ? $user->name : '?', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="w-full min-w-0 text-center sm:text-left">
                        <p class="text-sm font-bold text-[#364153]">Foto profil</p>
                        <p class="mt-1 text-xs text-[#6a7282]">JPG, PNG, atau WebP — maks. 5 MB. Dipakai di header akun setelah disimpan. Pratinjau muncul sebelum menyimpan.</p>
                        <input
                            id="profile-avatar-input"
                            type="file"
                            name="avatar"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            class="hidden"
                        />
                        <button
                            type="button"
                            id="profile-avatar-open-menu"
                            class="mt-3 inline-flex cursor-pointer items-center justify-center rounded-xl bg-[#f3f4f6] px-4 py-2.5 text-sm font-bold text-[#1e2939] ring-1 ring-slate-200 hover:bg-slate-100"
                        >
                            Pilih foto
                        </button>
                        <p id="profile-avatar-filename" class="mt-1 min-h-[1.25rem] text-xs font-medium text-[#00a63e]"></p>
                        @if ($user->avatar_url)
                            <label class="mt-2 flex cursor-pointer items-center justify-center gap-2 text-sm font-semibold text-red-600 sm:justify-start">
                                <input type="checkbox" id="profile-remove-avatar" name="remove_avatar" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500" />
                                Hapus foto profil
                            </label>
                        @endif
                        @error('avatar')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <dialog id="profile-avatar-source-dialog" class="w-[min(100vw-2rem,22rem)] rounded-2xl border border-slate-200 bg-white p-0 shadow-xl ring-1 ring-black/5 backdrop:bg-black/40">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="text-sm font-black text-[#1e2939]">Sumber foto</p>
                        <p class="mt-0.5 text-xs text-[#6a7282]">Kamera atau file di perangkat</p>
                    </div>
                    <div class="flex flex-col gap-1 p-2">
                        <button type="button" id="profile-avatar-pick-camera" class="rounded-xl px-3 py-3 text-left text-sm font-bold text-[#1e2939] hover:bg-emerald-50">
                            Ambil foto (kamera)
                        </button>
                        <button type="button" id="profile-avatar-pick-gallery" class="rounded-xl px-3 py-3 text-left text-sm font-bold text-[#1e2939] hover:bg-slate-50">
                            Pilih dari galeri / file
                        </button>
                        <button type="button" id="profile-avatar-source-cancel" class="rounded-xl px-3 py-2.5 text-center text-sm font-semibold text-[#6a7282] hover:bg-slate-50">
                            Batal
                        </button>
                    </div>
                </dialog>

                <dialog id="profile-avatar-camera-dialog" class="w-[min(100vw-2rem,28rem)] rounded-2xl border border-slate-200 bg-white p-0 shadow-xl ring-1 ring-black/5 backdrop:bg-black/40">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="text-sm font-black text-[#1e2939]">Kamera</p>
                        <p id="profile-camera-error" class="mt-1 hidden text-xs font-medium text-red-600"></p>
                    </div>
                    <div class="p-3">
                        <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-black">
                            <video id="profile-camera-video" class="h-full w-full object-cover" playsinline muted autoplay></video>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" id="profile-camera-capture" class="inline-flex flex-1 items-center justify-center rounded-xl bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-4 py-2.5 text-sm font-bold text-white shadow-md">
                                Ambil foto
                            </button>
                            <button type="button" id="profile-camera-close" class="inline-flex items-center justify-center rounded-xl bg-[#f3f4f6] px-4 py-2.5 text-sm font-bold text-[#364153] ring-1 ring-slate-200">
                                Tutup
                            </button>
                        </div>
                    </div>
                </dialog>

                <canvas id="profile-camera-canvas" class="hidden" width="800" height="800"></canvas>

                <dialog id="profile-avatar-lightbox" class="max-h-none w-[min(100vw-1rem,42rem)] border-0 bg-transparent p-0 shadow-none backdrop:bg-black/70">
                    <div class="relative flex flex-col items-center gap-3 rounded-2xl bg-white p-3 shadow-2xl ring-1 ring-white/10 sm:p-4">
                        <button
                            type="button"
                            id="profile-lightbox-close"
                            class="absolute right-2 top-2 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-black/50 text-xl font-bold text-white backdrop-blur-sm hover:bg-black/70"
                            aria-label="Tutup"
                        >
                            ×
                        </button>
                        <img
                            id="profile-lightbox-img"
                            src=""
                            alt="Foto profil"
                            class="max-h-[min(85vh,720px)] w-auto max-w-full rounded-xl object-contain"
                        />
                        <p class="text-center text-xs font-medium text-slate-500">Klik di luar foto atau tombol × untuk menutup</p>
                    </div>
                </dialog>

                <script>
                    (function () {
                        var input = document.getElementById('profile-avatar-input');
                        var openBtn = document.getElementById('profile-avatar-open-menu');
                        var sourceDlg = document.getElementById('profile-avatar-source-dialog');
                        var cameraDlg = document.getElementById('profile-avatar-camera-dialog');
                        var pickCamera = document.getElementById('profile-avatar-pick-camera');
                        var pickGallery = document.getElementById('profile-avatar-pick-gallery');
                        var sourceCancel = document.getElementById('profile-avatar-source-cancel');
                        var video = document.getElementById('profile-camera-video');
                        var captureBtn = document.getElementById('profile-camera-capture');
                        var closeCam = document.getElementById('profile-camera-close');
                        var canvas = document.getElementById('profile-camera-canvas');
                        var camError = document.getElementById('profile-camera-error');
                        var preview = document.getElementById('profile-avatar-preview');
                        var currentWrap = document.getElementById('profile-avatar-current');
                        var filenameEl = document.getElementById('profile-avatar-filename');
                        var removeCb = document.getElementById('profile-remove-avatar');
                        var lightbox = document.getElementById('profile-avatar-lightbox');
                        var lightboxImg = document.getElementById('profile-lightbox-img');
                        var lightboxClose = document.getElementById('profile-lightbox-close');
                        var currentPhoto = document.getElementById('profile-avatar-current-photo');
                        var stream = null;
                        var previewUrl = null;

                        function openAvatarLightbox(src) {
                            if (!src || !lightbox || !lightboxImg) return;
                            lightboxImg.src = src;
                            lightbox.showModal();
                        }

                        function closeAvatarLightbox() {
                            lightbox?.close();
                        }

                        preview?.addEventListener('click', function () {
                            if (preview.src) openAvatarLightbox(preview.src);
                        });
                        preview?.addEventListener('keydown', function (e) {
                            if ((e.key === 'Enter' || e.key === ' ') && preview.src) {
                                e.preventDefault();
                                openAvatarLightbox(preview.src);
                            }
                        });

                        currentPhoto?.addEventListener('click', function () {
                            if (currentPhoto.src) openAvatarLightbox(currentPhoto.src);
                        });
                        currentPhoto?.addEventListener('keydown', function (e) {
                            if ((e.key === 'Enter' || e.key === ' ') && currentPhoto.src) {
                                e.preventDefault();
                                openAvatarLightbox(currentPhoto.src);
                            }
                        });

                        lightboxClose?.addEventListener('click', closeAvatarLightbox);

                        lightbox?.addEventListener('click', function (e) {
                            if (e.target === lightbox) closeAvatarLightbox();
                        });

                        lightbox?.addEventListener('close', function () {
                            if (lightboxImg) lightboxImg.src = '';
                        });

                        function showCamError(msg) {
                            if (!camError) return;
                            camError.textContent = msg;
                            camError.classList.remove('hidden');
                        }
                        function hideCamError() {
                            if (!camError) return;
                            camError.classList.add('hidden');
                            camError.textContent = '';
                        }

                        function stopCamera() {
                            if (stream) {
                                stream.getTracks().forEach(function (t) { t.stop(); });
                                stream = null;
                            }
                            if (video) video.srcObject = null;
                        }

                        function revokePreview() {
                            if (previewUrl) {
                                URL.revokeObjectURL(previewUrl);
                                previewUrl = null;
                            }
                        }

                        function setFileOnInput(file) {
                            if (!input || !file) return;
                            var dt = new DataTransfer();
                            dt.items.add(file);
                            input.files = dt.files;
                        }

                        function showPreview(file) {
                            if (!preview || !currentWrap || !file) return;
                            revokePreview();
                            previewUrl = URL.createObjectURL(file);
                            preview.src = previewUrl;
                            preview.classList.remove('hidden');
                            currentWrap.classList.add('hidden');
                            if (filenameEl) filenameEl.textContent = 'Pratinjau — ' + (file.name || 'foto-kamera.jpg');
                            if (removeCb) removeCb.checked = false;
                        }

                        function clearPreview() {
                            revokePreview();
                            if (preview) {
                                preview.src = '';
                                preview.classList.add('hidden');
                            }
                            if (currentWrap) currentWrap.classList.remove('hidden');
                            if (input) input.value = '';
                            if (filenameEl) filenameEl.textContent = '';
                        }

                        function openGalleryPicker() {
                            if (!input) return;
                            input.removeAttribute('capture');
                            input.click();
                        }

                        function openNativeCameraPicker() {
                            if (!input) return;
                            input.setAttribute('capture', 'user');
                            input.click();
                            setTimeout(function () { input.removeAttribute('capture'); }, 500);
                        }

                        async function openWebcamDialog() {
                            hideCamError();
                            if (!cameraDlg || !video) {
                                openNativeCameraPicker();
                                return;
                            }
                            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                openNativeCameraPicker();
                                return;
                            }
                            try {
                                stopCamera();
                                stream = await navigator.mediaDevices.getUserMedia({
                                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 1280 } },
                                    audio: false
                                });
                                video.srcObject = stream;
                                await video.play();
                                cameraDlg.showModal();
                            } catch (e) {
                                openNativeCameraPicker();
                            }
                        }

                        pickCamera?.addEventListener('click', function () {
                            sourceDlg?.close();
                            openWebcamDialog();
                        });

                        pickGallery?.addEventListener('click', function () {
                            sourceDlg?.close();
                            openGalleryPicker();
                        });

                        sourceCancel?.addEventListener('click', function () {
                            sourceDlg?.close();
                        });

                        openBtn?.addEventListener('click', function () {
                            sourceDlg?.showModal();
                        });

                        closeCam?.addEventListener('click', function () {
                            stopCamera();
                            cameraDlg?.close();
                        });

                        cameraDlg?.addEventListener('close', function () {
                            stopCamera();
                        });

                        captureBtn?.addEventListener('click', function () {
                            if (!video || !canvas || !input) return;
                            var vw = video.videoWidth;
                            var vh = video.videoHeight;
                            if (!vw || !vh) {
                                alert('Kamera belum siap. Tunggu sebentar lalu ketuk Ambil foto lagi.');
                                return;
                            }
                            var size = Math.min(vw, vh);
                            var sx = (vw - size) / 2;
                            var sy = (vh - size) / 2;
                            canvas.width = size;
                            canvas.height = size;
                            var ctx = canvas.getContext('2d');
                            ctx.drawImage(video, sx, sy, size, size, 0, 0, size, size);
                            canvas.toBlob(function (blob) {
                                if (!blob) return;
                                if (blob.size > 5 * 1024 * 1024) {
                                    alert('Foto melebihi 5 MB. Coba pilih dari galeri atau ambil ulang.');
                                    return;
                                }
                                var file = new File([blob], 'foto-profil.jpg', { type: 'image/jpeg' });
                                setFileOnInput(file);
                                showPreview(file);
                                stopCamera();
                                cameraDlg?.close();
                            }, 'image/jpeg', 0.85);
                        });

                        input?.addEventListener('change', function () {
                            var f = this.files && this.files[0];
                            if (f) {
                                if (f.size > 5 * 1024 * 1024) {
                                    alert('Ukuran file maksimal 5 MB.');
                                    input.value = '';
                                    return;
                                }
                                showPreview(f);
                            }
                        });

                        removeCb?.addEventListener('change', function () {
                            if (this.checked) {
                                clearPreview();
                            }
                        });
                    })();
                </script>

                <div>
                    <label for="name" class="block text-sm font-bold text-[#364153]">Nama akun</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        maxlength="255"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] shadow-sm ring-1 ring-black/5 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                    />
                    @error('name')
                        <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-bold text-[#364153]">Nomor telepon</label>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        value="{{ old('phone', $user->phone) }}"
                        maxlength="32"
                        placeholder="Contoh: 081234567890"
                        autocomplete="tel"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] shadow-sm ring-1 ring-black/5 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                    />
                    @error('phone')
                        <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-bold text-[#364153]">Alamat</label>
                    <textarea
                        name="address"
                        id="address"
                        rows="3"
                        maxlength="2000"
                        placeholder="Alamat lengkap (pengiriman / kontak)"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] shadow-sm ring-1 ring-black/5 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                    >{{ old('address', $user->address) }}</textarea>
                    @if($user->role === 'mitra')
                        <p class="mt-1 text-xs text-[#6a7282]">Ini alamat akun Anda. Alamat toko diatur di bagian restoran di bawah.</p>
                    @endif
                    @error('address')
                        <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm font-bold text-[#364153]">Email</span>
                    <p class="mt-1.5 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-[#1e2939]">{{ $user->email }}</p>
                    <p class="mt-1 text-xs text-[#6a7282]">Email tidak dapat diubah dari halaman ini.</p>
                </div>

                <div>
                    <span class="block text-sm font-bold text-[#364153]">Peran</span>
                    <p class="mt-1.5 capitalize text-[#1e2939]">
                        @if($user->role === 'customer')
                            Pelanggan
                        @elseif($user->role === 'mitra')
                            Mitra / Warung
                        @elseif($user->role === 'admin')
                            Admin
                        @else
                            {{ $user->role }}
                        @endif
                    </p>
                </div>

                @if($user->role === 'mitra')
                    @if($restaurant)
                        <div class="border-t border-slate-100 pt-5">
                            <h2 class="text-sm font-black uppercase tracking-wide text-[#364153]">Toko / restoran</h2>
                            <p class="mt-1 text-xs text-[#6a7282]">Nama dan lokasi dipakai di katalog serta peta pelacakan pesanan.</p>
                        </div>

                        <div>
                            <label for="restaurant_name" class="block text-sm font-bold text-[#364153]">Nama restoran</label>
                            <input
                                type="text"
                                name="restaurant_name"
                                id="restaurant_name"
                                value="{{ old('restaurant_name', $restaurant->name) }}"
                                required
                                maxlength="255"
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] shadow-sm ring-1 ring-black/5 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                            />
                            @error('restaurant_name')
                                <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-bold text-[#364153]">Deskripsi (opsional)</label>
                            <textarea
                                name="description"
                                id="description"
                                rows="3"
                                maxlength="2000"
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] shadow-sm ring-1 ring-black/5 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                            >{{ old('description', $restaurant->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address_line" class="block text-sm font-bold text-[#364153]">Alamat toko</label>
                            <textarea
                                name="address_line"
                                id="address_line"
                                rows="2"
                                maxlength="2000"
                                placeholder="Contoh: Jl. Merdeka No. 10, Jakarta"
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] shadow-sm ring-1 ring-black/5 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                            >{{ old('address_line', $restaurant->address_line) }}</textarea>
                            @error('address_line')
                                <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="latitude" class="block text-sm font-bold text-[#364153]">Latitude</label>
                                <input
                                    type="text"
                                    name="latitude"
                                    id="latitude"
                                    inputmode="decimal"
                                    placeholder="-6.2"
                                    value="{{ old('latitude', $restaurant->latitude) }}"
                                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] shadow-sm ring-1 ring-black/5 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                                />
                                @error('latitude')
                                    <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="longitude" class="block text-sm font-bold text-[#364153]">Longitude</label>
                                <input
                                    type="text"
                                    name="longitude"
                                    id="longitude"
                                    inputmode="decimal"
                                    placeholder="106.8"
                                    value="{{ old('longitude', $restaurant->longitude) }}"
                                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1e2939] shadow-sm ring-1 ring-black/5 focus:border-[#00a63e] focus:outline-none focus:ring-2 focus:ring-[#00a63e]/25"
                                />
                                @error('longitude')
                                    <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="text-xs text-[#6a7282]">Isi keduanya agar penanda di peta pelacakan akurat. Bisa disalin dari Google Maps (klik kanan → koordinat).</p>
                    @else
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                            <p class="font-bold">Belum ada restoran terdaftar</p>
                            <p class="mt-1">Buat restoran dari dashboard mitra untuk mengatur menu dan lokasi toko.</p>
                        </div>
                    @endif
                @endif

                <div class="pt-2">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-4 py-3 text-sm font-bold text-white shadow-md hover:opacity-95">
                        Simpan perubahan
                    </button>
                </div>
            </form>

            <div class="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-6">
                @if($user->role === 'customer')
                    <a href="{{ route('wishlist.index') }}" class="inline-flex items-center justify-center rounded-xl bg-[#f3f4f6] px-4 py-3 text-sm font-bold text-[#1e2939] ring-1 ring-slate-200 hover:bg-slate-100">
                        Wishlist
                    </a>
                    <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center rounded-xl bg-[#f3f4f6] px-4 py-3 text-sm font-bold text-[#1e2939] ring-1 ring-slate-200 hover:bg-slate-100">
                        Lihat keranjang
                    </a>
                @endif
                @if($user->role === 'mitra')
                    <a href="{{ route('mitra.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-4 py-3 text-sm font-bold text-white shadow-md hover:opacity-95">
                        Buka dashboard mitra
                    </a>
                @endif
                @if($user->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-[#00a63e] to-[#00bc7d] px-4 py-3 text-sm font-bold text-white shadow-md hover:opacity-95">
                        Panel admin
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
