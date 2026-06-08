<?php

namespace Tests\Unit\Ticket;

use App\Services\TicketStatusService;
use Tests\TestCase;

class TicketStatusServiceTest extends TestCase
{
    private TicketStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TicketStatusService();
    }

    public function test_terdapat_delapan_status(): void
    {
        $this->assertCount(8, TicketStatusService::allStatuses());
    }

    public function test_open_bisa_ke_assigned(): void
    {
        $this->assertTrue($this->service->canTransition('Open', 'Assigned'));
    }

    public function test_open_bisa_ke_closed(): void
    {
        $this->assertTrue($this->service->canTransition('Open', 'Closed'));
    }

    public function test_assigned_bisa_ke_in_progress(): void
    {
        $this->assertTrue($this->service->canTransition('Assigned', 'In Progress'));
    }

    public function test_assigned_bisa_ke_escalated(): void
    {
        $this->assertTrue($this->service->canTransition('Assigned', 'Escalated'));
    }

    public function test_in_progress_bisa_ke_resolved(): void
    {
        $this->assertTrue($this->service->canTransition('In Progress', 'Resolved'));
    }

    public function test_in_progress_bisa_ke_waiting_for_customer(): void
    {
        $this->assertTrue($this->service->canTransition('In Progress', 'Waiting for Customer'));
    }

    public function test_resolved_bisa_ke_reopened(): void
    {
        $this->assertTrue($this->service->canTransition('Resolved', 'Reopened'));
    }

    public function test_closed_bisa_ke_reopened(): void
    {
        $this->assertTrue($this->service->canTransition('Closed', 'Reopened'));
    }

    public function test_escalated_bisa_ke_in_progress(): void
    {
        $this->assertTrue($this->service->canTransition('Escalated', 'In Progress'));
    }

    public function test_escalated_bisa_ke_resolved(): void
    {
        $this->assertTrue($this->service->canTransition('Escalated', 'Resolved'));
    }

    public function test_escalated_tidak_bisa_ke_assigned(): void
    {
        $this->assertFalse($this->service->canTransition('Escalated', 'Assigned'));
    }

    public function test_waiting_for_customer_tidak_bisa_ke_closed(): void
    {
        $this->assertFalse($this->service->canTransition('Waiting for Customer', 'Closed'));
    }

    public function test_open_tidak_bisa_langsung_ke_resolved(): void
    {
        $this->assertFalse($this->service->canTransition('Open', 'Resolved'));
    }

    public function test_open_tidak_bisa_langsung_ke_in_progress(): void
    {
        $this->assertFalse($this->service->canTransition('Open', 'In Progress'));
    }

    public function test_resolved_tidak_bisa_ke_open(): void
    {
        $this->assertFalse($this->service->canTransition('Resolved', 'Open'));
    }

    public function test_closed_tidak_bisa_ke_in_progress(): void
    {
        $this->assertFalse($this->service->canTransition('Closed', 'In Progress'));
    }

    public function test_status_tidak_dikenal_tidak_punya_transisi(): void
    {
        $this->assertEmpty($this->service->allowedTransitions('StatusPalsu'));
    }

    public function test_open_hanya_punya_dua_transisi(): void
    {
        $this->assertEqualsCanonicalizing(['Assigned', 'Closed'], $this->service->allowedTransitions('Open'));
    }

    public function test_in_progress_punya_tiga_transisi(): void
    {
        $this->assertEqualsCanonicalizing(
            ['Waiting for Customer', 'Resolved', 'Escalated'],
            $this->service->allowedTransitions('In Progress')
        );
    }
}
