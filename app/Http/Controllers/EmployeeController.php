<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use App\Models\Rank;
use App\Models\WorkUnit;
use App\Models\EducationLevel;
use App\Models\EmployeeCategory;
use App\Models\EmploymentStatus;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Modul 3 — Manajemen Pegawai: daftar semua pegawai.
     */
    public function index(Request $request): View
    {
        $query = Employee::with(['currentPosition', 'workUnit', 'employmentStatus', 'golonganAkhir'])->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nip_lama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhereHas('currentPosition', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('workUnit', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($unit = $request->get('unit_id')) {
            $query->where('work_unit_id', $unit);
        }

        if ($pos = $request->get('position_id')) {
            $query->where('current_position_id', $pos);
        }

        if ($rank = $request->get('rank_id')) {
            $query->where('golongan_akhir_id', $rank);
        }

        // Filter cepat: semua / aktif / nonaktif / baru
        if ($filter = $request->get('status')) {
            if ($filter === 'aktif') {
                $query->whereRelation('employmentStatus', 'name', 'Aktif');
            } elseif ($filter === 'nonaktif') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('employmentStatus')
                      ->orWhereRelation('employmentStatus', 'name', '!=', 'Aktif');
                });
            } elseif ($filter === 'baru') {
                $query->where('created_at', '>=', now()->subDays(30));
            }
        }

        $perPage = in_array((int) $request->get('per_page', 15), [10, 15, 25, 50], true)
            ? (int) $request->get('per_page')
            : 15;

        $employees = $query->paginate($perPage)->withQueryString();

        $totals = [
            'semua' => Employee::count(),
            'aktif' => Employee::whereRelation('employmentStatus', 'name', 'Aktif')->count(),
            'baru' => Employee::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        $workUnits = WorkUnit::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('name')->get(['id', 'name']);
        $ranks = Rank::orderBy('urutan')->get(['id', 'golongan', 'pangkat']);

        return view('employees.index', compact('employees', 'totals', 'workUnits', 'positions', 'ranks'));
    }

    public function searchJson(Request $request): \Illuminate\Http\JsonResponse
    {
        $term = trim($request->get('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }
        $rows = Employee::with(['currentPosition', 'workUnit'])
            ->where(function ($query) use ($term) {
                $query->where('nama_lengkap', 'like', "%{$term}%")
                  ->orWhere('nip', 'like', "%{$term}%")
                  ->orWhere('nip_lama', 'like', "%{$term}%");
            })
            ->limit(8)
            ->get(['id', 'nama_lengkap', 'nip'])
            ->map(fn ($e) => [
                'id' => $e->id,
                'nama' => $e->nama_lengkap,
                'nip' => $e->nip,
                'jabatan' => $e->currentPosition?->name,
                'unit' => $e->workUnit?->name,
                'url' => route('employees.show', $e),
            ]);

        return response()->json($rows);
    }

    /**
     * Ekspor daftar pegawai ke CSV dengan filter yang sama seperti daftar.
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Employee::with(['currentPosition', 'workUnit', 'employmentStatus', 'golonganAkhir']);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($unit = $request->get('unit_id')) {
            $query->where('work_unit_id', $unit);
        }

        if ($pos = $request->get('position_id')) {
            $query->where('current_position_id', $pos);
        }

        if ($rank = $request->get('rank_id')) {
            $query->where('golongan_akhir_id', $rank);
        }

        if ($filter = $request->get('status')) {
            if ($filter === 'aktif') {
                $query->whereRelation('employmentStatus', 'name', 'Aktif');
            } elseif ($filter === 'nonaktif') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('employmentStatus')
                      ->orWhereRelation('employmentStatus', 'name', '!=', 'Aktif');
                });
            } elseif ($filter === 'baru') {
                $query->where('created_at', '>=', now()->subDays(30)); 
            }
        }

        $employees = $query->get();

        $filename = 'pegawai-' . now()->format('Ymd-Hi') . '.csv';

        return response()->streamDownload(function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'NIP', 'Nama Lengkap', 'Jabatan', 'Unit Kerja',
                'Golongan', 'Status', 'Jenis Kelamin', 'Tgl Lahir',
            ]);

            foreach ($employees as $e) {
                fputcsv($handle, [
                    $e->nip,
                    $e->nama_lengkap_dengan_gelar,
                    $e->currentPosition?->name ?? '',
                    $e->workUnit?->name ?? '',
                    $e->golonganAkhir?->golongan ?? '',
                    $e->employmentStatus?->name ?? '',
                    $e->jenis_kelamin === 'L' ? 'Laki-laki' : ($e->jenis_kelamin === 'P' ? 'Perempuan' : ''),
                    $e->tanggal_lahir?->format('d-m-Y') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Modul 5 — Profil Pegawai lengkap dengan tab.
     */
    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);

        $employee->load([
            'currentPosition', 'workUnit', 'employmentStatus', 'employeeCategory',
            'golonganAwal', 'golonganAkhir', 'pendidikanAkhir',
            'educations.educationLevel',
            'positionHistories.position', 'positionHistories.workUnit',
            'rankHistories.rank',
            'mutations.unitAsal', 'mutations.unitTujuan',
            'trainings', 'awards', 'disciplines', 'performances', 'families', 'documents',
            'user', 'changeRequests',
        ]);

        $employee->load([
            'auditLogs' => fn ($q) => $q->latest('created_at'),
        ]);

        return view('employees.show', compact('employee'));
    }

    /**
     * Form Edit Profil Pegawai — diisi Admin/Super Admin.
     */
    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        $employee->load([
            'workUnit', 'currentPosition', 'golonganAwal', 'golonganAkhir',
            'employeeCategory', 'employmentStatus', 'pendidikanAwal', 'pendidikanAkhir',
        ]);

        $workUnits = WorkUnit::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $ranks = Rank::orderBy('urutan')->get();
        $educationLevels = EducationLevel::orderBy('urutan')->get();
        $employeeCategories = EmployeeCategory::orderBy('name')->get();
        $employmentStatuses = EmploymentStatus::orderBy('name')->get();

        return view('employees.edit', compact(
            'employee', 'workUnits', 'positions', 'ranks',
            'educationLevels', 'employeeCategories', 'employmentStatuses'
        ));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate($this->validationRules());

        $data['kepemilikan_kpe'] = $request->boolean('kepemilikan_kpe');
        $data['izin_pemakaian_gelar'] = $request->boolean('izin_pemakaian_gelar');
        $data['data_updated_at'] = now();
        $data['is_draft'] = $request->boolean('is_draft') ? true : false;

        $employee->update($data);

        AuditLog::record(
            action: 'update',
            module: 'Pegawai',
            reference: $employee,
            description: "Admin memperbarui data pegawai {$employee->nama_lengkap} ({$employee->nip}).",
        );

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', $data['is_draft']
                ? 'Draft data pegawai berhasil disimpan. Lanjutkan melengkapi kapan saja.'
                : 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Simpan draft (data belum lengkap) dari wizard tambah pegawai.
     */
    protected function storeDraft(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:64'],
        ]);

        $data['kepemilikan_kpe'] = $request->boolean('kepemilikan_kpe');
        $data['izin_pemakaian_gelar'] = $request->boolean('izin_pemakaian_gelar');
        $data['is_draft'] = true;

        if (! empty($data['nama_lengkap'])) {
            $data['nama_lengkap'] = trim($data['nama_lengkap']);
        }

        $employee = Employee::create(array_filter($data, fn ($v) => filled($v) || is_bool($v)));

        AuditLog::record(
            action: 'create',
            module: 'Pegawai',
            reference: $employee,
            description: "Admin menyimpan draft pegawai " . ($employee->nama_lengkap ?: '(tanpa nama)') . '.',
        );

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Draft tersimpan. Lanjutkan melengkapi data kapan saja.');
    }

    /**
     * Wizard Tambah Pegawai (3 langkah: Identitas → Kontak & Alamat → Akun).
     */
    public function create(): View
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        if ($request->boolean('is_draft')) {
            return $this->storeDraft($request);
        }

        $data = $request->validate(
            array_merge($this->validationRules(), [
                'nip' => [
                    'required', 'string', 'max:64',
                    'unique:employees,nip',
                    'unique:users,nip',
                ],
                'nik' => ['nullable', 'string', 'max:255', 'unique:employees,nik'],
            ]),
            [
                'nip.required' => 'NIP wajib diisi.',
                'nip.unique' => 'NIP sudah terdaftar atas nama pegawai lain. Mohon periksa kembali data NIP — setiap NIP tidak boleh sama.',
                'nik.unique' => 'Nomor NIK sudah terdaftar. NIK tidak mungkin sama dengan pegawai lain, mohon periksa kembali.',
            ]
        );

        $data['kepemilikan_kpe'] = false;
        $data['izin_pemakaian_gelar'] = false;
        $data['data_updated_at'] = now();
        $data['is_draft'] = false;

        // Role akun: hanya Super Admin yang boleh menentukan Admin/User.
        $requestedRole = $request->input('role');
        if ($request->user()?->isSuperAdmin() && in_array($requestedRole, ['admin', 'user'], true)) {
            $role = $requestedRole;
        } else {
            $role = 'user';
        }

        try {
            $employee = Employee::create($data);

            // Akun login dibuat otomatis: username = NIP, password bawaan.
            $user = User::create([
                'nip' => $data['nip'],
                'name' => $data['nama_lengkap'],
                'email' => $data['email_resmi'] ?? ($data['email_pribadi'] ?? null),
                'role' => $role,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);

            $employee->update(['user_id' => $user->id]);
        } catch (\Illuminate\Database\QueryException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'nip' => 'Data tidak dapat disimpan karena sudah ada data yang sama di sistem (NIP/NIK). Mohon periksa kembali dan gunakan data yang berbeda.',
                ]);
        }

        $roleLabel = $role === 'admin' ? 'Admin' : 'User (Pegawai)';

        AuditLog::record(
            action: 'create',
            module: 'Pegawai',
            reference: $employee,
            description: "Admin menambah pegawai baru {$employee->nama_lengkap} ({$employee->nip}) beserta akun login otomatis (role {$roleLabel}).",
        );

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', "Pegawai baru berhasil ditambahkan. Akun login dibuat otomatis (username: {$employee->nip}, password bawaan: password123, role: {$roleLabel}).");
    }

    /**
     * Upload foto pegawai (oleh Admin/Super Admin).
     */
    public function updatePhoto(Request $request, Employee $employee): RedirectResponse
    {
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
            module: 'Pegawai',
            reference: $employee,
            description: "Admin memperbarui foto {$employee->nama_lengkap}.",
        );

        return redirect()->route('employees.show', $employee)->with('success', 'Foto pegawai berhasil diperbarui.');
    }

    protected function validationRules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'gelar_depan' => ['nullable', 'string', 'max:255'],
            'gelar_belakang' => ['nullable', 'string', 'max:255'],
            'nama_panggilan' => ['nullable', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:255'],
            'no_kk' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Khonghucu,Lainnya'],
            'status_perkawinan' => ['nullable', 'string', 'max:255'],
            'golongan_darah' => ['nullable', 'string', 'max:5'],
            'kepemilikan_kpe' => ['nullable', 'boolean'],
            'jenis_asn' => ['nullable', 'in:PNS,PPPK'],
            'status_calon' => ['nullable', 'string', 'max:255'],
            'kedudukan_pegawai' => ['nullable', 'string', 'max:255'],

            // Identitas administratif
            'no_npwp' => ['nullable', 'string', 'max:255'],
            'no_bpjs' => ['nullable', 'string', 'max:255'],
            'no_karpeg' => ['nullable', 'string', 'max:255'],
            'no_karis_karsu' => ['nullable', 'string', 'max:255'],
            'no_rekening' => ['nullable', 'string', 'max:255'],
            'bank' => ['nullable', 'string', 'max:255'],
            'no_taspen' => ['nullable', 'string', 'max:255'],
            'bapertarum' => ['nullable', 'in:Sudah Diambil,Belum Diambil,Tidak Ada'],

            // Status kepegawaian
            'status_pegawai' => ['nullable', 'in:PNS,PPPK,Honorer,Kontrak,Lainnya'],
            'employee_category_id' => ['nullable', 'exists:employee_categories,id'],
            'employment_status_id' => ['nullable', 'exists:employment_statuses,id'],

            // Pendidikan ringkas
            'pendidikan_awal_id' => ['nullable', 'exists:education_levels,id'],
            'tahun_pendidikan_awal' => ['nullable', 'digits:4'],
            'pendidikan_akhir_id' => ['nullable', 'exists:education_levels,id'],
            'tahun_pendidikan_akhir' => ['nullable', 'digits:4'],
            'izin_pemakaian_gelar' => ['nullable', 'boolean'],

            // Jabatan & organisasi
            'jenis_jabatan' => ['nullable', 'in:struktural,fungsional,pelaksana'],
            'eselon' => ['nullable', 'string', 'max:255'],
            'tmt_eselon' => ['nullable', 'date'],
            'current_position_id' => ['nullable', 'exists:positions,id'],
            'tmt_jabatan' => ['nullable', 'date'],
            'tugas_tambahan_1' => ['nullable', 'string', 'max:255'],
            'tmt_tugas_tambahan_1' => ['nullable', 'date'],
            'tugas_tambahan_2' => ['nullable', 'string', 'max:255'],
            'tmt_tugas_tambahan_2' => ['nullable', 'date'],
            'work_unit_id' => ['nullable', 'exists:work_units,id'],
            'tmt_skpd' => ['nullable', 'date'],
            'instansi_dipekerjakan' => ['nullable', 'string', 'max:255'],

            // Pangkat, golongan & gaji
            'golongan_awal_id' => ['nullable', 'exists:ranks,id'],
            'tmt_golongan_awal' => ['nullable', 'date'],
            'golongan_akhir_id' => ['nullable', 'exists:ranks,id'],
            'tmt_golongan_akhir' => ['nullable', 'date'],
            'masa_kerja_tahun' => ['nullable', 'integer', 'min:0', 'max:70'],
            'masa_kerja_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
            'gaji_pokok' => ['nullable', 'numeric'],
            'tmt_gaji_berkala_terbaru' => ['nullable', 'date'],

            // Alamat rumah
            'alamat_rumah' => ['nullable', 'string'],
            'rt_rumah' => ['nullable', 'string', 'max:5'],
            'rw_rumah' => ['nullable', 'string', 'max:5'],
            'kelurahan_rumah' => ['nullable', 'string', 'max:255'],
            'kecamatan_rumah' => ['nullable', 'string', 'max:255'],
            'kabkota_rumah' => ['nullable', 'string', 'max:255'],
            'provinsi_rumah' => ['nullable', 'string', 'max:255'],
            'kodepos_rumah' => ['nullable', 'string', 'max:10'],

            // Alamat domisili (KTP)
            'alamat_domisili_ktp' => ['nullable', 'string'],
            'rt_domisili' => ['nullable', 'string', 'max:5'],
            'rw_domisili' => ['nullable', 'string', 'max:5'],
            'kelurahan_domisili' => ['nullable', 'string', 'max:255'],
            'kecamatan_domisili' => ['nullable', 'string', 'max:255'],
            'kabkota_domisili' => ['nullable', 'string', 'max:255'],
            'provinsi_domisili' => ['nullable', 'string', 'max:255'],
            'kodepos_domisili' => ['nullable', 'string', 'max:10'],

            // Kontak
            'telp' => ['nullable', 'string', 'max:255'],
            'hp' => ['nullable', 'string', 'max:255'],
            'email_pribadi' => ['nullable', 'email'],
            'email_resmi' => ['nullable', 'email'],
        ];
    }
}