<?php

namespace Tests\Feature\Ticket;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketViewingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer1;
    protected User $customer2;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $customerRole = Role::factory()->create(['slug' => 'customer']);
        $adminRole = Role::factory()->create(['slug' => 'admin']);

        $this->customer1 = User::factory()->create(['role_id' => $customerRole->id]);
        $this->customer2 = User::factory()->create(['role_id' => $customerRole->id]);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
    }

    public function test_unauthenticated_users_cannot_access_tickets()
    {
        $response = $this->get(route('tickets.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_only_view_own_tickets_in_listing()
    {
        Ticket::factory()->count(2)->create(['created_by' => $this->customer1->id]);
        Ticket::factory()->count(3)->create(['created_by' => $this->customer2->id]);

        $response = $this->actingAs($this->customer1)->get(route('tickets.index'));

        $response->assertStatus(200);
        // Customer 1 should only see their 2 tickets
        $response->assertViewHas('tickets');
        $tickets = $response->original->getData()['tickets'];
        $this->assertCount(2, $tickets);
    }

    public function test_customer_cannot_view_other_customers_ticket_detail()
    {
        $ticket = Ticket::factory()->create(['created_by' => $this->customer2->id]);

        $response = $this->actingAs($this->customer1)->get(route('tickets.show', $ticket));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_all_tickets_in_listing()
    {
        Ticket::factory()->count(2)->create(['created_by' => $this->customer1->id]);
        Ticket::factory()->count(3)->create(['created_by' => $this->customer2->id]);

        $response = $this->actingAs($this->admin)->get(route('tickets.index'));

        $response->assertStatus(200);
        // Admin should see all 5 tickets
        $response->assertViewHas('tickets');
        $tickets = $response->original->getData()['tickets'];
        $this->assertCount(5, $tickets);
    }
}
