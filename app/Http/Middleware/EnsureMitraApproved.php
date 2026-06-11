<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMitraApproved
{
    /**
     * Mitra dengan pendaftaran pending/rejected tidak boleh mengakses portal bisnis sampai admin menyetujui.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'mitra') {
            return $next($request);
        }

        if ($user->mitraApprovedForPortal()) {
            return $next($request);
        }

        if (($user->mitra_approval_status ?? '') === 'rejected') {
            return redirect()
                ->route('mitra.registration.status')
                ->with(
                    'status',
                    'Registrasi mitra Anda belum dapat disetujui. Hubungi admin jika perlu.',
                );
        }

        return redirect()->route('mitra.registration.status');
    }
}
