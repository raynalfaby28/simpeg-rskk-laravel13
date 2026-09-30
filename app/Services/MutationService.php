<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ChangeRequest;
use App\Models\Employee;
use App\Models\EmployeeMutation;
use App\Models\Position;
use Illuminate\Support\Facades\Storage;

class MutationService
{
    /**
     * Riwayat Mutasi = sumber kebenaran jabatan & unit kerja pegawai.
     * Setiap kali baris mutasi disimpan/diubah, jabatan dan unit kerja saat ini
     * pada profil pegawai ikut diperbarui.
     */
    public function syncFromMutation(Employee $employee, EmployeeMutation $mutation): void
    {
        $dirty = false;

        if (filled($mutation->jabatan_baru)) {
            $position = Position::where('name', $mutation->jabatan_baru)->first();
            if ($position && $employee->current_position_id !== $position->id) {
                $employee->current_position_id = $position->id;
                $dirty = true;
            }
        }

        if (filled($mutation->unit_tujuan_id)) {
            $unitId = (int) $mutation->unit_tujuan_id;
            if ($employee->work_unit_id !== $unitId) {
                $employee->work_unit_id = $unitId;
                $dirty = true;
            }
        }

        if ($dirty) {
            $employee->save();
            AuditLog::record(
                action: 'update',
                module: 'Kepegawaian',
                reference: $employee,
                description: 'Jabatan/Unit Kerja diperbarui otomatis dari Riwayat Mutasi untuk '.$employee->nama_lengkap,
            );
        }
    }

    /**
     * Terapkan pengajuan Riwayat Mutasi yang telah disetujui Super Admin/Admin.
     * new_data berisi kolom mutasi + _mutation_operation (create|update|delete)
     * + _mutation_id (khusus update/delete).
     */
    public function applyPendingRequest(ChangeRequest $changeRequest): void
    {
        $payload = $changeRequest->new_data ?? [];
        $operation = $payload['_mutation_operation'] ?? 'create';
        $mutationId = $payload['_mutation_id'] ?? null;
        unset($payload['_mutation_operation'], $payload['_mutation_id']);

        $employee = $changeRequest->employee;
        if (! $employee) {
            return;
        }

        if ($operation === 'delete') {
            $mutation = EmployeeMutation::where('employee_id', $employee->id)
                ->where('id', $mutationId)
                ->first();

            if ($mutation) {
                foreach ($payload as $field => $value) {
                    if (str_ends_with($field, '_path') && is_string($value) && $value !== '') {
                        Storage::disk('public')->delete($value);
                    }
                }
                $mutation->delete();
            }

            return;
        }

        $mutation = null;
        if ($operation === 'update' && $mutationId) {
            $mutation = EmployeeMutation::where('employee_id', $employee->id)
                ->where('id', $mutationId)
                ->first();
        }

        if ($mutation) {
            foreach ($payload as $field => $value) {
                if (str_ends_with($field, '_path') && is_string($value) && $value !== ''
                    && $mutation->{$field} && $mutation->{$field} !== $value) {
                    Storage::disk('public')->delete($mutation->{$field});
                }
            }
            $mutation->update($payload);
        } else {
            $mutation = EmployeeMutation::create(array_merge(['employee_id' => $employee->id], $payload));
        }

        $this->syncFromMutation($employee, $mutation);
    }

    /**
     * Bersihkan berkas SEMENTARA saat pengajuan mutasi ditolak.
     * Diabaikan untuk operasi delete — new_data hanya berisi nilai lama yang
     * masih terpakai oleh baris mutasi yang tetap ada.
     */
    public function discardPendingFiles(ChangeRequest $changeRequest): void
    {
        if (($changeRequest->new_data['_mutation_operation'] ?? 'create') === 'delete') {
            return;
        }

        foreach (($changeRequest->new_data ?? []) as $field => $value) {
            if (str_ends_with($field, '_path') && is_string($value) && $value !== '') {
                Storage::disk('public')->delete($value);
            }
        }
    }
}