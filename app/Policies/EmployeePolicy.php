<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        // Daftar pegawai hanya untuk Admin/Super Admin; role user fokus ke profil sendiri.
        return $user->role !== 'user';
    }

    public function view(User $user, Employee $employee): bool
    {
        // Admin/Super Admin boleh lihat semua pegawai; role user hanya profilnya sendiri.
        if ($user->role !== 'user') {
            return true;
        }

        return $employee->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role !== 'user';
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->role !== 'user';
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->role === 'super_admin';
    }
}