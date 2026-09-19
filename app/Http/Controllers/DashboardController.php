<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ChangeRequest;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeMutation;
use App\Models\EmployeeTraining;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->role === 'user') {
            return $this->userDashboard($user);
        }

        return $this->adminDashboard($user);
    }

    private function userDashboard($user): View
    {
        $employee = $user->employee;

        $employee->load([
            'employmentStatus', 'currentPosition', 'workUnit', 'golonganAkhir',
        ]);

        $changeRequests = collect();
        if ($employee) {
            $changeRequests = ChangeRequest::where('employee_id', $employee->id)
                ->latest()
                ->limit(8)
                ->get();
        }

        $statusCounts = [
            'pending' => $changeRequests->where('status', 'pending')->count(),
            'approved' => $changeRequests->where('status', 'approved')->count(),
            'rejected' => $changeRequests->where('status', 'rejected')->count(),
        ];
$notifications = $user->appNotifications()->latest()->limit(5)->get();
        $unreadCount = $user->appNotifications()->unread()->count();

        return view('dashboard', [
            'employee' => $employee,
            'changeRequests' => $changeRequests,
            'statusCounts' => $statusCounts,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'role' => 'user',
        ]);
    }

    private function adminDashboard($user): View
    {
        $totals = [
            'pegawai' => Employee::count(),
            'aktif' => Employee::whereRelation('employmentStatus', 'name', 'Aktif')->count(),
            'baru' => Employee::where('created_at', '>=', now()->subDays(30))->count(),
        ];
        $totals['nonaktif'] = max($totals['pegawai'] - $totals['aktif'], 0);

        $pendingApprovals = ChangeRequest::pending()->count();
        $unverifiedDocuments = Document::where('status_verifikasi', '!=', 'terverifikasi')->count();
        $incompleteProfiles = Employee::where(function ($q) {
            $q->whereNull('nik')->orWhereNull('hp')->orWhereNull('tanggal_lahir');
        })->count();

        $perUnit = Employee::with('workUnit')
            ->get()
            ->groupBy(fn ($e) => $e->workUnit?->name ?? 'Tanpa Unit')
            ->map->count()
            ->sortDesc()
            ->take(6);

        // Komposisi pegawai untuk visualisasi ringkas
        $perStatus = Employee::with('employmentStatus')
            ->get()
            ->groupBy(fn ($e) => $e->employmentStatus?->name ?? 'Tanpa Status')
            ->map->count()
            ->sortDesc();

        $perGolongan = Employee::with('golonganAkhir')
            ->get()
            ->groupBy(fn ($e) => $e->golonganAkhir?->golongan ?? 'Tanpa Golongan')
            ->map->count()
            ->sortDesc()
            ->take(6);

        $perPendidikan = Employee::with('pendidikanAkhir')
            ->get()
            ->groupBy(fn ($e) => $e->pendidikanAkhir?->name ?? 'Tanpa Data')
            ->map->count()
            ->sortDesc()
            ->take(6);

        $perJenis = Employee::with('employeeCategory')
            ->get()
            ->groupBy(fn ($e) => $e->employeeCategory?->name ?? 'Tanpa Kategori')
            ->map->count()
            ->sortDesc()
            ->take(6);

        $perGender = [
            'Laki-laki' => Employee::where('jenis_kelamin', 'L')->count(),
            'Perempuan' => Employee::where('jenis_kelamin', 'P')->count(),
            'Belum Isi' => Employee::whereNull('jenis_kelamin')->count(),
        ];

        $recentMutations = EmployeeMutation::with('employee', 'unitTujuan')
            ->orderByDesc('tanggal_mutasi')
            ->limit(6)
            ->get();
        $recentTrainings = EmployeeTraining::with('employee')
            ->latest()
            ->limit(6)
            ->get();

        // Sertifikasi masa berlakunya akan segera habis / sudah habis
        $expiredCerts = EmployeeTraining::with('employee')
            ->where('masa_berlaku', '<', now())
            ->orderBy('masa_berlaku')
            ->limit(5)
            ->get();
        $expiringCerts = EmployeeTraining::with('employee')
            ->whereBetween('masa_berlaku', [now(), now()->addDays(120)])
            ->orderBy('masa_berlaku')
            ->limit(5)
            ->get();
        $sertifAkts = [
            'expired' => EmployeeTraining::where('masa_berlaku', '<', now())->count(),
            'expiring' => EmployeeTraining::whereBetween('masa_berlaku', [now(), now()->addDays(120)])->count(),
        ];

        $recentRequests = ChangeRequest::with('employee')->latest()->limit(6)->get();
        $recentLogs = AuditLog::latest()->limit(8)->get();
        $unreadCount = $user->appNotifications()->unread()->count();

        return view('dashboard', [
            'totals' => $totals,
            'pendingApprovals' => $pendingApprovals,
            'unverifiedDocuments' => $unverifiedDocuments,
            'incompleteProfiles' => $incompleteProfiles,
            'perUnit' => $perUnit,
            'perStatus' => $perStatus,
            'perGolongan' => $perGolongan,
            'perPendidikan' => $perPendidikan,
            'perJenis' => $perJenis,
            'perGender' => $perGender,
            'recentMutations' => $recentMutations,
            'recentTrainings' => $recentTrainings,
            'expiredCerts' => $expiredCerts,
            'expiringCerts' => $expiringCerts,
            'sertifAkts' => $sertifAkts,
            'recentRequests' => $recentRequests,
            'recentLogs' => $recentLogs,
            'role' => 'admin',
            'unreadCount' => $unreadCount,
        ]);
    }
}
