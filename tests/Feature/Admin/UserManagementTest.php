<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_user(): void
    {
        $admin = User::factory()->admin()->create();
        $customerRole = Role::firstWhere('slug', 'customer');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Demo Customer',
            'email' => 'demo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $customerRole->id,
        ])->assertRedirect();

        $user = User::where('email', 'demo@example.com')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
            'role_id' => $customerRole->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Customer']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
