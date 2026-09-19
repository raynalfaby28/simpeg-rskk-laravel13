<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Daftar akun. Super Admin lihat semua, Admin cuma lihat role 'user'.
     */
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->user()->role === 'admin') {
            $query->where('role', 'user');
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $accounts = $query->paginate(15)->withQueryString();

        return view('admin.accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        return view('admin.accounts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();

        // Admin tidak boleh membuat akun Super Admin — ditolak di backend walau
        // request dimanipulasi atau field role tidak ada di form.
        if ($actor->role !== 'super_admin' && $request->input('role') === 'super_admin') {
            AuditLog::record(
                action: 'role.change_attempt_denied',
                module: 'Akun',
                reference: $actor,
                description: 'Percobaan membuat akun role Super Admin ditolak.',
            );
            abort(403, 'Admin tidak memiliki izin untuk menetapkan role Super Admin.');
        }

        $allowedRoles = $actor->role === 'super_admin'
            ? ['super_admin', 'admin', 'user']
            : ['user']; // Admin cuma boleh buat akun User

        $data = $request->validate([
            'nip' => ['required', 'string', 'unique:users,nip'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', $allowedRoles)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'nip' => $data['nip'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'password_cipher' => Crypt::encryptString($data['password']),
            'is_active' => true,
        ]);

        // Sesuai alur: begitu akun pegawai dibuat, langsung siapkan record employees
        // kosong yang nanti dilengkapi sendiri oleh pegawai / Admin.
        if ($user->role === 'user') {
            Employee::create([
                'user_id' => $user->id,
                'nip' => $user->nip,
                'nama_lengkap' => $user->name,
            ]);
        }

        AuditLog::record(
            action: 'create',
            module: 'Akun',
            reference: $user,
            description: "{$this->actorLabel($actor)} membuat akun {$user->name} ({$user->nip}) dengan role {$user->role}.",
        );

        return redirect()
            ->route('admin.accounts.index')
            ->with('success', "Akun {$data['name']} berhasil dibuat.");
    }

    /**
     * Aktif / nonaktifkan akun. Admin tidak boleh menyentuh Super Admin.
     */
    public function toggleActive(Request $request, User $account): RedirectResponse
    {
        $this->guardAgainstTouchingSuperAdmin($request, $account);

        if ($account->id === $request->user()->id) {
            abort(403, 'Tidak bisa menonaktifkan akun sendiri.');
        }

        $account->update(['is_active' => ! $account->is_active]);

        AuditLog::record(
            action: 'update',
            module: 'Akun',
            reference: $account,
            description: "{$this->actorLabel($request->user())} ".($account->is_active ? 'mengaktifkan' : 'menonaktifkan')." akun {$account->name} ({$account->nip}).",
        );

        return back()->with('success', $account->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function resetPassword(Request $request, User $account): RedirectResponse
    {
        $this->guardAgainstTouchingSuperAdmin($request, $account);

        $newPassword = 'password123';
        $account->update([
            'password' => Hash::make($newPassword),
            'password_cipher' => Crypt::encryptString($newPassword),
        ]);

        AuditLog::record(
            action: 'update',
            module: 'Akun',
            reference: $account,
            description: "{$this->actorLabel($request->user())} mereset password akun {$account->name} ({$account->nip}).",
        );

        // Di produksi: kirim password baru lewat WA/email resmi, jangan ditampilkan di layar.
        return back()->with('success', "Password direset menjadi password bawaan: {$newPassword}");
    }

    /**
     * Buka password akun (hanya Super Admin). Dipakai tombol "Lihat" di Manajemen Akun.
     */
    public function revealPassword(Request $request, User $account): JsonResponse
    {
        if ($request->user()->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang boleh melihat password akun.');
        }

        return response()->json([
            'nip' => $account->nip,
            'name' => $account->name,
            'password' => $account->decrypted_password,
        ]);
    }

    public function updateRole(Request $request, User $account): RedirectResponse
    {
        $actor = $request->user();

        if ($actor->role !== 'super_admin') {
            AuditLog::record(
                action: 'role.change_attempt_denied',
                module: 'Akun',
                reference: $account,
                description: 'Percobaan mengubah role oleh akun non-Super Admin ditolak.',
            );
            abort(403, 'Hanya Super Admin yang boleh mengubah role.');
        }

        if ($account->role === 'super_admin') {
            abort(403, 'Role Super Admin tidak boleh diubah dari sini.');
        }

        $data = $request->validate([
            'role' => ['required', 'in:admin,user'], // tidak bisa promote ke super_admin
        ]);

        $account->update(['role' => $data['role']]);

        AuditLog::record(
            action: 'role.changed',
            module: 'Akun',
            reference: $account,
            description: "{$this->actorLabel($actor)} mengubah role akun {$account->name} ({$account->nip}) menjadi {$data['role']}.",
        );

        return back()->with('success', 'Role akun diperbarui.');
    }

    public function destroy(Request $request, User $account): RedirectResponse
    {
        $this->guardAgainstTouchingSuperAdmin($request, $account);

        if ($account->id === $request->user()->id) {
            abort(403, 'Tidak bisa menghapus akun sendiri.');
        }

        $account->delete();

        AuditLog::record(
            action: 'delete',
            module: 'Akun',
            reference: $account,
            description: "{$this->actorLabel($request->user())} menghapus akun {$account->name} ({$account->nip}).",
        );

        return back()->with('success', 'Akun dihapus.');
    }

    private function actorLabel(User $user): string
    {
        return $user->role === 'super_admin' ? 'Super Admin' : 'Admin';
    }

    private function guardAgainstTouchingSuperAdmin(Request $request, User $account): void
    {
        $actor = $request->user();

        if ($actor->role === 'admin' && $account->role !== 'user') {
            abort(403, 'Admin hanya boleh mengelola akun User.');
        }

        if ($actor->role !== 'super_admin' && $account->role === 'super_admin') {
            abort(403, 'Akun Super Admin tidak boleh diubah oleh role ini.');
        }
    }
}
