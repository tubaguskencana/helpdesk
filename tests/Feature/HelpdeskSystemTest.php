<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpdeskSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test Login page loads and user can authenticate.
     */
    public function test_login_page_renders_and_user_can_authenticate(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign In to Your Account');

        $admin = User::where('email', 'admin@helpdesk.test')->first();

        $loginResponse = $this->post('/login', [
            'email' => 'admin@helpdesk.test',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test Admin Dashboard loads with overview metrics.
     */
    public function test_admin_dashboard_loads(): void
    {
        $admin = User::where('email', 'admin@helpdesk.test')->first();

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Administrator Executive Overview');
        $response->assertSee('Total Tickets');
    }

    /**
     * Test Requester Dashboard loads with user-tailored tickets.
     */
    public function test_user_dashboard_loads(): void
    {
        $user = User::where('email', 'user@helpdesk.test')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Requester Dashboard');
        $response->assertSee('Submit Support Ticket');
    }

    /**
     * Test Ticket Creation and Auto-generated ticket number.
     */
    public function test_user_can_create_ticket_with_auto_generated_number(): void
    {
        $user = User::where('email', 'user@helpdesk.test')->first();
        $dept = Department::where('name', 'IT Support')->first();
        $cat = Category::where('department_id', $dept->id)->first();

        $response = $this->actingAs($user)->post('/tickets', [
            'department_id' => $dept->id,
            'category_id' => $cat->id,
            'priority' => 'high',
            'subject' => 'Internet Lambat di Ruang Rapat',
            'description' => 'Koneksi internet sangat lambat dan sering terputus.',
        ]);

        $ticket = Ticket::where('subject', 'Internet Lambat di Ruang Rapat')->first();
        $this->assertNotNull($ticket);
        $this->assertMatchesRegularExpression('/HD-\d{4}-\d{6}/', $ticket->ticket_number);
        $this->assertEquals(Ticket::STATUS_OPEN, $ticket->status);
        $this->assertNotNull($ticket->sla_due_at);

        $response->assertRedirect(route('tickets.show', $ticket));
    }

    /**
     * Test Staff can see internal notes, but requester CANNOT see internal notes!
     */
    public function test_internal_notes_are_strictly_hidden_from_requester(): void
    {
        $ticket = Ticket::where('ticket_number', 'HD-2026-000101')->first();
        $this->assertNotNull($ticket);

        $agent = User::where('email', 'agent.it@helpdesk.test')->first();
        $requester = $ticket->user;

        // Staff sees internal notes
        $staffResponse = $this->actingAs($agent)->get(route('tickets.show', $ticket));
        $staffResponse->assertStatus(200);
        $staffResponse->assertSee('INTERNAL NOTE');

        // Requester DOES NOT see internal note
        $userResponse = $this->actingAs($requester)->get(route('tickets.show', $ticket));
        $userResponse->assertStatus(200);
        $userResponse->assertDontSee('INTERNAL NOTE &bull; Hidden from Requester', false);
        $userResponse->assertDontSee('Catatan Internal: Roller pick-up printer');
    }

    /**
     * Test Staff can post an internal note.
     */
    public function test_staff_can_post_internal_note(): void
    {
        $ticket = Ticket::where('ticket_number', 'HD-2026-000101')->first();
        $agent = User::where('email', 'agent.it@helpdesk.test')->first();

        $response = $this->actingAs($agent)->post(route('tickets.replies.store', $ticket), [
            'message' => 'Catatan Rahasia: Vendor printer sudah dikonfirmasi.',
            'is_internal' => 1,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'message' => 'Catatan Rahasia: Vendor printer sudah dikonfirmasi.',
            'is_internal' => true,
        ]);
    }

    /**
     * Test Ticket status update and activity log.
     */
    public function test_staff_can_update_ticket_status(): void
    {
        $ticket = Ticket::where('ticket_number', 'HD-2026-000101')->first();
        $agent = User::where('email', 'agent.it@helpdesk.test')->first();

        $response = $this->actingAs($agent)->patch(route('tickets.status', $ticket), [
            'status' => 'resolved',
        ]);

        $response->assertRedirect();
        $ticket->refresh();

        $this->assertEquals('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id,
            'activity_type' => 'resolved',
        ]);
    }

    /**
     * Test Report export to CSV works.
     */
    public function test_staff_can_export_csv_report(): void
    {
        $admin = User::where('email', 'admin@helpdesk.test')->first();

        $response = $this->actingAs($admin)->get(route('reports.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
