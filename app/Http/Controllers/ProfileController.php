<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Models\AuditLog;
use App\Models\EmployeeCategory;
use App\Models\EmploymentStatus;
use App\Models\Notification;
use App\Models\Position;
use App\Models\Rank;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private array $editableFields = [
        'nama_lengkap', 'gelar_depan', 'gelar_belakang', 'nik',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'status_perkawinan',
        'alamat_rumah', 'kelurahan_rumah', 'kecamatan_rumah', 'kabkota_rumah', 'provinsi_rumah', 'kodepos_rumah',
        'hp', 'email_pribadi',
        // Informasi Kepegawaian (diajukan, menunggu persetujuan Super Admin)
        'employment_status_id', 'employee_category_id', 'jenis_asn',
        'work_unit_id', 'current_position_id', 'jenis_jabatan', 'golongan_akhir_id',
        'tmt_jabatan', 'tmt_skpd', 'masa_kerja_tahun', 'masa_kerja_bulan',
    ];

    /**
     * Modul 14 — halaman "Profil Saya" untuk User.
     */
    public function edit(Request $request): View
    {
        $employee = $request->user()->employee;

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
                'employeeCategories' => EmployeeCategory::orderBy('name')->get(['id', 'name']),
                'employmentStatuses' => EmploymentStatus::orderBy('name')->get(['id', 'name']),
            ]
        ));
    }

    /**
     * Simpan sebagai PENGAJUAN, bukan langsung mengubah data resmi.
     */
    public function update(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'gelar_depan' => ['nullable', 'string', 'max:255'],
            'gelar_belakang' => ['nullable', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:255', Rule::unique('employees', 'nik')->ignore($employee->id)],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'string'],
            'status_perkawinan' => ['nullable', 'string'],
            'alamat_rumah' => ['nullable', 'string'],
            'kelurahan_rumah' => ['nullable', 'string', 'max:255'],
            'kecamatan_rumah' => ['nullable', 'string', 'max:255'],
            'kabkota_rumah' => ['nullable', 'string', 'max:255'],
            'provinsi_rumah' => ['nullable', 'string', 'max:255'],
            'kodepos_rumah' => ['nullable', 'string', 'max:10'],
            'hp' => ['nullable', 'string', 'max:255'],
            'email_pribadi' => ['nullable', 'email'],
            'employment_status_id' => ['nullable', 'integer', 'exists:employment_statuses,id'],
            'employee_category_id' => ['nullable', 'integer', 'exists:employee_categories,id'],
            'jenis_asn' => ['nullable', 'in:PNS,PPPK'],
            'work_unit_id' => ['nullable', 'integer', 'exists:work_units,id'],
            'current_position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'jenis_jabatan' => ['nullable', 'in:struktural,fungsional,pelaksana'],
            'golongan_akhir_id' => ['nullable', 'integer', 'exists:ranks,id'],
            'tmt_jabatan' => ['nullable', 'date'],
            'tmt_skpd' => ['nullable', 'date'],
            'masa_kerja_tahun' => ['nullable', 'integer', 'min:0', 'max:70'],
            'masa_kerja_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
        ]);

        $oldData = collect($this->editableFields)
            ->mapWithKeys(fn ($field) => [$field => $employee->{$field}])
            ->toArray();

        $requestObj = ChangeRequest::create([
            'employee_id' => $employee->id,
            'module_type' => 'profil',
            'old_data' => $oldData,
            'new_data' => $data,
            'status' => 'pending',
        ]);

        // Notifikasi ke seluruh Super Admin
        $superAdmins = User::where('role', 'super_admin')->pluck('id');
        foreach ($superAdmins as $saId) {
            Notification::send(
                userId: $saId,
                title: 'Ada pengajuan perubahan data baru',
                body: $employee->nama_lengkap . ' mengajukan perubahan data pribadi.',
                url: route('approvals.show', $requestObj),
            );
        }

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Perubahan data berhasil diajukan, menunggu persetujuan Super Admin.');
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
            description: 'Memperbarui foto profil ' . $employee->nama_lengkap,
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
                description: 'Menghapus foto profil ' . $employee->nama_lengkap,
            );
        }

        return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil dihapus.');
    }
}
