<?php

namespace App\Http\Controllers;

use App\Models\{
    AuditLog, AssetType, Employee, EmployeeAsset, EmployeeAward, EmployeeCreditScore, EmployeeDiscipline,
    EmployeeDisease, EmployeeEducation, EmployeeEmergencyContact, EmployeeFamily,
    EmployeeInactivePeriod, EmployeeIpasn, EmployeeLanguage, EmployeeLeave,
    EmployeeLegalStatus, EmployeeMutation, EmployeePerformance, EmployeePmkHistory,
    EmployeePppkContract, EmployeePositionHistory, EmployeeRankHistory,
    EmployeeSalaryHistory, EmployeeSkp, EmployeeTraining,
    EducationLevel, Position, Rank, WorkUnit,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubDataController extends Controller
{
    private function config(): array
    {
        return [
            'pendidikan' => [
                'label' => 'Pendidikan', 'model' => EmployeeEducation::class, 'tab' => 'pendidikan-diklat', 'module' => 'Pendidikan',
                'fields' => [
                    'kategori' => ['label' => 'Jenis Pendidikan', 'type' => 'select', 'options' => ['formal' => 'Pendidikan Formal', 'non_formal' => 'Pendidikan Non Formal'], 'required' => true, 'cols' => 1],
                    'education_level_id' => ['label' => 'Jenjang', 'type' => 'select', 'source' => 'education-levels', 'required' => true, 'cols' => 1],
                    'institution' => ['label' => 'Institusi', 'type' => 'text', 'cols' => 2],
                    'faculty' => ['label' => 'Fakultas', 'type' => 'text'],
                    'major' => ['label' => 'Program Studi', 'type' => 'text'],
                    'no_ijazah' => ['label' => 'Nomor Ijazah', 'type' => 'text'],
                    'tahun_masuk' => ['label' => 'Tahun Masuk', 'type' => 'year'],
                    'tahun_lulus' => ['label' => 'Tahun Lulus', 'type' => 'year'],
                    'status' => ['label' => 'Status', 'type' => 'text'],
                    'file_ijazah_path' => ['label' => 'Dokumen Ijazah', 'type' => 'file'],
                    'file_transkrip_path' => ['label' => 'Dokumen Transkrip', 'type' => 'file'],
                ],
            ],
            'diklat' => [
                'label' => 'Diklat / Pelatihan', 'model' => EmployeeTraining::class, 'tab' => 'pendidikan-diklat', 'module' => 'Diklat',
                'fields' => [
                    'kategori' => ['label' => 'Jenis Diklat', 'type' => 'select', 'options' => [
                        'struktural' => 'Diklat Struktural',
                        'fungsional' => 'Diklat Fungsional',
                        'teknis' => 'Diklat Teknis',
                        'keahlian_profesi' => 'Sertifikat Keahlian / Profesi',
                        'bintek_seminar' => 'Bimbingan Teknis / Seminar',
                    ], 'required' => true, 'cols' => 1],
                    'nama_pelatihan' => ['label' => 'Nama Pelatihan', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'jenis_pelatihan' => ['label' => 'Jenis Pelatihan', 'type' => 'text'],
                    'penyelenggara' => ['label' => 'Penyelenggara', 'type' => 'text'],
                    'tempat' => ['label' => 'Tempat', 'type' => 'text'],
                    'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date'],
                    'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
                    'no_sertifikat' => ['label' => 'Nomor Sertifikat', 'type' => 'text'],
                    'masa_berlaku' => ['label' => 'Berlaku Sampai', 'type' => 'date'],
                    'file_sertifikat_path' => ['label' => 'Dokumen Sertifikat', 'type' => 'file'],
                ],
            ],
            'bahasa' => [
                'label' => 'Riwayat Bahasa', 'model' => EmployeeLanguage::class, 'tab' => 'pendidikan-diklat', 'module' => 'Bahasa',
                'fields' => [
                    'nama_bahasa' => ['label' => 'Nama Bahasa', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'tingkat' => ['label' => 'Tingkat', 'type' => 'select', 'options' => ['Pemula' => 'Pemula', 'Menengah' => 'Menengah', 'Mahir' => 'Mahir', 'Fasih' => 'Fasih']],
                    'kemampuan' => ['label' => 'Kemampuan', 'type' => 'select', 'options' => ['Lisan & Tulisan' => 'Lisan & Tulisan', 'Lisan' => 'Lisan', 'Tulisan' => 'Tulisan']],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                ],
            ],
            'jabatan' => [
                'label' => 'Riwayat Jabatan', 'model' => EmployeePositionHistory::class, 'tab' => 'kepegawaian', 'module' => 'Kepegawaian',
                'fields' => [
                    'position_id' => ['label' => 'Jabatan', 'type' => 'select', 'source' => 'positions', 'required' => true, 'cols' => 2],
                    'work_unit_id' => ['label' => 'Unit Kerja', 'type' => 'select', 'source' => 'work-units'],
                    'no_sk' => ['label' => 'Nomor SK', 'type' => 'text'],
                    'tanggal_sk' => ['label' => 'Tanggal SK', 'type' => 'date'],
                    'tmt' => ['label' => 'TMT', 'type' => 'date', 'required' => true],
                    'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
                    'alasan_perubahan' => ['label' => 'Alasan Perubahan', 'type' => 'textarea', 'cols' => 1],
                    'file_sk_path' => ['label' => 'Dokumen SK', 'type' => 'file'],
                ],
            ],
            'pangkat' => [
                'label' => 'Riwayat Pangkat', 'model' => EmployeeRankHistory::class, 'tab' => 'kepegawaian', 'module' => 'Kepegawaian',
                'fields' => [
                    'rank_id' => ['label' => 'Golongan / Pangkat', 'type' => 'select', 'source' => 'ranks', 'required' => true, 'cols' => 2],
                    'no_sk' => ['label' => 'Nomor SK', 'type' => 'text'],
                    'tanggal_sk' => ['label' => 'Tanggal SK', 'type' => 'date'],
                    'tmt' => ['label' => 'TMT', 'type' => 'date', 'required' => true],
                    'masa_kerja_tahun' => ['label' => 'Masa Kerja (Tahun)', 'type' => 'number'],
                    'masa_kerja_bulan' => ['label' => 'Masa Kerja (Bulan)', 'type' => 'number'],
                    'file_sk_path' => ['label' => 'Dokumen SK', 'type' => 'file'],
                ],
            ],
            'mutasi' => [
                'label' => 'Riwayat Mutasi', 'model' => EmployeeMutation::class, 'tab' => 'kepegawaian', 'module' => 'Kepegawaian',
                'fields' => [
                    'jenis_mutasi' => ['label' => 'Jenis Mutasi', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'unit_asal_id' => ['label' => 'Unit Asal', 'type' => 'select', 'source' => 'work-units'],
                    'unit_tujuan_id' => ['label' => 'Unit Tujuan', 'type' => 'select', 'source' => 'work-units'],
                    'jabatan_lama' => ['label' => 'Jabatan Lama', 'type' => 'text'],
                    'jabatan_baru' => ['label' => 'Jabatan Baru', 'type' => 'text'],
                    'tanggal_mutasi' => ['label' => 'Tanggal Mutasi', 'type' => 'date'],
                    'no_sk' => ['label' => 'Nomor SK', 'type' => 'text'],
                    'alasan' => ['label' => 'Alasan', 'type' => 'textarea', 'cols' => 1],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_sk_path' => ['label' => 'Dokumen SK', 'type' => 'file'],
                ],
            ],
            'kgb' => [
                'label' => 'Riwayat KGB', 'model' => EmployeeSalaryHistory::class, 'tab' => 'kepegawaian', 'module' => 'KGB',
                'fields' => [
                    'rank_id' => ['label' => 'Golongan', 'type' => 'select', 'source' => 'ranks', 'cols' => 1],
                    'gaji_pokok' => ['label' => 'Gaji Pokok', 'type' => 'number', 'required' => true, 'cols' => 1],
                    'no_sk' => ['label' => 'Nomor SK', 'type' => 'text'],
                    'tanggal_sk' => ['label' => 'Tanggal SK', 'type' => 'date'],
                    'tmt' => ['label' => 'TMT', 'type' => 'date', 'required' => true],
                    'masa_kerja_tahun' => ['label' => 'Masa Kerja (Tahun)', 'type' => 'number'],
                    'masa_kerja_bulan' => ['label' => 'Masa Kerja (Bulan)', 'type' => 'number'],
                    'file_sk_path' => ['label' => 'Dokumen SK', 'type' => 'file'],
                ],
            ],
            'cuti' => [
                'label' => 'Riwayat Cuti', 'model' => EmployeeLeave::class, 'tab' => 'kepegawaian', 'module' => 'Cuti',
                'fields' => [
                    'jenis_cuti' => ['label' => 'Jenis Cuti', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date'],
                    'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
                    'jumlah_hari' => ['label' => 'Jumlah Hari', 'type' => 'number'],
                    'no_sk' => ['label' => 'Nomor SK', 'type' => 'text'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_sk_path' => ['label' => 'Dokumen SK', 'type' => 'file'],
                ],
            ],
            'inaktif' => [
                'label' => 'Riwayat Inaktif', 'model' => EmployeeInactivePeriod::class, 'tab' => 'kepegawaian', 'module' => 'Kepegawaian',
                'fields' => [
                    'status' => ['label' => 'Status', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date'],
                    'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
                    'alasan' => ['label' => 'Alasan', 'type' => 'textarea', 'cols' => 1],
                    'no_sk' => ['label' => 'Nomor SK', 'type' => 'text'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                ],
            ],
            'kedudukan_hukum' => [
                'label' => 'Kedudukan Hukum', 'model' => EmployeeLegalStatus::class, 'tab' => 'kepegawaian', 'module' => 'Kedudukan Hukum',
                'fields' => [
                    'status' => ['label' => 'Status', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'kasus' => ['label' => 'Kasus', 'type' => 'textarea', 'cols' => 1],
                    'tanggal' => ['label' => 'Tanggal', 'type' => 'date'],
                    'no_putusan' => ['label' => 'Nomor Putusan', 'type' => 'text'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_path' => ['label' => 'Dokumen', 'type' => 'file'],
                ],
            ],
            'hukdis' => [
                'label' => 'Catatan Hukuman Disiplin', 'model' => EmployeeDiscipline::class, 'tab' => 'kepegawaian', 'module' => 'Hukdis',
                'fields' => [
                    'jenis_pelanggaran' => ['label' => 'Jenis Pelanggaran', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'tanggal' => ['label' => 'Tanggal', 'type' => 'date'],
                    'tingkat_pelanggaran' => ['label' => 'Tingkat Pelanggaran', 'type' => 'text'],
                    'sanksi' => ['label' => 'Sanksi', 'type' => 'text'],
                    'no_keputusan' => ['label' => 'Nomor Keputusan', 'type' => 'text'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_path' => ['label' => 'Dokumen', 'type' => 'file'],
                ],
            ],
            'penyakit' => [
                'label' => 'Riwayat Penyakit', 'model' => EmployeeDisease::class, 'tab' => 'kepegawaian', 'module' => 'Penyakit',
                'fields' => [
                    'nama_penyakit' => ['label' => 'Nama Penyakit', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'tanggal' => ['label' => 'Tanggal', 'type' => 'date'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_path' => ['label' => 'Dokumen', 'type' => 'file'],
                ],
            ],
            'kontak_darurat' => [
                'label' => 'Kontak Darurat', 'model' => EmployeeEmergencyContact::class, 'tab' => 'kepegawaian', 'module' => 'Kontak Darurat',
                'fields' => [
                    'nama' => ['label' => 'Nama Kontak', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'hubungan' => ['label' => 'Hubungan', 'type' => 'text'],
                    'telepon' => ['label' => 'No. Telepon', 'type' => 'text'],
                    'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'cols' => 1],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                ],
            ],
            'pmk' => [
                'label' => 'Peninjauan Masa Kerja', 'model' => EmployeePmkHistory::class, 'tab' => 'kepegawaian', 'module' => 'PMK',
                'fields' => [
                    'no_sk' => ['label' => 'Nomor SK', 'type' => 'text'],
                    'tanggal_sk' => ['label' => 'Tanggal SK', 'type' => 'date'],
                    'tmt' => ['label' => 'TMT', 'type' => 'date'],
                    'tambah_tahun' => ['label' => 'Tambah Masa Kerja (Tahun)', 'type' => 'number'],
                    'tambah_bulan' => ['label' => 'Tambah Masa Kerja (Bulan)', 'type' => 'number'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_sk_path' => ['label' => 'Dokumen SK', 'type' => 'file'],
                ],
            ],
            'kontrak_pppk' => [
                'label' => 'Riwayat Kontrak PPPK', 'model' => EmployeePppkContract::class, 'tab' => 'kepegawaian', 'module' => 'Kontrak PPPK',
                'fields' => [
                    'nomor_kontrak' => ['label' => 'Nomor Kontrak', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date'],
                    'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
                    'masa_kerja' => ['label' => 'Masa Kerja', 'type' => 'text'],
                    'instansi' => ['label' => 'Instansi', 'type' => 'text'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_kontrak_path' => ['label' => 'Dokumen Kontrak', 'type' => 'file'],
                ],
            ],
            'kinerja' => [
                'label' => 'Penilaian Kinerja', 'model' => EmployeePerformance::class, 'tab' => 'kinerja-penghargaan', 'module' => 'Kinerja',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'type' => 'number'],
                    'periode' => ['label' => 'Periode', 'type' => 'text'],
                    'uraian_penilaian' => ['label' => 'Uraian Penilaian', 'type' => 'textarea', 'cols' => 1],
                    'nilai' => ['label' => 'Nilai (0 – 100)', 'type' => 'number'],
                    'predikat' => ['label' => 'Predikat', 'type' => 'text'],
                    'pejabat_penilai' => ['label' => 'Pejabat Penilai', 'type' => 'text'],
                    'catatan' => ['label' => 'Catatan', 'type' => 'textarea', 'cols' => 1],
                    'file_path' => ['label' => 'Dokumen Penilaian', 'type' => 'file'],
                ],
            ],
            'skp' => [
                'label' => 'Sasaran Kinerja Pegawai', 'model' => EmployeeSkp::class, 'tab' => 'kinerja-penghargaan', 'module' => 'SKP',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'type' => 'year', 'required' => true],
                    'periode' => ['label' => 'Periode', 'type' => 'select', 'options' => ['Tahunan' => 'Tahunan', 'Semester I' => 'Semester I', 'Semester II' => 'Semester II']],
                    'uraian_kegiatan' => ['label' => 'Uraian Kegiatan', 'type' => 'textarea', 'cols' => 1],
                    'target' => ['label' => 'Target', 'type' => 'textarea', 'cols' => 1],
                    'realisasi' => ['label' => 'Realisasi', 'type' => 'textarea', 'cols' => 1],
                    'nilai' => ['label' => 'Nilai (0 – 100)', 'type' => 'number'],
                    'predikat' => ['label' => 'Predikat', 'type' => 'text'],
                    'pejabat_penilai' => ['label' => 'Pejabat Penilai', 'type' => 'text'],
                    'file_path' => ['label' => 'Dokumen SKP', 'type' => 'file'],
                ],
            ],
            'angka_kredit' => [
                'label' => 'Angka Kredit', 'model' => EmployeeCreditScore::class, 'tab' => 'kinerja-penghargaan', 'module' => 'Angka Kredit',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'type' => 'year'],
                    'unsur' => ['label' => 'Unsur', 'type' => 'select', 'options' => ['Utama' => 'Utama', 'Penunjang' => 'Penunjang']],
                    'butir_kegiatan' => ['label' => 'Butir Kegiatan', 'type' => 'text'],
                    'nilai_angka_kredit' => ['label' => 'Nilai Angka Kredit', 'type' => 'number'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_path' => ['label' => 'Dokumen', 'type' => 'file'],
                ],
            ],
            'ipasn' => [
                'label' => 'Indeks Profesionalitas ASN', 'model' => EmployeeIpasn::class, 'tab' => 'kinerja-penghargaan', 'module' => 'IPASN',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'type' => 'year', 'required' => true],
                    'komponen' => ['label' => 'Komponen', 'type' => 'select', 'options' => [
                        'Integritas' => 'Integritas',
                        'Kualitas Kerja' => 'Kualitas Kerja',
                        'Kompetensi' => 'Kompetensi',
                        'Kedisiplinan' => 'Kedisiplinan',
                        'Total' => 'Total Indeks',
                    ]],
                    'nilai' => ['label' => 'Nilai', 'type' => 'number'],
                    'predikat' => ['label' => 'Predikat', 'type' => 'text'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_path' => ['label' => 'Dokumen', 'type' => 'file'],
                ],
            ],
            'penghargaan' => [
                'label' => 'Penghargaan', 'model' => EmployeeAward::class, 'tab' => 'kinerja-penghargaan', 'module' => 'Penghargaan',
                'fields' => [
                    'nama_penghargaan' => ['label' => 'Nama Penghargaan', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'jenis_penghargaan' => ['label' => 'Jenis Penghargaan', 'type' => 'text'],
                    'pemberi_penghargaan' => ['label' => 'Pemberi', 'type' => 'text'],
                    'tingkat' => ['label' => 'Tingkat', 'type' => 'text'],
                    'tahun' => ['label' => 'Tahun', 'type' => 'year'],
                    'no_penghargaan' => ['label' => 'Nomor Piagam', 'type' => 'text'],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'file_path' => ['label' => 'Dokumen Piagam', 'type' => 'file'],
                ],
            ],
            'keluarga' => [
                'label' => 'Keluarga', 'model' => EmployeeFamily::class, 'tab' => 'keluarga', 'module' => 'Keluarga',
                'fields' => [
                    'type' => ['label' => 'Hubungan', 'type' => 'select', 'options' => ['pasangan' => 'Suami / Istri', 'anak' => 'Anak', 'orang_tua' => 'Orang Tua', 'saudara' => 'Saudara'], 'required' => true, 'cols' => 2],
                    'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'nik' => ['label' => 'NIK', 'type' => 'text'],
                    'tempat_lahir' => ['label' => 'Tempat Lahir', 'type' => 'text'],
                    'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date'],
                    'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
                    'pekerjaan' => ['label' => 'Pekerjaan', 'type' => 'text'],
                    'status' => ['label' => 'Status (mis. ANAK KANDUNG)', 'type' => 'text'],
                    'status_tanggungan' => ['label' => 'Status Tanggungan', 'type' => 'checkbox'],
                ],
            ],
            'aset' => [
                'label' => 'Aset Pegawai', 'model' => EmployeeAsset::class, 'tab' => 'aset', 'module' => 'Aset',
                'fields' => [
                    'asset_type_id' => ['label' => 'Jenis Aset', 'type' => 'select', 'source' => 'asset-types', 'required' => true, 'cols' => 2],
                    'nama_aset' => ['label' => 'Nama Aset / Deskripsi', 'type' => 'text', 'required' => true, 'cols' => 2],
                    'merk' => ['label' => 'Merk', 'type' => 'text'],
                    'no_seri' => ['label' => 'Nomor Seri', 'type' => 'text'],
                    'no_inventaris' => ['label' => 'Nomor Inventaris RS', 'type' => 'text'],
                    'tanggal_mulai' => ['label' => 'Mulai Dipakai dari Tanggal', 'type' => 'date', 'required' => true, 'cols' => 1],
                    'tanggal_selesai' => ['label' => 'Sampai Tanggal', 'type' => 'date', 'sub' => 'Kosongkan jika aset masih dipakai.', 'cols' => 1],
                    'kondisi' => ['label' => 'Kondisi', 'type' => 'select', 'options' => ['Baik' => 'Baik', 'Cukup Baik' => 'Cukup Baik', 'Rusak Ringan' => 'Rusak Ringan', 'Rusak Berat' => 'Rusak Berat']],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => [
                        'Masih Dipakai' => 'Masih Dipakai',
                        'Mutasi Aset' => 'Mutasi Aset (tidak dipakai lagi)',
                        'Dikembalikan' => 'Dikembalikan ke Rumah Sakit',
                    ]],
                    'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'cols' => 1],
                    'surat_mutasi_path' => ['label' => 'Foto Surat Mutasi Aset', 'type' => 'file', 'sub' => 'Unggah foto/scan surat mutasi bila status Mutasi Aset.'],
                    'surat_pengembalian_path' => ['label' => 'Foto Surat Pengembalian', 'type' => 'file', 'sub' => 'Unggah foto/scan surat pengembalian ke RS bila status Dikembalikan.'],
                    'file_path' => ['label' => 'Kartu Inventaris / BAST', 'type' => 'file'],
                ],
            ],
        ];
    }

    private function optionsFor(array $conf): array
    {
        $map = [];
        foreach ($conf['fields'] as $name => $field) {
            if (isset($field['options'])) {
                $map[$name] = $field['options'];
            } elseif (($field['source'] ?? null) === 'education-levels') {
                $map[$name] = EducationLevel::orderBy('urutan')->pluck('name', 'id')->all();
            } elseif (($field['source'] ?? null) === 'asset-types') {
                $map[$name] = AssetType::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
            } elseif (($field['source'] ?? null) === 'positions') {
                $map[$name] = Position::orderBy('name')->pluck('name', 'id')->all();
            } elseif (($field['source'] ?? null) === 'ranks') {
                $map[$name] = Rank::orderBy('urutan')->get()->mapWithKeys(fn ($r) => [$r->id => trim($r->golongan . ($r->pangkat ? ' — ' . $r->pangkat : ''))])->all();
            } elseif (($field['source'] ?? null) === 'work-units') {
                $map[$name] = WorkUnit::orderBy('name')->pluck('name', 'id')->all();
            }
        }
        return $map;
    }

    private function rulesFor(array $conf): array
    {
        $rules = [];
        foreach ($conf['fields'] as $name => $field) {
            $r = [];
            if ($field['type'] === 'file') {
                $r[] = 'nullable';
                $r[] = 'file';
                $r[] = 'mimes:pdf,jpg,jpeg,png';
                $r[] = 'max:5120';
            } else {
                $r[] = 'nullable';
                if (($field['required'] ?? false)) {
                    $r[] = 'required';
                }
                if ($field['type'] === 'date') {
                    $r[] = 'date';
                }
                if ($field['type'] === 'number') {
                    $r[] = 'numeric';
                }
                if ($field['type'] === 'year') {
                    $r[] = 'digits:4';
                }
            }
            $rules[$name] = $r;
        }
        return $rules;
    }

    private function storeFile(Request $request, string $field): ?string
    {
        $file = $request->file($field);
        if (!$file) {
            return null;
        }
        $dir = 'sub/' . strtolower(str_replace('_path', '', $field));
        return $file->store($dir, 'public');
    }

    /**
     * Admin/Super Admin boleh akses semua employee; role user hanya data miliknya sendiri.
     */
    private function authorizeAccess(Employee $employee): void
    {
        $user = request()->user();
        if ($user && in_array($user->role, ['super_admin', 'admin'], true)) {
            return;
        }
        abort_unless($user && $employee->user_id === $user->id, 403, 'Anda hanya bisa mengelola data pegawai milik sendiri.');
    }

    public function create(Employee $employee, string $type)
    {
        $this->authorizeAccess($employee);
        [$conf] = $this->resolve($type);
        return view('sub.form', [
            'employee' => $employee,
            'conf' => $conf,
            'options' => $this->optionsFor($conf),
            'row' => null,
            'type' => $type,
        ]);
    }

    public function store(Request $request, Employee $employee, string $type)
    {
        $this->authorizeAccess($employee);
        [$conf] = $this->resolve($type);
        $validated = $request->validate($this->rulesFor($conf));

        $data = ['employee_id' => $employee->id];
        foreach (array_keys($conf['fields']) as $field) {
            $f = $conf['fields'][$field];
            if ($f['type'] === 'file') {
                if ($path = $this->storeFile($request, $field)) {
                    $data[$field] = $path;
                }
            } elseif ($f['type'] === 'checkbox') {
                $data[$field] = $request->boolean($field);
            } else {
                $data[$field] = $request->filled($field) ? $request->input($field) : null;
            }
        }

        $model = $conf['model']::create($data);
        AuditLog::record('create', $conf['module'], $model, 'Tambah ' . $conf['label'] . ' untuk ' . $employee->nama_lengkap);

        return $this->redirectAfterSave($conf, $employee, $conf['label'] . ' berhasil ditambahkan.');
    }

    public function edit(Employee $employee, string $type, int $id)
    {
        $this->authorizeAccess($employee);
        [$conf] = $this->resolve($type);
        $row = $conf['model']::where('employee_id', $employee->id)->findOrFail($id);
        return view('sub.form', [
            'employee' => $employee,
            'conf' => $conf,
            'options' => $this->optionsFor($conf),
            'row' => $row,
            'type' => $type,
        ]);
    }

    public function update(Request $request, Employee $employee, string $type, int $id)
    {
        $this->authorizeAccess($employee);
        [$conf] = $this->resolve($type);
        $row = $conf['model']::where('employee_id', $employee->id)->findOrFail($id);
        $request->validate($this->rulesFor($conf));

        foreach (array_keys($conf['fields']) as $field) {
            $f = $conf['fields'][$field];
            if ($f['type'] === 'file') {
                if ($path = $this->storeFile($request, $field)) {
                    if ($row->{$field}) {
                        Storage::disk('public')->delete($row->{$field});
                    }
                    $row->{$field} = $path;
                }
            } elseif ($f['type'] === 'checkbox') {
                $row->{$field} = $request->boolean($field);
            } else {
                $row->{$field} = $request->filled($field) ? $request->input($field) : null;
            }
        }
        $row->save();

        AuditLog::record('update', $conf['module'], $row, 'Ubah ' . $conf['label'] . ' milik ' . $employee->nama_lengkap);
        return $this->redirectAfterSave($conf, $employee, $conf['label'] . ' berhasil diperbarui.');
    }

    public function destroy(Employee $employee, string $type, int $id)
    {
        $this->authorizeAccess($employee);
        [$conf] = $this->resolve($type);
        $row = $conf['model']::where('employee_id', $employee->id)->findOrFail($id);
        foreach (array_keys($conf['fields']) as $field) {
            if (($conf['fields'][$field]['type'] ?? null) === 'file' && $row->{$field}) {
                Storage::disk('public')->delete($row->{$field});
            }
        }
        $row->delete();

        AuditLog::record('delete', $conf['module'], $row, 'Hapus ' . $conf['label'] . ' milik ' . $employee->nama_lengkap);
        return $this->redirectAfterSave($conf, $employee, $conf['label'] . ' berhasil dihapus.');
    }

    public function download(Request $request, string $type, int $id)
    {
        [$conf] = $this->resolve($type);
        $row = $conf['model']::findOrFail($id);

        $user = $request->user();
        if ($user->role === 'user' && $row->employee_id !== $user->employee?->id) {
            abort(403, 'Anda hanya bisa mengunduh lampiran milik sendiri.');
        }

        $path = null;
        $field = $request->query('field');
        if ($field && isset($conf['fields'][$field]) && ($conf['fields'][$field]['type'] ?? null) === 'file') {
            $path = $row->{$field};
        } else {
            foreach (array_keys($conf['fields']) as $f) {
                if (($conf['fields'][$f]['type'] ?? null) === 'file' && $row->{$f}) {
                    $path = $row->{$f};
                    break;
                }
            }
        }
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        return Storage::disk('public')->download($path);
    }

    /**
     * Arahkan kembali ke halaman asal setelah operasi sub-data:
     * user pemilik diarahkan ke "Profil Saya", admin/SA ke halaman pegawai.
     */
    private function redirectAfterSave(array $conf, Employee $employee, string $message): \Illuminate\Http\RedirectResponse
    {
        $fragment = 'tab-' . $conf['tab'];
        $user = request()->user();
        if ($user && $user->role === 'user') {
            return redirect()->route('profile.edit')
                ->with('fragment', $fragment)
                ->with('success', $message);
        }
        return redirect()->route('employees.show', $employee)
            ->with('fragment', $fragment)
            ->with('success', $message);
    }

    private function resolve(string $type): array
    {
        $types = $this->config();
        abort_unless(isset($types[$type]), 404);
        return [$types[$type]];
    }
}