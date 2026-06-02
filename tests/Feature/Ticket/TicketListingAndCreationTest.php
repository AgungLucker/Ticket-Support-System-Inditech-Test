<?php

namespace Tests\Feature\Ticket;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketListingAndCreationTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // Listing Tiket Sesuai Role (Ketat)
    // =========================================================

    public function test_customer_hanya_melihat_tiket_milik_sendiri(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        Ticket::factory()->recycle([$customer])->create(['created_by' => $customer->id]);
        Ticket::factory()->recycle([$otherCustomer])->create(['created_by' => $otherCustomer->id]);

        $response = $this->actingAs($customer)->get(route('tickets.index'));

        $response->assertOk();
        $response->assertViewHas('tickets', fn($tickets) => $tickets->total() === 1);
    }

    public function test_agent_hanya_melihat_tiket_yang_ditugaskan_padanya(): void
    {
        $agent = User::factory()->agent()->create();
        $otherAgent = User::factory()->agent()->create();
        $customer = User::factory()->customer()->create();

        Ticket::factory()->recycle([$customer])->create(['assigned_agent_id' => $agent->id]);
        Ticket::factory()->recycle([$customer])->create(['assigned_agent_id' => null]);
        Ticket::factory()->recycle([$customer])->create(['assigned_agent_id' => $otherAgent->id]);

        $response = $this->actingAs($agent)->get(route('tickets.index'));

        $response->assertOk();
        $response->assertViewHas('tickets', fn($tickets) => $tickets->total() === 1);
    }

    public function test_admin_melihat_semua_tiket(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        Ticket::factory(5)->recycle([$customer])->create();

        $response = $this->actingAs($admin)->get(route('tickets.index'));

        $response->assertOk();
        $response->assertViewHas('tickets', fn($tickets) => $tickets->total() === 5);
    }

    // =========================================================
    // Admin Buat Tiket Atas Nama Customer
    // =========================================================

    public function test_admin_bisa_buat_tiket_atas_nama_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        $response = $this->actingAs($admin)->post(route('tickets.store'), [
            'title'       => 'Tiket dari Admin',
            'description' => 'Deskripsi tiket yang dibuat Admin atas nama Customer',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'created_by'  => $customer->id,
        ]);

        $response->assertRedirect(route('tickets.index'));
        $this->assertDatabaseHas('tickets', [
            'title'      => 'Tiket dari Admin',
            'created_by' => $customer->id,
        ]);
    }

    public function test_admin_wajib_pilih_customer_saat_buat_tiket(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        $response = $this->actingAs($admin)->post(route('tickets.store'), [
            'title'       => 'Tiket tanpa requester',
            'description' => 'Deskripsi',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
        ]);

        $response->assertSessionHasErrors('created_by');
    }

    public function test_customer_tidak_perlu_mengisi_created_by(): void
    {
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        $response = $this->actingAs($customer)->post(route('tickets.store'), [
            'title'       => 'Tiket dari Customer',
            'description' => 'Deskripsi tiket',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
        ]);

        $response->assertRedirect(route('tickets.index'));
        $this->assertDatabaseHas('tickets', [
            'title'      => 'Tiket dari Customer',
            'created_by' => $customer->id,
        ]);
    }

    // =========================================================
    // Otorisasi akses tiket
    // =========================================================

    public function test_customer_tidak_bisa_lihat_tiket_milik_customer_lain(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $ticket = Ticket::factory()->recycle([$customer2])->create(['created_by' => $customer2->id]);

        $this->actingAs($customer1)
            ->get(route('tickets.show', $ticket))
            ->assertForbidden();
    }

    // =========================================================
    // Filter Tiket
    // =========================================================

    public function test_filter_tiket_berdasarkan_status(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        Ticket::factory()->recycle([$customer])->create(['status' => 'Open']);
        Ticket::factory()->recycle([$customer])->create(['status' => 'Resolved']);

        $response = $this->actingAs($admin)->get(route('tickets.index', ['status' => 'Open']));

        $response->assertOk();
        $response->assertViewHas('tickets', fn($tickets) => $tickets->total() === 1);
    }

    // =========================================================
    // Filter Overdue
    // =========================================================

    public function test_filter_overdue_hanya_tampilkan_tiket_yang_melewati_sla(): void
    {
        $admin    = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        // Tiket overdue: due_at sudah lewat, status belum selesai
        Ticket::factory()->recycle([$customer])->create([
            'due_at' => now()->subHour(),
            'status' => 'Open',
        ]);

        // Tiket belum overdue
        Ticket::factory()->recycle([$customer])->create([
            'due_at' => now()->addDay(),
            'status' => 'Open',
        ]);

        // Tiket overdue tapi sudah Resolved (tidak dihitung overdue)
        Ticket::factory()->recycle([$customer])->create([
            'due_at' => now()->subHour(),
            'status' => 'Resolved',
        ]);

        $response = $this->actingAs($admin)->get(route('tickets.index', ['overdue' => '1']));

        $response->assertOk();
        $response->assertViewHas('tickets', fn($tickets) => $tickets->total() === 1);
    }

    public function test_filter_overdue_tidak_tampilkan_tiket_closed(): void
    {
        $admin    = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        // Overdue tapi Closed — tidak dihitung
        Ticket::factory()->recycle([$customer])->create([
            'due_at' => now()->subHour(),
            'status' => 'Closed',
        ]);

        $response = $this->actingAs($admin)->get(route('tickets.index', ['overdue' => '1']));

        $response->assertOk();
        $response->assertViewHas('tickets', fn($tickets) => $tickets->total() === 0);
    }

    public function test_filter_overdue_tanpa_due_at_tidak_dihitung(): void
    {
        $admin    = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        // Tiket tanpa due_at tidak dianggap overdue
        Ticket::factory()->recycle([$customer])->create([
            'due_at' => null,
            'status' => 'Open',
        ]);

        $response = $this->actingAs($admin)->get(route('tickets.index', ['overdue' => '1']));

        $response->assertOk();
        $response->assertViewHas('tickets', fn($tickets) => $tickets->total() === 0);
    }
}
