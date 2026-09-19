<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public const KATEGORI = [
        'KTP', 'KK', 'NPWP', 'BPJS', 'KARPEG', 'KARIS/KARSU', 'KPE',
        'Ijazah', 'Transkrip',
        'SK Pengangkatan', 'SK PNS/PPPK', 'SK Pangkat', 'SK Jabatan', 'SK Mutasi', 'SK Gaji',
        'Surat Tugas', 'Diklat', 'Sertifikat', 'STR', 'Penghargaan',
        'Keterangan', 'Dokumen Pendukung',
    ];

    /** Kelompok arsip (label tab Arsip & Dokumen Digital). */
    public const KATEGORI_GRUP = [
        'pribadi' => 'Dokumen Pribadi',
        'akademik' => 'Dokumen Akademik',
        'kepegawaian' => 'Dokumen Kepegawaian',
        'diklat' => 'Diklat & Sertifikat',
        'cuti' => 'Cuti',
        'mutasi' => 'SK Mutasi',
        'penghargaan' => 'Penghargaan',
        'lainnya' => 'Lainnya',
    ];

    /**
     * Daftar dokumen: user melihat miliknya sendiri, admin melihat semua.
     */
    public function index(Request $request): View
    {
        $admin = $request->user()->role !== 'user';

        $query = Document::with(['employee'])
            ->when($admin, function ($q) use ($request) {
                if ($filter = $request->get('status')) {
                    $q->where('status_verifikasi', $filter);
                }
                if ($kategori = $request->get('kategori')) {
                    $q->where('jenis_dokumen', $kategori);
                }
                if ($search = $request->get('q')) {
                    $q->where(fn ($w) => $w->where('jenis_dokumen', 'like', "%{$search}%")
                        ->orWhere('no_dokumen', 'like', "%{$search}%"))
                        ->orWhereHas('employee', fn ($eq) => $eq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%"));
                }
            })
            ->when(! $admin, fn ($q) => $q->where('employee_id', $request->user()->employee?->id))
            ->latest();

        $documents = $query->paginate(15)->withQueryString();

        $totals = [
            'semua' => Document::count(),
            'belum_diverifikasi' => Document::where('status_verifikasi', 'belum_diverifikasi')->count(),
            'terverifikasi' => Document::where('status_verifikasi', 'terverifikasi')->count(),
            'ditolak' => Document::where('status_verifikasi', 'ditolak')->count(),
            'draft' => 0,
        ];

        $kategoriList = collect(self::KATEGORI)->map(fn ($k) => [
            'name' => $k,
            'total' => Document::where('jenis_dokumen', $k)->count(),
        ])->filter(fn ($k) => $k['total'] > 0)->values();

        return view('documents.index', compact('documents', 'totals', 'admin', 'kategoriList'));
    }

    public function create(Request $request, ?Employee $employee = null): View
    {
        $admin = $request->user()->role !== 'user';

        if (! $admin && ! $employee) {
            $employee = $request->user()->employee;
        }

        $employees = $admin ? Employee::orderBy('nama_lengkap')->get() : null;

        return view('documents.create', compact('employee', 'admin', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = $request->user()->role !== 'user';

        $data = $request->validate([
            'employee_id' => [$admin ? 'required' : 'nullable', 'exists:employees,id'],
            'jenis_dokumen' => ['required', 'in:'.implode(',', self::KATEGORI)],
            'kategori' => ['nullable', 'in:'.implode(',', array_keys(self::KATEGORI_GRUP))],
            'no_dokumen' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $employee = $admin
            ? Employee::findOrFail($data['employee_id'])
            : $request->user()->employee;

        abort_if(! $employee, 403, 'Akun Anda belum terhubung ke data pegawai.');

        $filePath = $request->file('file')->store('documents', 'public');

        $document = Document::create([
            'employee_id' => $employee->id,
            'jenis_dokumen' => $data['jenis_dokumen'],
            'kategori' => $data['kategori'] ?? 'lainnya',
            'no_dokumen' => $data['no_dokumen'] ?? null,
            'tanggal' => $data['tanggal'] ?? null,
            'file_path' => $filePath,
            'keterangan' => $data['keterangan'] ?? null,
            'status_verifikasi' => $admin ? 'terverifikasi' : 'belum_diverifikasi',
        ]);

        AuditLog::record(
            action: 'create',
            module: 'Dokumen',
            reference: $document,
            description: auth()->user()->role === 'user'
                ? "Pegawai {$employee->nama_lengkap} mengunggah dokumen {$document->jenis_dokumen}."
                : "Admin mengunggah dokumen {$document->jenis_dokumen} untuk {$employee->nama_lengkap}.",
        );

        if (! $admin) {
            $admins = User::whereIn('role', ['super_admin', 'admin'])->pluck('id');
            foreach ($admins as $adminId) {
                Notification::send(
                    userId: $adminId,
                    title: 'Dokumen baru menunggu verifikasi',
                    body: $employee->nama_lengkap . ' mengunggah ' . $document->jenis_dokumen,
                    url: route('documents.index'),
                );
            }
        }

        return redirect()
            ->route('documents.index')
            ->with('success', $admin
                ? 'Dokumen berhasil diunggah dan langsung diverifikasi.'
                : 'Dokumen berhasil diunggah, menunggu verifikasi admin.');
    }

    public function verify(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeAdmin($request);

        if ($document->status_verifikasi === 'terverifikasi') {
            return back()->with('error', 'Dokumen ini sudah terverifikasi.');
        }

        $document->update([
            'status_verifikasi' => 'terverifikasi',
            'keterangan' => null,
        ]);

        AuditLog::record(action: 'update', module: 'Dokumen', reference: $document,
            description: "Dokumen {$document->jenis_dokumen} milik {$document->employee->nama_lengkap} diverifikasi.");

        if ($user = $document->employee?->user) {
            Notification::send(
                userId: $user->id,
                title: 'Dokumen Anda terverifikasi',
                body: $document->jenis_dokumen . ' telah diverifikasi dan diterima.',
                url: route('documents.index'),
            );
        }

        return back()->with('success', 'Dokumen berhasil diverifikasi.');
    }

    public function reject(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'keterangan' => ['required', 'string', 'max:500'],
        ]);

        $document->update([
            'status_verifikasi' => 'ditolak',
            'keterangan' => $data['keterangan'],
        ]);

        AuditLog::record(action: 'reject', module: 'Dokumen', reference: $document,
            description: "Dokumen {$document->jenis_dokumen} milik {$document->employee->nama_lengkap} ditolak.");

        if ($user = $document->employee?->user) {
            Notification::send(
                userId: $user->id,
                title: 'Dokumen Anda perlu diperbaiki',
                body: $document->jenis_dokumen . ' ditolak. Alasan: ' . $data['keterangan'],
                url: route('documents.index'),
            );
        }

        return back()->with('success', 'Dokumen ditolak, alasan sudah dikirim ke pegawai.');
    }

    public function edit(Request $request, Document $document): View
    {
        $this->authorizeAdmin($request);

        $admin = true;
        $employee = $document->employee;
        $employees = null;

        return view('documents.edit', compact('document', 'employee', 'admin', 'employees'));
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'jenis_dokumen' => ['required', 'in:'.implode(',', self::KATEGORI)],
            'kategori' => ['nullable', 'in:'.implode(',', array_keys(self::KATEGORI_GRUP))],
            'no_dokumen' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $filePath = $document->file_path;
        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($document->file_path);
            $filePath = $request->file('file')->store('documents', 'public');
        }

        $document->update([
            'jenis_dokumen' => $data['jenis_dokumen'],
            'kategori' => $data['kategori'] ?? $document->kategori ?? 'lainnya',
            'no_dokumen' => $data['no_dokumen'] ?? null,
            'tanggal' => $data['tanggal'] ?? null,
            'file_path' => $filePath,
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        AuditLog::record(action: 'update', module: 'Dokumen', reference: $document,
            description: "Dokumen {$document->jenis_dokumen} milik {$document->employee->nama_lengkap} diperbarui.");

        return redirect()
            ->route('documents.index')
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function download(Request $request, Document $document): mixed
    {
        $user = $request->user();

        if ($user->role === 'user' && $document->employee_id !== $user->employee?->id) {
            abort(403, 'Anda hanya bisa mengunduh dokumen milik sendiri.');
        }

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        $ext = pathinfo($document->file_path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download(
            $document->file_path,
            'Dokumen-' . str_replace(' ', '-', $document->jenis_dokumen) . '.' . $ext
        );
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $user = $request->user();

        if ($user->role === 'user' && $document->employee_id !== $user->employee?->id) {
            abort(403, 'Anda hanya bisa menghapus dokumen milik sendiri.');
        }

        AuditLog::record(action: 'delete', module: 'Dokumen', reference: $document,
            description: "Dokumen {$document->jenis_dokumen} milik {$document->employee->nama_lengkap} dihapus.");

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->role !== 'user', 403, 'Hanya Admin/Super Admin yang bisa memverifikasi.');
    }
}