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
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[\d\s\+\-\(\)]*$/'],
            'address' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'file', 'max:5120', 'mimes:jpeg,jpg,png,webp,gif'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];

        if ($user->role === 'mitra') {
            $restaurant = Restaurant::where('user_id', $user->id)->orderBy('id')->first();

            $rules = $baseRules;
            if ($restaurant) {
                $rules['restaurant_name'] = ['required', 'string', 'max:255'];
                $rules['description'] = ['nullable', 'string', 'max:2000'];
                $rules['address_line'] = ['nullable', 'string', 'max:2000'];
                $rules['latitude'] = ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'];
                $rules['longitude'] = ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'];
            }

            $validated = $request->validate($rules);

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

        $validated = $request->validate($baseRules);

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
