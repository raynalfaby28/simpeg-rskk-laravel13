<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkUnit;
use App\Models\EmployeeCategory;
use App\Models\EmploymentStatus;
use App\Models\EducationLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Super Admin pertama (login: NIP 000000000000000001 / password: superadmin123)
        User::create([
            'nip' => '000000000000000001',
            'name' => 'Super Admin RSKK',
            'email' => 'superadmin@rskk.go.id',
            'password' => Hash::make('superadmin123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);


        
        // Master data dasar biar sistem langsung bisa dipakai
        WorkUnit::insert([
            ['name' => 'Subbagian Tata Usaha', 'code' => 'TU', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bidang Pelayanan Medis', 'code' => 'YANMED', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bidang Keperawatan', 'code' => 'KEP', 'created_at' => now(), 'updated_at' => now()],
        ]);

        EmployeeCategory::insert([
            ['name' => 'PNS', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PPPK', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kontrak', 'created_at' => now(), 'updated_at' => now()],
        ]);

        EmploymentStatus::insert([
            ['name' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cuti', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nonaktif', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pensiun', 'created_at' => now(), 'updated_at' => now()],
        ]);

        EducationLevel::insert([
            ['name' => 'SMA/SMK', 'urutan' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'D3', 'urutan' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S1', 'urutan' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S2', 'urutan' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
