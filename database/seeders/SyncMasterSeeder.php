<?php

namespace Database\Seeders;

use App\Models\AwardType;
use App\Models\DiklatType;
use App\Models\DocumentType;
use App\Models\EducationType;
use App\Models\EmployeeType;
use App\Models\PositionType;
use Illuminate\Database\Seeder;

/**
 * Mengisi master data yang selama ini kosong (count 0) dengan nilai yang
 * sebenarnya dipakai di form/profil pegawai agar sumber datanya sinkron.
 */
class SyncMasterSeeder extends Seeder
{
    public function run(): void
    {
        $positionTypes = [
            ['code' => 'struktural', 'name' => 'Struktural'],
            ['code' => 'fungsional', 'name' => 'Fungsional'],
            ['code' => 'pelaksana', 'name' => 'Pelaksana'],
        ];
        foreach ($positionTypes as $row) {
            PositionType::updateOrCreate(['code' => $row['code']], $row + ['is_active' => true]);
        }

        $educationTypes = [
            ['code' => 'formal', 'name' => 'Pendidikan Formal'],
            ['code' => 'non_formal', 'name' => 'Pendidikan Non Formal'],
        ];
        foreach ($educationTypes as $row) {
            EducationType::updateOrCreate(['code' => $row['code']], $row + ['is_active' => true]);
        }

        $diklatTypes = [
            ['code' => 'struktural', 'name' => 'Diklat Struktural'],
            ['code' => 'fungsional', 'name' => 'Diklat Fungsional'],
            ['code' => 'teknis', 'name' => 'Diklat Teknis'],
            ['code' => 'keahlian_profesi', 'name' => 'Sertifikat Keahlian / Profesi'],
            ['code' => 'bintek_seminar', 'name' => 'Bimbingan Teknis / Seminar'],
        ];
        foreach ($diklatTypes as $row) {
            DiklatType::updateOrCreate(['code' => $row['code']], $row + ['is_active' => true]);
        }

        $documentTypes = [
            'KTP', 'KK', 'NPWP', 'BPJS', 'KARPEG', 'KARIS/KARSU', 'KPE',
            'Ijazah', 'Transkrip',
            'SK Pengangkatan', 'SK PNS/PPPK', 'SK Pangkat', 'SK Jabatan', 'SK Mutasi', 'SK Gaji',
            'Surat Tugas', 'Diklat', 'Sertifikat', 'STR', 'Penghargaan',
            'Keterangan', 'Dokumen Pendukung',
            'Ijazah Ners', 'Sertifikat BTCLS',
        ];
        foreach ($documentTypes as $name) {
            DocumentType::updateOrCreate(['code' => $name], ['name' => $name, 'is_active' => true]);
        }

        $awardTypes = [
            'Piagam', 'Satya Lencana', 'Sertifikat', 'Medali', 'Lainnya',
        ];
        foreach ($awardTypes as $name) {
            AwardType::updateOrCreate(['code' => $name], ['name' => $name, 'is_active' => true]);
        }

        $employeeTypes = [
            'PNS', 'PPPK', 'Honorer', 'Kontrak', 'BLUD', 'Lainnya',
        ];
        foreach ($employeeTypes as $name) {
            EmployeeType::updateOrCreate(['code' => $name], ['name' => $name, 'is_active' => true]);
        }
    }
}