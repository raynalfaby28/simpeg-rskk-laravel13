<?php

namespace App\Http\Controllers;

use App\Models\{
    Document, Employee, EmployeeCategory, EmployeePerformance, EmployeeTraining, EmploymentStatus, Position, Rank, WorkUnit,
};
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $section = $request->get('section', 'pegawai');
        $unitId = $request->get('unit');
        $q = $request->get('q');

        $employees = Employee::with(['currentPosition', 'workUnit', 'golonganAkhir', 'employmentStatus'])
            ->when($unitId, fn ($qq) => $qq->where('work_unit_id', $unitId))
            ->when($q, fn ($qq) => $qq->where(fn ($w) => $w->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%")));

        $perUnit = Employee::with('workUnit')->get()->groupBy(fn ($e) => $e->workUnit?->name ?? 'Tanpa Unit')->map->count()->sortDesc();
        $perPosition = Employee::with('currentPosition')->get()->groupBy(fn ($e) => $e->currentPosition?->name ?? 'Belum Diisi')->map->count()->sortDesc();
        $perRank = Employee::with('golonganAkhir')->get()->groupBy(fn ($e) => trim(($e->golonganAkhir?->golongan ?? '—') . ' ' . ($e->golonganAkhir?->pangkat ?? '')))->map->count()->sortDesc();
        $perEducation = Employee::with('pendidikanAkhir')->get()->groupBy(fn ($e) => $e->pendidikanAkhir?->name ?? 'Belum Diisi')->map->count()->sortDesc();
        $perStatus = Employee::get()->groupBy(fn ($e) => $e->status_pegawai ?: 'Lainnya')->map->count()->sortDesc();
        $perCategory = Employee::with('employeeCategory')->get()->groupBy(fn ($e) => $e->employeeCategory?->name ?? 'Belum Diisi')->map->count()->sortDesc();
        $documents = Document::with(['employee'])
            ->when($q, fn ($qq) => $qq->whereHas('employee', fn ($w) => $w->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%")))
            ->limit(500)->get();

        $trainings = EmployeeTraining::with('employee')
            ->when($q, fn ($qq) => $qq->where(fn ($w) => $w->where('nama_pelatihan', 'like', "%{$q}%")
                ->orWhereHas('employee', fn ($e) => $e->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%"))))
            ->orderByDesc('tanggal_selesai')
            ->limit(500)->get();

        $performances = EmployeePerformance::with('employee')
            ->when($q, fn ($qq) => $qq->whereHas('employee', fn ($e) => $e->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%")))
            ->orderByDesc('tahun')
            ->latest()
            ->limit(500)->get();

        $sertifSummary = [
            'expired' => EmployeeTraining::where('masa_berlaku', '<', now())->count(),
            'expiring' => EmployeeTraining::whereBetween('masa_berlaku', [now(), now()->addDays(120)])->count(),
            'valid' => EmployeeTraining::where('masa_berlaku', '>', now()->addDays(120))->count(),
            'tanpa_berlaku' => EmployeeTraining::whereNull('masa_berlaku')->count(),
        ];

        $kinerjaStats = EmployeePerformance::with('employee')->get()
            ->groupBy(fn ($p) => $p->predikat ?: 'Tanpa Predikat')
            ->map->count()->sortDesc();

        $entryCount = match ($section) {
            'dokumen' => $documents->count(),
            'diklat' => $trainings->count(),
            'kinerja' => $performances->count(),
            'pegawai' => $employees->count(),
            default => $perUnit->sum(),
        };

        return view('reports.index', [
            'section' => $section,
            'units' => WorkUnit::orderBy('name')->pluck('name', 'id'),
            'employees' => $employees->orderBy('nama_lengkap')->limit(500)->get(),
            'perUnit' => $perUnit,
            'perPosition' => $perPosition,
            'perRank' => $perRank,
            'perEducation' => $perEducation,
            'perStatus' => $perStatus,
            'perCategory' => $perCategory,
            'documents' => $documents,
            'trainings' => $trainings,
            'performances' => $performances,
            'sertifSummary' => $sertifSummary,
            'kinerjaStats' => $kinerjaStats,
            'unitId' => $unitId,
            'q' => $q,
            'entryCount' => $entryCount,
            'counts' => [
                'pegawai' => Employee::count(),
                'aktif' => Employee::whereRelation('employmentStatus', 'name', 'Aktif')->count(),
                'unit' => WorkUnit::count(),
                'dokumen' => Document::count(),
            ],
        ]);
    }

    public function csv(Request $request, string $jenis)
    {
        $canSeeGaji = auth()->user()->role === 'super_admin';
        $rows = match ($jenis) {
            'unit' => $this->simpleRows(Employee::with('workUnit')->get(), fn ($e) => [$e->workUnit?->name ?? 'Tanpa Unit']),
            'jabatan' => $this->simpleRows(Employee::with('currentPosition')->get(), fn ($e) => [$e->currentPosition?->name ?? 'Belum Diisi']),
            'golongan' => $this->simpleRows(Employee::with('golonganAkhir')->get(), fn ($e) => [trim(($e->golonganAkhir?->golongan ?? '—') . ' ' . ($e->golonganAkhir?->pangkat ?? ''))]),
            'pendidikan' => $this->simpleRows(Employee::with('pendidikanAkhir')->get(), fn ($e) => [$e->pendidikanAkhir?->name ?? 'Belum Diisi']),
            'status' => $this->simpleRows(Employee::get(), fn ($e) => [$e->status_pegawai ?: 'Lainnya']),
            'dokumen' => Document::with('employee')->limit(2000)->get()->map(fn ($d) => [
                'nip' => $d->employee?->nip ?? '', 'nama' => $d->employee?->nama_lengkap ?? '',
                'jenis' => $d->jenis_dokumen, 'nomor' => $d->no_dokumen ?? '',
                'tanggal' => optional($d->tanggal)->format('d-m-Y') ?: '',
                'status' => str_replace('_', ' ', $d->status_verifikasi),
            ])->all(),
            'diklat' => EmployeeTraining::with('employee')->limit(2000)->get()->map(fn ($t) => [
                'nip' => $t->employee?->nip ?? '', 'nama' => $t->employee?->nama_lengkap ?? '',
                'pelatihan' => $t->nama_pelatihan, 'jenis' => $t->jenis_pelatihan ?? '',
                'mulai' => optional($t->tanggal_mulai)->format('d-m-Y') ?: '',
                'selesai' => optional($t->tanggal_selesai)->format('d-m-Y') ?: '',
                'masa_berlaku' => optional($t->masa_berlaku)->format('d-m-Y') ?: '',
                'no_sertifikat' => $t->no_sertifikat ?? '',
            ])->all(),
            'kinerja' => EmployeePerformance::with('employee')->limit(2000)->get()->map(fn ($p) => [
                'nip' => $p->employee?->nip ?? '', 'nama' => $p->employee?->nama_lengkap ?? '',
                'tahun' => $p->tahun, 'periode' => $p->periode ?? '',
                'nilai' => $p->nilai, 'predikat' => $p->predikat ?? '',
            ])->all(),
            default => Employee::with(['currentPosition', 'workUnit', 'golonganAkhir', 'employmentStatus'])
                ->orderBy('nama_lengkap')->limit(5000)->get()->map(fn ($e) => [
                    'nip' => $e->nip, 'nama' => $e->nama_lengkap,
                    'jabatan' => $e->currentPosition?->name ?? '', 'unit' => $e->workUnit?->name ?? '',
                    'golongan' => trim(($e->golonganAkhir?->golongan ?? '') . ' ' . ($e->golonganAkhir?->pangkat ?? '')),
                    'status' => $e->status_pegawai ?: ($e->employmentStatus?->name ?? ''),
                    'gaji' => $e->gaji_pokok ? ($canSeeGaji ? $e->gaji_pokok : '••••') : '',
                    'hp' => $e->hp ?? '', 'email' => $e->email_resmi ?? '',
                ])->all(),
        };

        return response()->streamDownload(function () use ($jenis, $rows) {
            $headers = match ($jenis) {
                'unit' => ['Unit Kerja', 'Jumlah Pegawai'],
                'jabatan' => ['Jabatan', 'Jumlah Pegawai'],
                'golongan' => ['Golongan', 'Jumlah Pegawai'],
                'pendidikan' => ['Pendidikan Terakhir', 'Jumlah Pegawai'],
                'status' => ['Status Pegawai', 'Jumlah Pegawai'],
                'dokumen' => ['NIP', 'Nama', 'Jenis Dokumen', 'Nomor Dokumen', 'Tanggal', 'Status Verifikasi'],
                'diklat' => ['NIP', 'Nama', 'Nama Pelatihan', 'Jenis Pelatihan', 'Mulai', 'Selesai', 'Masa Berlaku', 'No. Sertifikat'],
                'kinerja' => ['NIP', 'Nama', 'Tahun', 'Periode', 'Nilai', 'Predikat'],
                default => ['NIP', 'Nama Lengkap', 'Jabatan', 'Unit Kerja', 'Golongan', 'Status', 'Gaji Pokok', 'No. HP', 'Email Resmi'],
            };
            $out = fopen('php://output', 'w');
            // BOM UTF-8 + penanda separator agar Excel (Indonesia) membagi kolom dengan benar
            fwrite($out, "\xEF\xBB\xBF" . "sep=;\r\n");
            $eol = "\r\n";
            fputcsv($out, $headers, ';', '"', '', $eol);
            foreach ($rows as $row) {
                fputcsv($out, array_values($row), ';', '"', '', $eol);
            }
            fclose($out);
        }, "laporan-{$jenis}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function simpleRows($employees, callable $mapper): array
    {
        return $employees->groupBy(fn ($e) => $mapper($e)[0])->map->count()
            ->sortDesc()->map(fn ($c, $label) => [$label, $c])->values()->all();
    }
}