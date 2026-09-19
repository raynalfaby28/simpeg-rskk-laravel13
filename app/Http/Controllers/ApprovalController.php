<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ChangeRequest;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    /**
     * Modul 19 — Approval Center: daftar pengajuan dengan filter status & modul.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');
        $module = $request->get('module');
        $q = $request->get('q');

        $query = ChangeRequest::with('employee');

        if (! in_array($status, ['all', 'reviewed', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($module) {
            $query->where('module_type', $module);
        }
        if ($q) {
            $query->whereHas('employee', fn ($qq) => $qq->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%"));
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'pending' => ChangeRequest::pending()->count(),
            'reviewed' => ChangeRequest::where('status', 'reviewed')->count(),
            'approved' => ChangeRequest::where('status', 'approved')->count(),
            'rejected' => ChangeRequest::where('status', 'rejected')->count(),
        ];

        return view('approvals.index', compact('requests', 'stats', 'status', 'module', 'q'));
    }

    /**
     * Bandingkan data lama vs data baru sebelum diputuskan.
     */
    public function show(Request $request, ChangeRequest $approval): View
    {
        $approval->load('employee.user', 'approver', 'reviewer');

        $canDecide = $this->decisionDenialReason($request->user(), $approval) === null;

        return view('approvals.show', ['request' => $approval, 'canDecide' => $canDecide]);
    }

    public function approve(Request $request, ChangeRequest $approval): RedirectResponse
    {
        $denial = $this->decisionDenialReason($request->user(), $approval);
        if ($denial) {
            abort(403, $denial);
        }

        if ($approval->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $conflict = $this->uniqueConflicts($approval);
        if ($conflict) {
            return back()->with('error', 'Tidak dapat menyetujui: '.$conflict);
        }

        $actorLabel = $this->actorLabel($request->user());

        try {
            DB::transaction(function () use ($approval, $request) {
                $approval->approve($request->user());
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyetujui pengajuan: '.(method_exists($e, 'getMessage') ? $e->getMessage() : 'terjadi kesalahan data.').' Coba tolak pengajuan ini agar pegawai dapat memperbaiki datanya.');
        }

        $this->notifyEmployee($approval, 'approved');

        AuditLog::record(
            action: 'approve',
            module: 'Approval',
            reference: $approval,
            description: "{$actorLabel} menyetujui pengajuan data {$approval->employee->nama_lengkap} (modul {$approval->module_type}).",
        );

        return redirect()
            ->route('approvals.index')
            ->with('success', "Perubahan data {$approval->employee->nama_lengkap} disetujui, data resmi telah diperbarui.");
    }

    public function reject(Request $request, ChangeRequest $approval): RedirectResponse
    {
        $denial = $this->decisionDenialReason($request->user(), $approval);
        if ($denial) {
            abort(403, $denial);
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($approval->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $approval->reject($request->user(), $data['rejection_reason']);

        $this->notifyEmployee($approval, 'rejected');

        AuditLog::record(
            action: 'reject',
            module: 'Approval',
            reference: $approval,
            description: "{$this->actorLabel($request->user())} menolak pengajuan data {$approval->employee->nama_lengkap} (modul {$approval->module_type}).",
        );

        return redirect()
            ->route('approvals.index')
            ->with('success', 'Pengajuan ditolak, alasan sudah dikirim ke pegawai.');
    }

    /**
     * Alasan penolakan keputusan approval (null = boleh memutuskan).
     * Admin boleh memutuskan pengajuan pegawai lain sesuai kewenangan,
     * TETAPI tidak boleh memproses akun Super Admin maupun pengajuannya sendiri
     * (pemisahan pembuat data dan approver).
     */
    private function decisionDenialReason(User $actor, ChangeRequest $approval): ?string
    {
        if ($actor->role === 'super_admin') {
            return null;
        }

        if ($actor->role !== 'admin') {
            return 'Anda tidak memiliki izin untuk memproses pengajuan ini.';
        }

        $target = $approval->employee?->user;

        if (! $target) {
            return 'Pengajuan ini tidak terhubung ke akun pengguna.';
        }

        if ($target->role === 'super_admin') {
            return 'Admin tidak memiliki izin untuk memproses pengajuan akun Super Admin.';
        }

        if ($target->id === $actor->id) {
            return 'Anda tidak dapat memproses pengajuan perubahan data Anda sendiri.';
        }

        return null;
    }

    private function actorLabel(User $user): string
    {
        return $user->role === 'super_admin' ? 'Super Admin' : 'Admin';
    }

    private function uniqueConflicts(ChangeRequest $approval): ?string
    {
        $new = $approval->new_data ?? [];

        if (filled($new['nik'] ?? null)) {
            $taken = Employee::where('nik', $new['nik'])
                ->where('id', '!=', $approval->employee_id)
                ->first();
            if ($taken) {
                return "NIK {$new['nik']} sudah dipakai pegawai lain ({$taken->nama_lengkap}). Tolak pengajuan ini agar pegawai dapat memperbaiki datanya.";
            }
        }

        return null;
    }

    private function notifyEmployee(ChangeRequest $approval, string $result): void
    {
        $user = $approval->employee?->user;
        if (! $user) {
            return;
        }

        $moduleLabel = str_replace('_', ' ', $approval->module_type);

        if ($result === 'approved') {
            Notification::send(
                userId: $user->id,
                title: 'Perubahan data Anda disetujui',
                body: "Pengajuan {$moduleLabel} telah disetujui dan data resmi diperbarui.",
                url: route('dashboard'),
            );
        } else {
            Notification::send(
                userId: $user->id,
                title: 'Pengajuan perubahan data ditolak',
                body: "Pengajuan {$moduleLabel} ditolak. Alasan: {$approval->rejection_reason}",
                url: route('dashboard'),
            );
        }
    }
}
