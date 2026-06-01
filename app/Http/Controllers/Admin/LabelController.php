<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLabelRequest;
use App\Http\Requests\Admin\UpdateLabelRequest;
use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class LabelController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Label::class);
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

    public function store(StoreLabelRequest $request)
    {
        Gate::authorize('create', Label::class);

        $data = $request->validated();

        Label::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'color' => $data['color'] ?? '#e2e8f0',
        ]);

        return redirect()->back()->with('success', 'Label berhasil ditambahkan.');
    }

    public function update(UpdateLabelRequest $request, Label $label)
    {
        Gate::authorize('update', $label);

        $data = $request->validated();

        $label->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'color' => $data['color'] ?? '#e2e8f0',
        ]);

        return redirect()->back()->with('success', 'Label berhasil diperbarui.');
    }

    public function destroy(Label $label)
    {
        Gate::authorize('delete', $label);

        if ($label->tickets()->exists()) {
            return redirect()->back()->with('error', 'Label tidak dapat dihapus karena sedang terpasang pada tiket.');
        }

        $label->update(['slug' => $label->slug . '-deleted-' . time()]);
        $label->delete();
        return redirect()->back()->with('success', 'Label berhasil dihapus.');
    }
}
