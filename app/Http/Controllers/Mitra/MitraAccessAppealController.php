<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\MitraAccessAppeal;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MitraAccessAppealController extends Controller
{
    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $restaurant->user_id === $user->id, 403);
        abort_unless(($restaurant->access_status ?? 'active') === 'locked', 403, 'Restoran tidak sedang terkunci.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ], [
            'message.required' => 'Jelaskan alasan banding Anda.',
            'message.min' => 'Alasan minimal 20 karakter agar admin dapat meninjau.',
        ]);

        $pending = MitraAccessAppeal::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->exists();

        if ($pending) {
            return back()->withErrors([
                'message' => 'Pengajuan banding sebelumnya masih menunggu tinjauan admin.',
            ])->withInput();
        }

        MitraAccessAppeal::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        return back()->with('status', 'Pengajuan banding telah dikirim. Tim akan meninjaunya.');
    }
}
