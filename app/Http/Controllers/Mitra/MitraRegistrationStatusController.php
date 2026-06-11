<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MitraRegistrationStatusController extends Controller
{
    /** Halaman status pendaftaran mitra (menunggu admin / ditolak). */
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'mitra') {
            return redirect()->route('home');
        }

        if ($user->mitraApprovedForPortal()) {
            return redirect()->route('mitra.dashboard')
                ->with('status', 'Akun Anda sudah aktif.');
        }

        $status = (string) ($user->mitra_approval_status ?? 'pending');

        return view('mitra.registration-status', [
            'approvalStatus' => $status === 'rejected' ? 'rejected' : 'pending',
        ]);
    }
}
