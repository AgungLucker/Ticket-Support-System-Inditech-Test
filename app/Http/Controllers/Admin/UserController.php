<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->with(['role', 'team'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role_id'), fn ($query) => $query->where('role_id', $request->integer('role_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'teams' => Team::orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $data = $this->normalizedData($request->validated());
        
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $this->normalizedData($request->validated());

        if ($request->user()->is($user) && (int) $data['role_id'] !== $user->role_id) {
            throw ValidationException::withMessages([
                'role_id' => 'Anda tidak dapat mengubah role akun yang sedang digunakan.',
            ]);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        if ($user->id === $request->user()->id) {
            return redirect()->back()->withErrors([
                'user' => 'Anda tidak dapat menghapus akun Anda sendiri melalui halaman ini.',
            ]);
        }

        if (
            $user->createdTickets()->exists()
            || $user->assignedTickets()->exists()
            || $user->supervisedTeams()->exists()
            || $user->comments()->exists()
            || $user->uploadedAttachments()->exists()
        ) {
            return redirect()->back()->withErrors([
                'user' => 'User tidak dapat dihapus karena masih terhubung dengan tiket, tim, komentar, atau lampiran.',
            ]);
        }

        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    private function normalizedData(array $data): array
    {
        $role = Role::findOrFail($data['role_id']);

        if (in_array($role->slug, ['admin', 'customer', 'supervisor'], true)) {
            $data['team_id'] = null;
        }

        return $data;
    }
}
