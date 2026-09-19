<?php

namespace App\Http\Controllers;

use App\Models\AwardType;
use App\Models\AssetType;
use App\Models\DiklatType;
use App\Models\DocumentType;
use App\Models\EducationLevel;
use App\Models\EducationType;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\EmployeeType;
use App\Models\EmploymentStatus;
use App\Models\MasterItem;
use App\Models\MasterType;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\Rank;
use App\Models\SubUnit;
use App\Models\WorkUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(string $type): View
    {
        $conf = $this->conf($type);
        $model = $conf['model'];

        $query = $model::query();
        if (isset($conf['master_type_id'])) {
            $query->where('master_type_id', $conf['master_type_id']);
        }
        if ($type === 'units') {
            $query->with('parent');
        }
        $rows = $query->orderBy($conf['order'])->get();

        $options = [];
        if ($type === 'units') {
            $options['parent_id'] = WorkUnit::orderBy('name')->pluck('name', 'id');
        }

        $navTypes = array_map(fn ($c) => $c['label'], $this->types());
        $types = $this->types();

        $typeCounts = [];
        foreach ($types as $key => $c) {
            $typeCounts[$key] = isset($c['master_type_id'])
                ? MasterItem::where('master_type_id', $c['master_type_id'])->count()
                : $c['model']::count();
        }

        return view('master.index', compact('conf', 'rows', 'options', 'type', 'navTypes', 'types', 'typeCounts'));
    }

    public function create(string $type): View
    {
        $conf = $this->conf($type);

        $options = [];
        if ($type === 'units') {
            $options['parent_id'] = WorkUnit::orderBy('name')->pluck('name', 'id');
        }

        return view('master.form', compact('conf', 'options', 'type'));
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $conf = $this->conf($type);

        $data = $request->validate($this->rules($conf));

        if (isset($conf['master_type_id'])) {
            $data['master_type_id'] = $conf['master_type_id'];
        }

        if (collect($conf['fields'])->contains(fn ($f) => ($f['type'] ?? '') === 'boolean')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $conf['model']::create($data);

        return redirect()
            ->route('master.index', $type)
            ->with('success', "Master {$conf['label']} berhasil ditambahkan.");
    }

    public function edit(string $type, int $id): View
    {
        $conf = $this->conf($type);
        $row = $this->findRow($conf, $id);

        $options = [];
        if ($type === 'units') {
            $options['parent_id'] = WorkUnit::where('id', '!=', $id)->orderBy('name')->pluck('name', 'id');
        }

        return view('master.form', compact('conf', 'row', 'options', 'type'));
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $conf = $this->conf($type);

        $row = $this->findRow($conf, $id);

        $rules = $this->rules($conf);
        if ($type === 'units' && $request->filled('parent_id') && $row->id == $request->parent_id) {
            unset($rules['parent_id']);
        }

        $data = $request->validate($rules);

        if (collect($conf['fields'])->contains(fn ($f) => ($f['type'] ?? '') === 'boolean')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $row->update($data);

        return redirect()
            ->route('master.index', $type)
            ->with('success', "Master {$conf['label']} berhasil diperbarui.");
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $conf = $this->conf($type);
        $row = $this->findRow($conf, $id);

        if ($usage = $this->usageCount($type, $row)) {
            return back()->with('error', "Master ini masih dipakai {$usage} data, tidak bisa dihapus.");
        }

        $row->delete();

        return redirect()
            ->route('master.index', $type)
            ->with('success', "Master {$conf['label']} berhasil dihapus.");
    }

    private function types(): array
    {
        $types = [
            'units' => [
                'label' => 'Unit Kerja',
                'model' => WorkUnit::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama Unit', 'parent' => 'Unit Induk'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Unit', 'type' => 'text', 'required' => true],
                    ['name' => 'parent_id', 'label' => 'Unit Induk', 'type' => 'select', 'empty' => 'Tanpa Induk'],
                ],
                'display' => [
                    'parent' => fn ($row) => $row->parent?->name,
                ],
                'order' => 'name',
            ],
            'jabatan' => [
                'label' => 'Jabatan',
                'model' => Position::class,
                'columns' => ['name' => 'Nama Jabatan', 'type' => 'Jenis', 'eselon' => 'Eselon'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama Jabatan', 'type' => 'text', 'required' => true],
                    ['name' => 'type', 'label' => 'Jenis', 'type' => 'enum', 'options' => ['struktural' => 'Struktural', 'fungsional' => 'Fungsional', 'pelaksana' => 'Pelaksana']],
                    ['name' => 'eselon', 'label' => 'Eselon', 'type' => 'text'],
                ],
                'columns_label' => ['struktural' => 'Struktural', 'fungsional' => 'Fungsional', 'pelaksana' => 'Pelaksana'],
                'order' => 'name',
            ],
            'golongan' => [
                'label' => 'Golongan / Pangkat',
                'model' => Rank::class,
                'columns' => ['golongan' => 'Golongan', 'pangkat' => 'Pangkat', 'urutan' => 'Urutan'],
                'fields' => [
                    ['name' => 'golongan', 'label' => 'Golongan', 'type' => 'text', 'required' => true],
                    ['name' => 'pangkat', 'label' => 'Pangkat', 'type' => 'text'],
                    ['name' => 'urutan', 'label' => 'Urutan', 'type' => 'number'],
                ],
                'order' => 'urutan',
            ],
            'kategori' => [
                'label' => 'Kategori Pegawai',
                'model' => EmployeeCategory::class,
                'columns' => ['name' => 'Nama Kategori'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama Kategori', 'type' => 'text', 'required' => true],
                ],
                'order' => 'name',
            ],
            'pendidikan' => [
                'label' => 'Jenjang Pendidikan',
                'model' => EducationLevel::class,
                'columns' => ['name' => 'Jenjang', 'urutan' => 'Urutan'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Jenjang', 'type' => 'text', 'required' => true],
                    ['name' => 'urutan', 'label' => 'Urutan', 'type' => 'number'],
                ],
                'order' => 'urutan',
            ],
            'status' => [
                'label' => 'Status Kepegawaian',
                'model' => EmploymentStatus::class,
                'columns' => ['name' => 'Status'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Status', 'type' => 'text', 'required' => true],
                ],
                'order' => 'name',
            ],
            'sub-unit' => [
                'label' => 'Sub Unit Organisasi',
                'model' => SubUnit::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama Sub Unit', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Sub Unit', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'display' => ['is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif'],
                'order' => 'name',
            ],
            'jenis-jabatan' => [
                'label' => 'Jenis Jabatan',
                'model' => PositionType::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama Jenis Jabatan', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Jenis Jabatan', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'display' => ['is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif'],
                'order' => 'name',
            ],
            'jenis-pegawai' => [
                'label' => 'Jenis Pegawai',
                'model' => EmployeeType::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama Jenis Pegawai', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Jenis Pegawai', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'display' => ['is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif'],
                'order' => 'name',
            ],
            'jenis-pendidikan' => [
                'label' => 'Jenis Pendidikan',
                'model' => EducationType::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama Jenis Pendidikan', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Jenis Pendidikan', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'display' => ['is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif'],
                'order' => 'name',
            ],
            'jenis-diklat' => [
                'label' => 'Jenis Diklat',
                'model' => DiklatType::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama Jenis Diklat', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Jenis Diklat', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'display' => ['is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif'],
                'order' => 'name',
            ],
            'jenis-dokumen' => [
                'label' => 'Jenis Dokumen',
                'model' => DocumentType::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama Jenis Dokumen', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Jenis Dokumen', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'display' => ['is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif'],
                'order' => 'name',
            ],
            'jenis-penghargaan' => [
                'label' => 'Jenis Penghargaan',
                'model' => AwardType::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama Jenis Penghargaan', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Jenis Penghargaan', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'display' => ['is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif'],
                'order' => 'name',
            ],
            'aset' => [
                'label' => 'Aset Pegawai',
                'model' => AssetType::class,
                'columns' => ['name' => 'Nama Aset', 'category' => 'Kategori', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama Aset', 'type' => 'text', 'required' => true],
                    ['name' => 'category', 'label' => 'Kategori', 'type' => 'enum', 'options' => [
                        'kendaraan' => 'Kendaraan',
                        'elektronik' => 'Elektronik',
                        'perabot' => 'Perabot',
                        'sarana' => 'Sarana & Prasarana',
                        'lainnya' => 'Lainnya',
                    ]],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'columns_label' => [
                    'kendaraan' => 'Kendaraan',
                    'elektronik' => 'Elektronik',
                    'perabot' => 'Perabot',
                    'sarana' => 'Sarana & Prasarana',
                    'lainnya' => 'Lainnya',
                ],
                'display' => [
                    'is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif',
                    'category' => fn ($row) => ($row->category && isset($this->asetKategori()[$row->category])) ? $this->asetKategori()[$row->category] : ($row->category ?: '—'),
                ],
                'order' => 'name',
            ],
        ];

        foreach (MasterType::query()->where('is_active', true)->orderBy('sort')->orderBy('id')->get() as $mt) {
            $key = $mt->key;
            if (isset($types[$key])) {
                continue;
            }

            $types[$key] = [
                'label' => $mt->label,
                'model' => MasterItem::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama', 'urutan' => 'Urutan', 'description' => 'Keterangan', 'is_active' => 'Status'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'name', 'label' => 'Nama '.$mt->label, 'type' => 'text', 'required' => true],
                    ['name' => 'urutan', 'label' => 'Urutan', 'type' => 'number'],
                    ['name' => 'description', 'label' => 'Keterangan', 'type' => 'textarea'],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'boolean'],
                ],
                'display' => ['is_active' => fn ($row) => $row->is_active ? 'Aktif' : 'Nonaktif'],
                'order' => 'name',
                'master_type_id' => $mt->id,
            ];
        }

        return $types;
    }

    private function conf(string $type): array
    {
        $types = $this->types();

        if (! isset($types[$type])) {
            abort(404);
        }

        return $types[$type];
    }

    private function rules(array $conf): array
    {
        $rules = [];
        foreach ($conf['fields'] as $field) {
            $rule = ($field['required'] ?? false) ? ['required'] : ['nullable'];

            if (($field['type'] ?? '') === 'number') {
                $rule[] = 'integer';
            } elseif (($field['type'] ?? '') === 'boolean') {
                $rule[] = 'boolean';
            } elseif (($field['type'] ?? '') === 'enum') {
                $rule[] = 'in:'.implode(',', array_keys($field['options']));
            } elseif (($field['type'] ?? '') === 'select') {
                $rule[] = 'exists:work_units,id';
            }
            $rules[$field['name']] = $rule;
        }

        return $rules;
    }

    private function asetKategori(): array
    {
        return [
            'kendaraan' => 'Kendaraan',
            'elektronik' => 'Elektronik',
            'perabot' => 'Perabot',
            'sarana' => 'Sarana & Prasarana',
            'lainnya' => 'Lainnya',
        ];
    }

    private function findRow(array $conf, int $id)
    {
        $query = $conf['model']::query();
        if (isset($conf['master_type_id'])) {
            $query->where('master_type_id', $conf['master_type_id']);
        }

        return $query->findOrFail($id);
    }

    public function keys(): array
    {
        return array_keys($this->types());
    }

    private function usageCount(string $type, object $row): int
    {
        return match ($type) {
            'units' => $row->employees()->count() + $row->children()->count(),
            'jabatan' => Employee::where('current_position_id', $row->id)->count(),
            'golongan' => Employee::where('golongan_awal_id', $row->id)->orWhere('golongan_akhir_id', $row->id)->count(),
            'kategori' => Employee::where('employee_category_id', $row->id)->count(),
            'pendidikan' => Employee::where('pendidikan_awal_id', $row->id)->orWhere('pendidikan_akhir_id', $row->id)->count(),
            'status' => Employee::where('employment_status_id', $row->id)->count(),
            'aset' => \App\Models\EmployeeAsset::where('asset_type_id', $row->id)->count(),
            default => 0,
        };
    }
}