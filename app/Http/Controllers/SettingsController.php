<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    private array $keys = [
        'app_name' => 'Nama Aplikasi',
        'app_tagline' => 'Tagline Aplikasi',
        'rs_name' => 'Nama Rumah Sakit',
        'rs_address' => 'Alamat Rumah Sakit',
        'rs_phone' => 'Telepon Rumah Sakit',
        'rs_email' => 'Email Rumah Sakit',
    ];

    public function edit()
    {
        $current = Settings::get(null);
        $loginPhotoUrl = $this->loginPhotoUrl($current['login_photo_path'] ?? null);
        return view('settings.edit', [
            'settings' => $current,
            'keys' => $this->keys,
            'loginPhotoUrl' => $loginPhotoUrl,
        ]);
    }

    public function update(Request $request)
    {
        foreach ($this->keys as $key => $label) {
            Settings::set($key, $request->input($key, ''));
        }

        if ($request->hasFile('login_photo')) {
            $request->validate([
                'login_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            ]);

            $oldPath = Settings::get('login_photo_path');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('login_photo')->store('settings/login', 'public');
            Settings::set('login_photo_path', $path);
        }

        AuditLog::record('update', 'Pengaturan', auth()->user(), 'Mengubah pengaturan sistem');
        return redirect()->route('settings.edit')->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    public function deletePhoto()
    {
        $oldPath = Settings::get('login_photo_path');
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
            Settings::where('key', 'login_photo_path')->delete();
            AuditLog::record('update', 'Pengaturan', auth()->user(), 'Mengembalikan foto halaman login ke bawaan');
        }

        return redirect()->route('settings.edit')->with('success', 'Foto halaman login dikembalikan ke bawaan.');
    }

    private function loginPhotoUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? Storage::url($path) : null;
    }
}