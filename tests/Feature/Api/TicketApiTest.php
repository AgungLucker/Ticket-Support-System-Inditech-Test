<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    // --------------------------------------------------------
    // Auth
    // --------------------------------------------------------

    public function test_login_returns_token(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->postJson('/api/login', [
            'email'       => $user->email,
            'password'    => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertOk()
                 ->assertJsonPath('data.token', fn($v) => ! empty($v))
                 ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->customer()->create();

        $this->postJson('/api/login', [
            'email'       => $user->email,
            'password'    => 'wrong-password',
            'device_name' => 'test',
        ])->assertUnprocessable();
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/tickets')->assertUnauthorized();
    }

    public function test_logout_revokes_token(): void
    {
        $user  = User::factory()->customer()->create();
        $token = $user->createToken('test');

        $this->withToken($token->plainTextToken)->deleteJson('/api/logout')->assertOk();

        // Verifikasi token di-hapus dari database
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    // --------------------------------------------------------
    // List Tickets (role-based)
    // --------------------------------------------------------

    public function test_customer_hanya_melihat_tiket_miliknya_sendiri(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();
        Ticket::factory()->recycle([$customer1])->create(['created_by' => $customer1->id]);
        Ticket::factory()->recycle([$customer2])->create(['created_by' => $customer2->id]);

        $token    = $customer1->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/tickets');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_agent_hanya_melihat_tiket_yang_ditugaskan_padanya(): void
    {
        $agent    = User::factory()->agent()->create();
        $customer = User::factory()->customer()->create();

        Ticket::factory()->recycle([$customer])->create(['assigned_agent_id' => $agent->id]);
        Ticket::factory()->recycle([$customer])->create(['assigned_agent_id' => null]);

        $token    = $agent->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/tickets');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_admin_melihat_semua_tiket(): void
    {
        $admin    = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        Ticket::factory(3)->recycle([$customer])->create();

        $token    = $admin->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/tickets');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    // --------------------------------------------------------
    // Create Ticket
    // --------------------------------------------------------

    public function test_customer_bisa_buat_tiket(): void
    {
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        $token    = $customer->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/tickets', [
            'title'       => 'Tiket dari Customer via API',
            'description' => 'Deskripsi masalah',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
        ]);

        $response->assertCreated()
                 ->assertJsonPath('data.title', 'Tiket dari Customer via API');

        $this->assertDatabaseHas('tickets', [
            'title'      => 'Tiket dari Customer via API',
            'created_by' => $customer->id,
        ]);
    }

    public function test_admin_bisa_buat_tiket_atas_nama_customer(): void
    {
        $admin    = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        $token    = $admin->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/tickets', [
            'title'       => 'Tiket Admin atas nama Customer',
            'description' => 'Deskripsi',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'created_by'  => $customer->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('tickets', [
            'title'      => 'Tiket Admin atas nama Customer',
            'created_by' => $customer->id,
        ]);
    }

    // --------------------------------------------------------
    // Show Ticket — internal notes tidak bocor ke Customer
    // --------------------------------------------------------

    public function test_customer_tidak_melihat_internal_notes(): void
    {
        $customer = User::factory()->customer()->create();
        $agent    = User::factory()->agent()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by'        => $customer->id,
            'assigned_agent_id' => $agent->id,
        ]);

        // Tambah internal note dan public comment
        $ticket->comments()->create(['user_id' => $agent->id, 'content' => 'Catatan internal', 'is_internal_note' => true]);
        $ticket->comments()->create(['user_id' => $agent->id, 'content' => 'Komentar publik',  'is_internal_note' => false]);

        $token    = $customer->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();
        $comments = $response->json('data.comments');
        $this->assertCount(1, $comments);
        $this->assertEquals('Komentar publik', $comments[0]['body']);
    }

    public function test_agent_melihat_internal_notes_di_tiket_miliknya(): void
    {
        $customer = User::factory()->customer()->create();
        $agent    = User::factory()->agent()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by'        => $customer->id,
            'assigned_agent_id' => $agent->id,
        ]);

        $ticket->comments()->create(['user_id' => $agent->id, 'content' => 'Catatan internal', 'is_internal_note' => true]);
        $ticket->comments()->create(['user_id' => $agent->id, 'content' => 'Komentar publik',  'is_internal_note' => false]);

        $token    = $agent->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson("/api/tickets/{$ticket->id}");

        $response->assertOk();
        $this->assertCount(2, $response->json('data.comments'));
    }

    // --------------------------------------------------------
    // Update Status
    // --------------------------------------------------------

    public function test_agent_bisa_update_status_tiket_miliknya(): void
    {
        $customer = User::factory()->customer()->create();
        $agent    = User::factory()->agent()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by'        => $customer->id,
            'assigned_agent_id' => $agent->id,
            'status'            => 'Assigned',
        ]);

        $token    = $agent->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => 'In Progress',
        ]);

        $response->assertOk()->assertJsonPath('data.status', 'In Progress');
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'In Progress']);
    }

    public function test_status_tidak_valid_ditolak(): void
    {
        $customer = User::factory()->customer()->create();
        $agent    = User::factory()->agent()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by'        => $customer->id,
            'assigned_agent_id' => $agent->id,
            'status'            => 'Open',
        ]);

        $token = $agent->createToken('test')->plainTextToken;
        // Open → Resolved bukan transisi valid (Open hanya bisa ke Assigned atau Closed)
        $this->withToken($token)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => 'Resolved',
        ])->assertStatus(422);
    }

    // --------------------------------------------------------
    // Assign
    // --------------------------------------------------------

    public function test_admin_bisa_assign_tiket(): void
    {
        $admin    = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $agent    = User::factory()->agent()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by' => $customer->id,
            'status'     => 'Open',
        ]);

        $token    = $admin->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson("/api/tickets/{$ticket->id}/assign", [
            'agent_id' => $agent->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tickets', [
            'id'                => $ticket->id,
            'assigned_agent_id' => $agent->id,
            'status'            => 'Assigned',
        ]);
    }

    // --------------------------------------------------------
    // Add Comment
    // --------------------------------------------------------

    public function test_customer_bisa_tambah_komentar(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create(['created_by' => $customer->id]);

        $token    = $customer->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson("/api/tickets/{$ticket->id}/comments", [
            'body' => 'Saya lampirkan screenshotnya ya.',
        ]);

        $response->assertCreated()
                 ->assertJsonPath('data.body', 'Saya lampirkan screenshotnya ya.')
                 ->assertJsonPath('data.is_internal', false);
    }

    public function test_customer_tidak_bisa_buat_internal_note(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create(['created_by' => $customer->id]);

        $token = $customer->createToken('test')->plainTextToken;

        // Customer kirim is_internal=true tapi harus di-ignore (bukan 403)
        $response = $this->withToken($token)->postJson("/api/tickets/{$ticket->id}/comments", [
            'body'        => 'Ini harusnya publik.',
            'is_internal' => true,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('comments', [
            'content'          => 'Ini harusnya publik.',
            'is_internal_note' => false, // Dipaksa false untuk Customer
        ]);
    }
}
