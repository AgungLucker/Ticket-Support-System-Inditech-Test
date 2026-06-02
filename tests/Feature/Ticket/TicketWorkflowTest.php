<?php

namespace Tests\Feature\Ticket;

use App\Models\Category;
use App\Models\Priority;
use App\Models\SlaRule;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // Update Status — transisi valid
    // =========================================================

    public function test_agent_bisa_ubah_status_tiket_yang_ditugaskan(): void
    {
        $agent  = User::factory()->agent()->create();
        $customer = User::factory()->customer()->create();
        $ticket = Ticket::factory()->recycle([$customer])->create([
            'status'            => 'Assigned',
            'assigned_agent_id' => $agent->id,
        ]);

        $this->actingAs($agent)
            ->patch(route('tickets.updateStatus', $ticket), ['status' => 'In Progress'])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'In Progress']);
    }

    public function test_admin_bisa_ubah_status_tiket_manapun(): void
    {
        $admin  = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $ticket = Ticket::factory()->recycle([$customer])->create(['status' => 'Open']);

        $this->actingAs($admin)
            ->patch(route('tickets.updateStatus', $ticket), ['status' => 'Assigned'])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'Assigned']);
    }

    // =========================================================
    // Update Status — transisi tidak valid (ditolak service)
    // =========================================================

    public function test_transisi_status_tidak_valid_ditolak(): void
    {
        $admin  = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $ticket = Ticket::factory()->recycle([$customer])->create(['status' => 'Open']);

        // Open → In Progress tidak valid, harus lewat Assigned dulu
        $this->actingAs($admin)
            ->patch(route('tickets.updateStatus', $ticket), ['status' => 'In Progress'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'Open']);
    }

    public function test_customer_tidak_bisa_ubah_status_selain_reopen(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by' => $customer->id,
            'status'     => 'Assigned',
        ]);

        $this->actingAs($customer)
            ->patch(route('tickets.updateStatus', $ticket), ['status' => 'In Progress'])
            ->assertForbidden();
    }

    public function test_customer_bisa_reopen_tiket_miliknya_yang_resolved(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by' => $customer->id,
            'status'     => 'Resolved',
        ]);

        $this->actingAs($customer)
            ->patch(route('tickets.updateStatus', $ticket), ['status' => 'Reopened'])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'Reopened']);
    }

    public function test_customer_tidak_bisa_reopen_tiket_milik_customer_lain(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();
        $ticket    = Ticket::factory()->recycle([$customer2])->create([
            'created_by' => $customer2->id,
            'status'     => 'Resolved',
        ]);

        $this->actingAs($customer1)
            ->patch(route('tickets.updateStatus', $ticket), ['status' => 'Reopened'])
            ->assertForbidden();
    }

    // =========================================================
    // Assign Tiket
    // =========================================================

    public function test_admin_bisa_assign_tiket_ke_agent(): void
    {
        $admin    = User::factory()->admin()->create();
        $agent    = User::factory()->agent()->create();
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create(['status' => 'Open']);

        $this->actingAs($admin)
            ->post(route('tickets.assign', $ticket), ['agent_id' => $agent->id])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id'                => $ticket->id,
            'assigned_agent_id' => $agent->id,
            'status'            => 'Assigned',
        ]);
    }

    public function test_assign_tiket_open_otomatis_jadi_assigned(): void
    {
        $admin    = User::factory()->admin()->create();
        $agent    = User::factory()->agent()->create();
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create(['status' => 'Open']);

        $this->actingAs($admin)
            ->post(route('tickets.assign', $ticket), ['agent_id' => $agent->id]);

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'Assigned']);
    }

    public function test_customer_tidak_bisa_assign_tiket(): void
    {
        $customer = User::factory()->customer()->create();
        $agent    = User::factory()->agent()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create(['created_by' => $customer->id]);

        $this->actingAs($customer)
            ->post(route('tickets.assign', $ticket), ['agent_id' => $agent->id])
            ->assertForbidden();
    }

    // =========================================================
    // Komentar publik
    // =========================================================

    public function test_customer_bisa_tambah_komentar_publik(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create(['created_by' => $customer->id]);

        $this->actingAs($customer)
            ->post(route('tickets.comments.store', $ticket), ['content' => 'Halo ini komentar saya'])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'ticket_id'        => $ticket->id,
            'content'          => 'Halo ini komentar saya',
            'is_internal_note' => false,
        ]);
    }

    public function test_customer_tidak_bisa_kirim_internal_note(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create(['created_by' => $customer->id]);

        $this->actingAs($customer)->post(route('tickets.comments.store', $ticket), [
            'content'          => 'Coba kirim internal note',
            'is_internal_note' => '1',
        ]);

        // Tetap tersimpan tapi sebagai public comment, bukan internal note
        $this->assertDatabaseHas('comments', [
            'ticket_id'        => $ticket->id,
            'is_internal_note' => false,
        ]);
    }

    // =========================================================
    // Internal note tidak bocor ke Customer (wajib spek)
    // =========================================================

    public function test_internal_note_tidak_terlihat_oleh_customer(): void
    {
        $agent    = User::factory()->agent()->create();
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by'        => $customer->id,
            'assigned_agent_id' => $agent->id,
        ]);

        // Agent tambah internal note
        $ticket->comments()->create([
            'user_id'          => $agent->id,
            'content'          => 'Ini catatan rahasia untuk internal tim',
            'is_internal_note' => true,
        ]);

        // Customer buka halaman detail tiket
        $response = $this->actingAs($customer)->get(route('tickets.show', $ticket));

        $response->assertOk();
        $response->assertDontSee('Ini catatan rahasia untuk internal tim');
    }

    public function test_internal_note_terlihat_oleh_agent(): void
    {
        $agent    = User::factory()->agent()->create();
        $customer = User::factory()->customer()->create();
        $ticket   = Ticket::factory()->recycle([$customer])->create([
            'created_by'        => $customer->id,
            'assigned_agent_id' => $agent->id,
        ]);

        $ticket->comments()->create([
            'user_id'          => $agent->id,
            'content'          => 'Ini catatan rahasia untuk internal tim',
            'is_internal_note' => true,
        ]);

        $response = $this->actingAs($agent)->get(route('tickets.show', $ticket));

        $response->assertOk();
        $response->assertSee('Ini catatan rahasia untuk internal tim');
    }
}
