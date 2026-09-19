<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        $loginPhotoPath = \App\Models\Settings::get('login_photo_path');
        $loginPhotoUrl = $loginPhotoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($loginPhotoPath)
            ? \Illuminate\Support\Facades\Storage::url($loginPhotoPath)
            : null;

        return view('auth.login', ['loginPhotoUrl' => $loginPhotoUrl]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nip' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('nip', $credentials['nip'])->first();

        if (! $user || ! $user->is_active) {
            throw ValidationException::withMessages([
                'nip' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
            ]);
        }

        if (! Auth::attempt(['nip' => $credentials['nip'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'nip' => 'NIP/Username atau password yang Anda masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}