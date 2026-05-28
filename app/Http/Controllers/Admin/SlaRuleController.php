<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Models\SlaRule;
use Illuminate\Http\Request;

class SlaRuleController extends Controller
{
    public function index()
    {
        $slaRules = SlaRule::with('priority')->latest()->get();
        $priorities = Priority::doesntHave('slaRule')->get(); // Only priorities without SLA rules for new creation
        return view('admin.sla_rules.index', compact('slaRules', 'priorities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'priority_id' => 'required|exists:priorities,id|unique:sla_rules,priority_id',
            'response_time_hours' => 'required|integer|min:1',
            'resolution_time_hours' => 'required|integer|min:1|gte:response_time_hours',
        ], [
            'priority_id.required' => 'Prioritas wajib dipilih.',
            'priority_id.unique' => 'Prioritas ini sudah memiliki aturan SLA.',
            'response_time_hours.required' => 'Target Respon wajib diisi.',
            'resolution_time_hours.required' => 'Target Penyelesaian wajib diisi.',
            'resolution_time_hours.gte' => 'Target Penyelesaian tidak boleh lebih cepat (angkanya lebih kecil) dari Target Respon awal.',
        ]);

        SlaRule::create($request->only(['priority_id', 'response_time_hours', 'resolution_time_hours']));

        return redirect()->back()->with('success', 'SLA Rule berhasil ditambahkan.');
    }

    public function update(Request $request, SlaRule $slaRule)
    {
        $request->validate([
            'response_time_hours' => 'required|integer|min:1',
            'resolution_time_hours' => 'required|integer|min:1|gte:response_time_hours',
        ], [
            'response_time_hours.required' => 'Target Respon wajib diisi.',
            'resolution_time_hours.required' => 'Target Penyelesaian wajib diisi.',
            'resolution_time_hours.gte' => 'Target Penyelesaian tidak boleh lebih cepat (angkanya lebih kecil) dari Target Respon awal.',
        ]);

        $slaRule->update($request->only(['response_time_hours', 'resolution_time_hours']));

        return redirect()->back()->with('success', 'SLA Rule berhasil diperbarui.');
    }

    public function destroy(SlaRule $slaRule)
    {
        $slaRule->delete();
        return redirect()->back()->with('success', 'SLA Rule berhasil dihapus.');
    }
}
