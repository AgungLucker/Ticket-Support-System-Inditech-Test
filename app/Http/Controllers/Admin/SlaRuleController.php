<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSlaRuleRequest;
use App\Http\Requests\Admin\UpdateSlaRuleRequest;
use App\Models\Priority;
use App\Models\SlaRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SlaRuleController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', SlaRule::class);
        $slaRules = SlaRule::query()
            ->with('priority')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->whereHas('priority', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $priorities = Priority::doesntHave('slaRule')->get(); // Only priorities without SLA rules for new creation
        return view('admin.sla_rules.index', compact('slaRules', 'priorities'));
    }

    public function store(StoreSlaRuleRequest $request)
    {
        Gate::authorize('create', SlaRule::class);

        SlaRule::create($request->validated());

        return redirect()->back()->with('success', 'SLA Rule berhasil ditambahkan.');
    }

    public function update(UpdateSlaRuleRequest $request, SlaRule $slaRule)
    {
        Gate::authorize('update', $slaRule);

        $slaRule->update($request->validated());

        return redirect()->back()->with('success', 'SLA Rule berhasil diperbarui.');
    }

    public function destroy(SlaRule $slaRule)
    {
        Gate::authorize('delete', $slaRule);

        $slaRule->delete();
        return redirect()->back()->with('success', 'SLA Rule berhasil dihapus.');
    }
}
