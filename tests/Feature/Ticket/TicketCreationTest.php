<?php

namespace Tests\Feature\Ticket;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
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

    public function test_attachment_upload_validates_file_size_and_type()
    {
        Storage::fake('public');

        $category = Category::factory()->create();
        $priority = Priority::factory()->create();

        // Invalid size (3MB) and invalid type (txt)
        $invalidFile = UploadedFile::fake()->create('document.txt', 3072);

        $response = $this->actingAs($this->customer)->post(route('tickets.store'), [
            'title' => 'Test',
            'description' => 'Test desc',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'attachments' => [$invalidFile],
        ]);

        $response->assertSessionHasErrors(['attachments.0']);
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
