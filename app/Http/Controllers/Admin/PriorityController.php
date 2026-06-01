<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PriorityController extends Controller
{
    public function index(Request $request)
    {
        $priorities = Priority::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('level', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.priorities.index', compact('priorities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('priorities')->whereNull('deleted_at')],
            'color' => 'nullable|string|max:20',
            'level' => ['required', 'integer', Rule::unique('priorities')->whereNull('deleted_at')],
        ], [
            'name.required' => 'Nama prioritas wajib diisi.',
            'name.unique' => 'Nama prioritas ini sudah digunakan.',
            'level.required' => 'Level angka wajib diisi.',
            'level.unique' => 'Level angka ini sudah digunakan oleh prioritas lain.',
        ]);

        Priority::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#94a3b8',
            'level' => $request->level,
        ]);

        return redirect()->back()->with('success', 'Prioritas berhasil ditambahkan.');
    }

    public function update(Request $request, Priority $priority)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('priorities')->ignore($priority->id)->whereNull('deleted_at')],
            'color' => 'nullable|string|max:20',
            'level' => ['required', 'integer', Rule::unique('priorities')->ignore($priority->id)->whereNull('deleted_at')],
        ], [
            'name.required' => 'Nama prioritas wajib diisi.',
            'name.unique' => 'Nama prioritas ini sudah digunakan.',
            'level.required' => 'Level angka wajib diisi.',
            'level.unique' => 'Level angka ini sudah digunakan oleh prioritas lain.',
        ]);

        $priority->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#94a3b8',
            'level' => $request->level,
        ]);

        return redirect()->back()->with('success', 'Prioritas berhasil diperbarui.');
    }

    public function destroy(Priority $priority)
    {
        $priority->update(['slug' => $priority->slug . '-deleted-' . time()]);
        $priority->delete();
        return redirect()->back()->with('success', 'Prioritas berhasil dihapus.');
    }
}
