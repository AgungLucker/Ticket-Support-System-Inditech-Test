<?php

namespace Tests\Feature\Ticket;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\SlaRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $customerRole = Role::factory()->create(['slug' => 'customer']);
        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);
    }

    public function test_customer_can_create_ticket_with_valid_data()
    {
        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        $response = $this->actingAs($this->customer)->post(route('tickets.store'), [
            'title' => 'Internet Mati Total',
            'description' => 'Lampu modem merah (LOS).',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
        ]);

        $response->assertRedirect(route('tickets.index'));
        $this->assertDatabaseHas('tickets', [
            'title' => 'Internet Mati Total',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'created_by' => $this->customer->id,
            'status' => 'Open',
        ]);
    }

    public function test_attachment_upload_validates_file_size()
    {
        Storage::fake('local');

        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        // Valid type (jpg) but exceeds 2MB limit
        $oversizedFile = UploadedFile::fake()->create('screenshot.jpg', 3072);

        $this->actingAs($this->customer)->post(route('tickets.store'), [
            'title'       => 'Test',
            'description' => 'Test desc',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'attachments' => [$oversizedFile],
        ])->assertSessionHasErrors(['attachments.0']);
    }

    public function test_attachment_upload_validates_file_type()
    {
        Storage::fake('local');

        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        // Valid size but disallowed type (txt)
        $invalidTypeFile = UploadedFile::fake()->create('notes.txt', 512);

        $this->actingAs($this->customer)->post(route('tickets.store'), [
            'title'       => 'Test',
            'description' => 'Test desc',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'attachments' => [$invalidTypeFile],
        ])->assertSessionHasErrors(['attachments.0']);
    }

    public function test_sla_due_date_is_set_when_ticket_is_created()
    {
        $priority = Priority::factory()->create();
        SlaRule::create([
            'priority_id'            => $priority->id,
            'response_time_hours'    => 4,
            'resolution_time_hours'  => 24,
        ]);
        $category = Category::factory()->create();

        $this->actingAs($this->customer)->post(route('tickets.store'), [
            'title'       => 'Test SLA',
            'description' => 'Cek due_at terisi otomatis',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
        ]);

        $ticket = \App\Models\Ticket::where('title', 'Test SLA')->first();
        $this->assertNotNull($ticket->due_at);
        $this->assertNotNull($ticket->response_due_at);
        // due_at should be ~24 hours from now
        $this->assertEqualsWithDelta(
            now()->addHours(24)->timestamp,
            $ticket->due_at->timestamp,
            60
        );
    }

    public function test_attachment_upload_saves_valid_files()
    {
        Storage::fake('public');

        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        // Valid image
        $validFile = UploadedFile::fake()->image('screenshot.jpg');

        $response = $this->actingAs($this->customer)->post(route('tickets.store'), [
            'title' => 'Test Upload',
            'description' => 'Test desc upload',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'attachments' => [$validFile],
        ]);

        $response->assertRedirect(route('tickets.index'));
        $this->assertDatabaseHas('attachments', [
            'original_name' => 'screenshot.jpg',
            'attachable_type' => 'App\Models\Ticket',
        ]);
    }
}
