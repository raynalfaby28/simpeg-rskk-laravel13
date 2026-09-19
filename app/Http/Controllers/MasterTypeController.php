<?php

namespace App\Http\Controllers;

use App\Models\MasterType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MasterTypeController extends Controller
{
    public function index(): View
    {
        $types = MasterType::withCount('items')->orderBy('sort')->orderBy('id')->get();

        return view('master.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('master.types.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $key = $this->uniqueKey($data['label']);

        MasterType::create([
            'label' => $data['label'],
            'key' => $key,
            'description' => $data['description'] ?? null,
            'sort' => $data['sort'] ?? 100,
            'is_active' => true,
        ]);

        return redirect()
            ->route('master.types.index')
            ->with('success', "Jenis master data «{$data['label']}» berhasil ditambahkan.");
    }

    public function edit(MasterType $masterType): View
    {
        return view('master.types.form', compact('masterType'));
    }

    public function update(Request $request, MasterType $masterType): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $masterType->update([
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
            'sort' => $data['sort'] ?? 100,
        ]);

        return redirect()
            ->route('master.types.index')
            ->with('success', "Jenis master data «{$data['label']}» berhasil diperbarui.");
    }

    public function destroy(MasterType $masterType): RedirectResponse
    {
        if ($masterType->items()->exists()) {
            return back()->with('error', "Jenis «{$masterType->label}» masih memiliki {$masterType->items()->count()} data, tidak bisa dihapus.");
        }

        $masterType->delete();

        return redirect()
            ->route('master.types.index')
            ->with('success', "Jenis master data «{$masterType->label}» berhasil dihapus.");
    }

    private function uniqueKey(string $label): string
    {
        $base = Str::slug($label, '-');
        $key = $base ?: 'tipe-master';
        $reserved = array_merge((new MasterDataController)->keys(), ['tipe']);

        $candidate = $key;
        $n = 2;
        while (in_array($candidate, $reserved, true) || MasterType::where('key', $candidate)->exists()) {
            $reserved[] = $candidate;
            $candidate = $key.'-'.$n++;
        }

        return $candidate;
    }
}