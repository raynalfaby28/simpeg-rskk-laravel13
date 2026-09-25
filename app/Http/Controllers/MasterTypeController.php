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
        $gapOptions = $this->gapOptions();

        return view('master.types.form', compact('gapOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort' => ['required', 'integer', 'min:1'],
        ]);

        $key = $this->uniqueKey($data['label']);

        MasterType::create([
            'label' => $data['label'],
            'key' => $key,
            'description' => $data['description'] ?? null,
            'sort' => $this->gap((int) $data['sort']),
            'is_active' => true,
        ]);

        return redirect()
            ->route('master.types.index')
            ->with('success', "Jenis master data «{$data['label']}» berhasil ditambahkan.");
    }

    public function edit(MasterType $masterType): View
    {
        $gapOptions = $this->gapOptions();
        $currentPosition = $this->gap((int) $masterType->sort);

        return view('master.types.form', compact('masterType', 'gapOptions', 'currentPosition'));
    }

    public function update(Request $request, MasterType $masterType): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort' => ['required', 'integer', 'min:1'],
        ]);

        $masterType->update([
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
            'sort' => $this->gap((int) $data['sort']),
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

    private function gap(int $position): int
    {
        return max(1, min($position, (new MasterDataController)->maxGap()));
    }

    private function gapOptions(): array
    {
        $labels = (new MasterDataController)->builtInLabels();

        $options = [];
        foreach ($labels as $i => $label) {
            $options[$i + 1] = "Sebelum {$label}";
        }
        $options[count($labels) + 1] = 'Paling akhir';

        return $options;
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
