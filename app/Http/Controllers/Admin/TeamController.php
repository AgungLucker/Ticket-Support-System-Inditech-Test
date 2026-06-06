<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamRequest;
use App\Http\Requests\Admin\UpdateTeamRequest;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('access-admin');

        $teams = Team::query()
            ->with(['supervisor', 'agents'])
            ->withCount(['users as agents_count' => fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'agent'))])
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $supervisors = User::whereHas('role', fn($q) => $q->where('slug', 'supervisor'))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'team_id']);

        return view('admin.teams.index', compact('teams', 'supervisors'));
    }

    public function store(StoreTeamRequest $request)
    {
        Gate::authorize('access-admin');

        $data = $request->validated();
        $team = Team::create($data);

        $this->syncSupervisorTeam($data['supervisor_id'] ?? null, $team->id);

        return redirect()->back()->with('success', 'Tim berhasil ditambahkan.');
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        Gate::authorize('access-admin');

        $data          = $request->validated();
        $oldSupervisor = $team->supervisor_id;

        $team->update($data);

        // Lepas team_id dari supervisor lama jika diganti
        if ($oldSupervisor && $oldSupervisor !== ($data['supervisor_id'] ?? null)) {
            User::where('id', $oldSupervisor)->update(['team_id' => null]);
        }

        $this->syncSupervisorTeam($data['supervisor_id'] ?? null, $team->id);

        return redirect()->back()->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Team $team)
    {
        Gate::authorize('access-admin');

        if ($team->users()->whereHas('role', fn($q) => $q->where('slug', 'agent'))->exists()) {
            return redirect()->back()->with('error', 'Tim tidak dapat dihapus karena masih memiliki agent. Pindahkan agent ke tim lain terlebih dahulu.');
        }

        if ($team->supervisor_id) {
            User::where('id', $team->supervisor_id)->update(['team_id' => null]);
        }

        $team->delete();

        return redirect()->back()->with('success', 'Tim berhasil dihapus.');
    }

    private function syncSupervisorTeam(?int $supervisorId, int $teamId): void
    {
        if ($supervisorId) {
            User::where('id', $supervisorId)->update(['team_id' => $teamId]);
        }
    }
}
