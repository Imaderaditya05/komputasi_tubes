<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $restaurant = null;
        if ($user && $user->role === 'mitra') {
            $restaurant = Restaurant::where('user_id', $user->id)->orderBy('id')->first();
        }

        return view('profile.show', [
            'user' => $user,
            'restaurant' => $restaurant,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[\d\s\+\-\(\)]*$/'],
            'address' => ['required', 'string', 'max:2000'],
            'avatar' => ['nullable', 'file', 'max:5120', 'mimes:jpeg,jpg,png,webp,gif'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];

        $baseMessages = [
            'name.required' => 'Nama akun wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, -, atau ().',
            'address.required' => 'Alamat akun wajib diisi.',
        ];

        if ($user->role === 'mitra') {
            $restaurant = Restaurant::where('user_id', $user->id)->orderBy('id')->first();

            $rules = $baseRules;
            $messages = $baseMessages;
            if ($restaurant) {
                $rules['restaurant_name'] = ['required', 'string', 'max:255'];
                $rules['description'] = ['nullable', 'string', 'max:2000'];
                $rules['address_line'] = ['required', 'string', 'max:2000'];
                $rules['latitude'] = ['nullable', 'numeric', 'between:-90,90'];
                $rules['longitude'] = ['nullable', 'numeric', 'between:-180,180'];

                $messages['restaurant_name.required'] = 'Nama restoran wajib diisi.';
                $messages['address_line.required'] = 'Alamat toko wajib diisi.';
                $messages['latitude.numeric'] = 'Latitude harus berupa angka.';
                $messages['latitude.between'] = 'Latitude harus antara -90 dan 90.';
                $messages['longitude.numeric'] = 'Longitude harus berupa angka.';
                $messages['longitude.between'] = 'Longitude harus antara -180 dan 180.';
            }

            $validated = $request->validate($rules, $messages);

            $user->update([
                'name' => $validated['name'],
                'phone' => $this->normalizePhone($validated['phone'] ?? null),
                'address' => $this->normalizeAddress($validated['address'] ?? null),
            ]);

            $this->syncAvatar($request, $user);

            if ($restaurant) {
                $restaurant->update([
                    'name' => $validated['restaurant_name'],
                    'description' => $validated['description'] ?? null,
                    'address_line' => $validated['address_line'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                ]);
            }

            return redirect()->route('profile.show')->with('status', 'Profil berhasil diperbarui.');
        }

        $validated = $request->validate($baseRules, $baseMessages);

        $user->update([
            'name' => $validated['name'],
            'phone' => $this->normalizePhone($validated['phone'] ?? null),
            'address' => $this->normalizeAddress($validated['address'] ?? null),
        ]);

        $this->syncAvatar($request, $user);

        return redirect()->route('profile.show')->with('status', 'Profil berhasil diperbarui.');
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        return trim($phone);
    }

    private function normalizeAddress(?string $address): ?string
    {
        if ($address === null || trim($address) === '') {
            return null;
        }

        return trim($address);
    }

    private function syncAvatar(Request $request, \App\Models\User $user): void
    {
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = null;
            $user->save();

            return;
        }

        $file = $request->file('avatar');
        if (! $file || ! $file->isValid()) {
            return;
        }

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $file->store('avatars/'.$user->id, 'public');
        $user->avatar_path = $path;
        $user->save();
    }
}
