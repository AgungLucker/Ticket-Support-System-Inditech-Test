<?php

namespace Tests\Unit\Ticket;

use App\Models\Priority;
use App\Models\SlaRule;
use App\Models\Ticket;
use App\Services\TicketService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketService = app(TicketService::class);
    }

    public function test_ticket_number_generator_creates_unique_sequential_format()
    {
        // Assert first ticket format
        $number1 = $this->ticketService->generateTicketNumber();
        $this->assertMatchesRegularExpression('/^TCK-\d{4}-\d{6}$/', $number1);

        // Mock a ticket in DB to test sequential logic
        Ticket::factory()->create([
            'ticket_number' => $number1,
        ]);

        $number2 = $this->ticketService->generateTicketNumber();

        // Ensure it increments
        $this->assertNotEquals($number1, $number2);

        $prefix = 'TCK-' . now()->year . '-';
        $this->assertEquals($prefix . '000001', $number1);
        $this->assertEquals($prefix . '000002', $number2);
    }

    public function test_sla_due_date_calculation_adds_resolution_hours()
    {
        $priority = Priority::factory()->create(['level' => 1]);
        SlaRule::create([
            'priority_id' => $priority->id,
            'response_time_hours' => 2,
            'resolution_time_hours' => 24,
        ]);

        $now = Carbon::now();
        Carbon::setTestNow($now);

        $dueDate = $this->ticketService->calculateSlaDueDate($priority->id);

        $this->assertEquals($now->addHours(24)->timestamp, $dueDate->timestamp);
    }

    public function test_sla_due_date_returns_null_if_no_rule()
    {
        $priority = Priority::factory()->create(['level' => 99]);
        // No SLA Rule created for this priority
        
        $dueDate = $this->ticketService->calculateSlaDueDate($priority->id);

        $this->assertNull($dueDate);
    }
}
