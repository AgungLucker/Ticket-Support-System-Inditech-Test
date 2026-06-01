<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LabelController extends Controller
{
    public function index(Request $request)
    {
        $labels = Label::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.labels.index', compact('labels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('labels')->whereNull('deleted_at')],
            'color' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama label wajib diisi.',
            'name.unique' => 'Nama label ini sudah digunakan.',
        ]);

        Label::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#e2e8f0',
        ]);

        return redirect()->back()->with('success', 'Label berhasil ditambahkan.');
    }

    public function update(Request $request, Label $label)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('labels')->ignore($label->id)->whereNull('deleted_at')],
            'color' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama label wajib diisi.',
            'name.unique' => 'Nama label ini sudah digunakan.',
        ]);

        $label->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#e2e8f0',
        ]);

        return redirect()->back()->with('success', 'Label berhasil diperbarui.');
    }

    public function destroy(Label $label)
    {
        $label->update(['slug' => $label->slug . '-deleted-' . time()]);
        $label->delete();
        return redirect()->back()->with('success', 'Label berhasil dihapus.');
    }
}
