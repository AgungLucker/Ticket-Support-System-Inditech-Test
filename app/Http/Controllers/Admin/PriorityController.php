<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePriorityRequest;
use App\Http\Requests\Admin\UpdatePriorityRequest;
use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class PriorityController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Priority::class);
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

    public function store(StorePriorityRequest $request)
    {
        Gate::authorize('create', Priority::class);

        $data = $request->validated();

        Priority::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'color' => $data['color'] ?? '#94a3b8',
            'level' => $data['level'],
        ]);

        return redirect()->back()->with('success', 'Prioritas berhasil ditambahkan.');
    }

    public function update(UpdatePriorityRequest $request, Priority $priority)
    {
        Gate::authorize('update', $priority);

        $data = $request->validated();

        $priority->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'color' => $data['color'] ?? '#94a3b8',
            'level' => $data['level'],
        ]);

        return redirect()->back()->with('success', 'Prioritas berhasil diperbarui.');
    }

    public function destroy(Priority $priority)
    {
        Gate::authorize('delete', $priority);

        if ($priority->tickets()->exists()) {
            return redirect()->back()->with('error', 'Prioritas tidak dapat dihapus karena sedang digunakan oleh tiket.');
        }

        $priority->update(['slug' => $priority->slug . '-deleted-' . time()]);
        $priority->delete();
        return redirect()->back()->with('success', 'Prioritas berhasil dihapus.');
    }
}
