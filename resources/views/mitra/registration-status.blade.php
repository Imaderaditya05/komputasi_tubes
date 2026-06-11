<x-layouts.app :title="'Menunggu persetujuan • Mitra SurpriseBite'" variant="marketing">
    <div class="pb-16 pt-10 sm:pt-14">
        <div class="mx-auto max-w-2xl rounded-3xl border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-white px-6 py-10 shadow-xl ring-1 ring-amber-100 sm:px-10">
            @if (($approvalStatus ?? 'pending') === 'rejected')
                <div class="mb-4 inline-flex rounded-full bg-red-100 px-4 py-1.5 text-xs font-black uppercase tracking-wide text-red-800 ring-1 ring-red-200">
                    Ditolak admin
                </div>
                <h1 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Registrasi mitra tidak disetujui</h1>
                <p class="mt-4 text-base leading-relaxed text-[#4a5565]">
                    Tim admin belum dapat menyetujui pendaftaran Anda untuk saat ini. Jika Anda yakin ada kesalahan,
                    hubungi tim SurpriseBite melalui kanal dukungan yang tersedia.
                </p>
            @else
                <div class="mb-4 inline-flex rounded-full bg-amber-100 px-4 py-1.5 text-xs font-black uppercase tracking-wide text-amber-950 ring-1 ring-amber-200">
                    Status: menunggu persetujuan
                </div>
                <h1 class="text-2xl font-black text-[#1e2939] sm:text-3xl">Akun Anda sedang diverifikasi</h1>
                <p class="mt-4 text-base leading-relaxed text-[#4a5565]">
                    Terima kasih telah mendaftar sebagai mitra SurpriseBite. Persetujuan akun baru dilakukan oleh admin secara manual.
                    Biasanya proses ini memakan <strong class="font-black text-[#1e2939]">1 sampai 3 hari kerja</strong> sejak Anda mendaftar.
                </p>
                <ul class="mt-6 space-y-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-semibold text-[#475569]">
                    <li class="flex gap-2"><span class="text-emerald-600">•</span> Anda sudah bisa login untuk melihat halaman ini.</li>
                    <li class="flex gap-2"><span class="text-emerald-600">•</span> Pengelolaan restoran dan menu akan bisa diakses setelah admin menyetujui.</li>
                    <li class="flex gap-2"><span class="text-emerald-600">•</span> Tidak ada tindakan lebih lanjut dari Anda kecuali menunggu notifikasi tim kami melalui email terdaftar (jika diaktifkan).</li>
                </ul>
            @endif

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ route('login.seller') }}" class="inline-flex items-center justify-center rounded-2xl border-2 border-slate-200 bg-white px-6 py-3 text-sm font-black text-[#364153] shadow-sm hover:bg-slate-50">
                    Kembali ke login mitra
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#00a63e] px-6 py-3 text-sm font-black text-white shadow-lg hover:bg-[#008f36]">
                    Ke beranda
                </a>
                <form method="post" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border-2 border-red-200 bg-red-50 px-6 py-3 text-sm font-black text-red-800 hover:bg-red-100">
                        Keluar dari akun
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
