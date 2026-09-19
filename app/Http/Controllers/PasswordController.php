<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('password.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
            'password_cipher' => Crypt::encryptString($request->password),
        ]);

        AuditLog::record('update', 'Pengaturan', auth()->user(), 'Mengubah password akun sendiri');
        return redirect()->route('password.edit')->with('success', 'Password berhasil diubah.');
    }
}