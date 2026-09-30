<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ChangeRequest;
use App\Models\BludCategory;
use App\Models\EducationLevel;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Notification;
use App\Models\OutsourcingJob;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\Rank;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private array $editableFields = [
        'nama_lengkap', 'nama_panggilan', 'gelar_depan', 'gelar_belakang', 'nik', 'no_kk',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'status_perkawinan', 'golongan_darah',
        'no_npwp', 'no_bpjs', 'no_karpeg', 'no_karis_karsu', 'no_taspen', 'no_rekening', 'bank', 'bapertarum',
        'pendidikan_awal_id', 'tahun_pendidikan_awal', 'pendidikan_akhir_id', 'tahun_pendidikan_akhir', 'izin_pemakaian_gelar',
        // Alamat & Kontak
        'alamat_rumah', 'rt_rumah', 'rw_rumah', 'kelurahan_rumah', 'kecamatan_rumah', 'kabkota_rumah', 'provinsi_rumah', 'kodepos_rumah',
        'alamat_domisili_ktp', 'rt_domisili', 'rw_domisili', 'kelurahan_domisili', 'kecamatan_domisili', 'kabkota_domisili', 'provinsi_domisili', 'kodepos_domisili',
        'telp', 'hp', 'email_pribadi', 'email_resmi',
        // Informasi Kepegawaian (referensi; dikelola via halaman Edit Profil admin)
'status_pegawai', 'outsourcing_job_id', 'employment_status_id',
        'jenis_asn', 'status_calon', 'kedudukan_pegawai', 'kepemilikan_kpe',
        'work_unit_id', 'current_position_id', 'jenis_jabatan', 'eselon', 'tmt_eselon',
        'tmt_jabatan', 'tmt_skpd', 'tugas_tambahan_1', 'tmt_tugas_tambahan_1',
        'tugas_tambahan_2', 'tmt_tugas_tambahan_2', 'instansi_dipekerjakan',
        'golongan_awal_id', 'tmt_golongan_awal', 'golongan_akhir_id', 'tmt_golongan_akhir',
        'masa_kerja_tahun', 'masa_kerja_bulan', 'gaji_pokok', 'tmt_gaji_berkala_terbaru',
    ];

    /**
     * Modul 14 — halaman "Profil Saya" untuk User.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;

        // Admin yang akunnya belum terhubung ke record pegawai mendapat record
        // kosong agar dapat melengkapi profil pribadinya sendiri. Super Admin
        // tidak dipaksa memiliki profil pegawai.
        if (! $employee && $user->role !== 'super_admin') {
            $employee = Employee::create([
                'user_id' => $user->id,
                'nip' => $user->nip,
                'nama_lengkap' => $user->name,
                'is_draft' => true,
            ]);
        }

        $pendingRequest = $employee
            ? ChangeRequest::where('employee_id', $employee->id)
                ->where('module_type', 'profil')
                ->pending()
                ->latest()
                ->first()
            : null;

        return view('profile.edit', array_merge(
            compact('employee', 'pendingRequest'),
            [
                'workUnits' => WorkUnit::orderBy('name')->get(['id', 'name']),
                'positions' => Position::orderBy('name')->get(['id', 'name']),
                'ranks' => Rank::orderBy('urutan')->get(['id', 'golongan', 'pangkat']),
                'educationLevels' => EducationLevel::orderBy('urutan')->get(['id', 'name']),
                'bludCategories' => BludCategory::orderBy('name')->get(['id', 'name']),
                'employmentStatuses' => EmploymentStatus::orderBy('name')->get(['id', 'name']),
                'positionTypes' => PositionType::where('is_active', true)->orderBy('name')->get(['code', 'name']),
                'outsourcingJobs' => OutsourcingJob::orderBy('name')->get(['id', 'name']),
            ]
        ));
    }

    /**
     * Simpan sebagai PENGAJUAN, bukan langsung mengubah data resmi.
     */
    public function update(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_if(! $employee, 403, 'Akun Anda belum terhubung ke data pegawai.');

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nama_panggilan' => ['nullable', 'string', 'max:255'],
            'gelar_depan' => ['nullable', 'string', 'max:255'],
            'gelar_belakang' => ['nullable', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:255', Rule::unique('employees', 'nik')->ignore($employee->id)],
            'no_kk' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'string'],
            'status_perkawinan' => ['nullable', 'string'],
            'golongan_darah' => ['nullable', 'string', 'max:5'],
            'no_npwp' => ['nullable', 'string', 'max:255'],
            'no_bpjs' => ['nullable', 'string', 'max:255'],
            'no_karpeg' => ['nullable', 'string', 'max:255'],
            'no_karis_karsu' => ['nullable', 'string', 'max:255'],
            'no_taspen' => ['nullable', 'string', 'max:255'],
            'no_rekening' => ['nullable', 'string', 'max:255'],
            'bank' => ['nullable', 'string', 'max:255'],
            'bapertarum' => ['nullable', 'in:Sudah Diambil,Belum Diambil,Tidak Ada'],
            'pendidikan_awal_id' => ['nullable', 'integer', 'exists:education_levels,id'],
            'tahun_pendidikan_awal' => ['nullable', 'string', 'max:10'],
            'pendidikan_akhir_id' => ['nullable', 'integer', 'exists:education_levels,id'],
            'tahun_pendidikan_akhir' => ['nullable', 'string', 'max:10'],
            'izin_pemakaian_gelar' => ['nullable'],
            'alamat_rumah' => ['nullable', 'string'],
            'rt_rumah' => ['nullable', 'string', 'max:5'],
            'rw_rumah' => ['nullable', 'string', 'max:5'],
            'kelurahan_rumah' => ['nullable', 'string', 'max:255'],
            'kecamatan_rumah' => ['nullable', 'string', 'max:255'],
            'kabkota_rumah' => ['nullable', 'string', 'max:255'],
            'provinsi_rumah' => ['nullable', 'string', 'max:255'],
            'kodepos_rumah' => ['nullable', 'string', 'max:10'],
            'alamat_domisili_ktp' => ['nullable', 'string'],
            'rt_domisili' => ['nullable', 'string', 'max:5'],
            'rw_domisili' => ['nullable', 'string', 'max:5'],
            'kelurahan_domisili' => ['nullable', 'string', 'max:255'],
            'kecamatan_domisili' => ['nullable', 'string', 'max:255'],
            'kabkota_domisili' => ['nullable', 'string', 'max:255'],
            'provinsi_domisili' => ['nullable', 'string', 'max:255'],
            'kodepos_domisili' => ['nullable', 'string', 'max:10'],
            'telp' => ['nullable', 'string', 'max:255'],
            'hp' => ['nullable', 'string', 'max:255'],
            'email_pribadi' => ['nullable', 'email'],
            'email_resmi' => ['nullable', 'email'],
            // Informasi Kepegawaian (referensi; dikelola via Edit Profil admin)
            'status_pegawai' => ['nullable', 'in:PNS,PPPK,Honorer,Kontrak,Outsourcing'],
            'outsourcing_job_id' => ['nullable', 'integer', 'exists:outsourcing_jobs,id'],
            'employment_status_id' => ['nullable', 'integer', 'exists:employment_statuses,id'],
            'jenis_asn' => ['nullable', 'in:PNS,PPPK'],
            'status_calon' => ['nullable', 'string', 'max:255'],
            'kedudukan_pegawai' => ['nullable', 'string', 'max:255'],
            'kepemilikan_kpe' => ['nullable'],
            'work_unit_id' => ['nullable', 'integer', 'exists:work_units,id'],
            'current_position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'jenis_jabatan' => ['nullable', 'in:'.implode(',', PositionType::pluck('code')->all())],
            'eselon' => ['nullable', 'string', 'max:255'],
            'tmt_eselon' => ['nullable', 'date'],
            'tmt_jabatan' => ['nullable', 'date'],
            'tmt_skpd' => ['nullable', 'date'],
            'tugas_tambahan_1' => ['nullable', 'string', 'max:255'],
            'tmt_tugas_tambahan_1' => ['nullable', 'date'],
            'tugas_tambahan_2' => ['nullable', 'string', 'max:255'],
            'tmt_tugas_tambahan_2' => ['nullable', 'date'],
            'instansi_dipekerjakan' => ['nullable', 'string', 'max:255'],
            'golongan_awal_id' => ['nullable', 'integer', 'exists:ranks,id'],
            'tmt_golongan_awal' => ['nullable', 'date'],
            'golongan_akhir_id' => ['nullable', 'integer', 'exists:ranks,id'],
            'tmt_golongan_akhir' => ['nullable', 'date'],
            'masa_kerja_tahun' => ['nullable', 'integer', 'min:0', 'max:70'],
            'masa_kerja_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
            'gaji_pokok' => ['nullable', 'string', 'max:255'],
            'tmt_gaji_berkala_terbaru' => ['nullable', 'date'],
        ]);

        $oldData = collect($this->editableFields)
            ->mapWithKeys(fn ($field) => [$field => $employee->{$field}])
            ->toArray();

        // Status kepegawaian serta pangkat/gaji dikelola via halaman Edit Profil
        // (Data Pegawai) — bukan lewat pengajuan. Diberlakukan sama untuk semua role
        // agar pengajuan selalu konsisten.
        $data = Arr::except($data, [
'status_pegawai', 'outsourcing_job_id', 'employment_status_id',
            'jenis_asn', 'status_calon', 'kedudukan_pegawai', 'kepemilikan_kpe',
            'golongan_awal_id', 'tmt_golongan_awal', 'golongan_akhir_id', 'tmt_golongan_akhir',
            'masa_kerja_tahun', 'masa_kerja_bulan', 'gaji_pokok', 'tmt_gaji_berkala_terbaru',
        ]);

        // Jabatan & Organisasi: boleh diisi/diajukan pertama kali oleh pegawai.
        // Jika jabatan/unit sudah terdata, perubahannya hanya lewat Riwayat Mutasi.
        if (filled($employee->current_position_id)) {
            $data = Arr::except($data, ['current_position_id', 'jenis_jabatan', 'tmt_jabatan']);
        }
        if (filled($employee->work_unit_id)) {
            $data = Arr::except($data, ['work_unit_id', 'tmt_skpd']);
        }

        $requestObj = ChangeRequest::create([
            'employee_id' => $employee->id,
            'module_type' => 'profil',
            'old_data' => $oldData,
            'new_data' => $data,
            'status' => 'pending',
        ]);

        // Notifikasi ke Super Admin dan Admin agar pengajuan dapat diproses
        // sesuai kewenangan (approver tidak boleh memproses pengajuannya sendiri).
        $recipients = User::whereIn('role', ['super_admin', 'admin'])
            ->where('id', '!=', $request->user()->id)
            ->pluck('id');

        foreach ($recipients as $recipientId) {
            Notification::send(
                userId: $recipientId,
                title: 'Ada pengajuan perubahan data baru',
                body: $employee->nama_lengkap.' mengajukan perubahan data pribadi.',
                url: route('approvals.show', $requestObj),
            );
        }

        AuditLog::record(
            action: 'update',
            module: 'Profil',
            reference: $employee,
            description: 'Mengajukan perubahan data profil '.$employee->nama_lengkap.' (modul profil).',
        );

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Perubahan data berhasil diajukan, menunggu persetujuan.');
    }

    /**
     * Unggah/ganti foto profil (foto milik sendiri).
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_if(! $employee, 403, 'Akun Anda belum terhubung ke data pegawai.');

        $data = $request->validate([
            'foto' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('foto')->store('fotos', 'public');
        if ($employee->foto_path) {
            Storage::disk('public')->delete($employee->foto_path);
        }
        $employee->foto_path = $path;
        $employee->save();

        AuditLog::record(
            action: 'update',
            module: 'Profil',
            reference: $employee,
            description: 'Memperbarui foto profil '.$employee->nama_lengkap,
        );

        return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil.
     */
    public function deletePhoto(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_if(! $employee, 403, 'Akun Anda belum terhubung ke data pegawai.');

        if ($employee->foto_path) {
            Storage::disk('public')->delete($employee->foto_path);
            $employee->foto_path = null;
            $employee->save();

            AuditLog::record(
                action: 'update',
                module: 'Profil',
                reference: $employee,
                description: 'Menghapus foto profil '.$employee->nama_lengkap,
            );
        }

        return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil dihapus.');
    }
}
