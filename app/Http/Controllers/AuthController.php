<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->role === 'admin') {
            return back()
                ->withErrors([
                    'email' => 'Email ini terdaftar sebagai admin. Buka halaman Login Admin untuk masuk.',
                ])
                ->withInput();
        }

        if ($user && in_array($user->role, ['seller', 'mitra'], true)) {
            return back()
                ->withErrors([
                    'email' => 'Email ini terdaftar sebagai mitra. Buka halaman Login Mitra untuk masuk.',
                ])
                ->withInput();
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('status', 'Berhasil masuk. Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()
            ->withErrors(['email' => 'Email atau password tidak valid. Belum punya akun? Daftar dulu.'])
            ->withInput();
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
            'terms.accepted' => 'Kamu perlu menyetujui syarat & ketentuan.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer', // default role
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Akun berhasil dibuat. Silakan login dengan email dan password kamu.');
    }

    public function showMitraRegister(): View
    {
        return view('auth.mitra-register');
    }

    public function registerMitra(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
            'terms.accepted' => 'Kamu perlu menyetujui syarat & ketentuan.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'mitra',
        ]);

        return redirect()
            ->route('login.seller')
            ->with('status', 'Akun mitra berhasil dibuat. Silakan login dengan email dan password Anda.');
    }

    public function showAdminLogin(): View
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || $user->role !== 'admin') {
            return back()
                ->withErrors(['email' => 'Email admin tidak ditemukan atau bukan akun admin.'])
                ->withInput();
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()
                ->route('admin.dashboard')
                ->with('status', 'Selamat datang di panel admin, ' . Auth::user()->name . '.');
        }

        return back()
            ->withErrors(['email' => 'Password admin tidak valid.'])
            ->withInput();
    }

    public function showSellerLogin(): View
    {
        return view('auth.seller-login'); // you can rename this view if needed
    }

    public function sellerLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || $user->role !== 'mitra') {
            return back()
                ->withErrors(['email' => 'Email mitra tidak ditemukan atau bukan akun mitra.'])
                ->withInput();
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()
                ->route('mitra.dashboard')
                ->with('status', 'Selamat datang di Portal Mitra, ' . Auth::user()->name . '.');
        }

        return back()
            ->withErrors(['password' => 'Password mitra tidak valid.'])
            ->withInput();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Berhasil logout.');
    }
}
