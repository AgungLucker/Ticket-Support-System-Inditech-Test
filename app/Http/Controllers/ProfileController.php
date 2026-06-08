<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->isAgent()) {
            $activeStatuses = ['Open', 'Assigned', 'In Progress', 'Waiting for Customer', 'Reopened', 'Escalated'];
            if ($user->assignedTickets()->whereIn('status', $activeStatuses)->exists()) {
                return Redirect::route('profile.edit')->withErrors([
                    'userDeletion' => 'Akun tidak dapat dihapus karena masih ada tiket aktif yang di-assign kepada Anda.',
                ], 'userDeletion');
            }
        }

        if ($user->isSupervisor() && $user->supervisedTeams()->exists()) {
            return Redirect::route('profile.edit')->withErrors([
                'userDeletion' => 'Akun tidak dapat dihapus karena Anda masih menjadi supervisor dari sebuah team.',
            ], 'userDeletion');
        }

        if ($user->isAdmin()) {
            $adminRole = \App\Models\Role::where('slug', 'admin')->first();
            $adminCount = \App\Models\User::where('role_id', $adminRole?->id)->count();
            if ($adminCount <= 1) {
                return Redirect::route('profile.edit')->withErrors([
                    'userDeletion' => 'Akun tidak dapat dihapus karena Anda adalah satu-satunya admin di sistem.',
                ], 'userDeletion');
            }
        }

        if ($user->isCustomer() && $user->createdTickets()->exists()) {
            return Redirect::route('profile.edit')->withErrors([
                'userDeletion' => 'Akun tidak dapat dihapus karena masih ada riwayat tiket yang terkait dengan akun Anda.',
            ], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
