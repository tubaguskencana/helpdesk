<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\SlaSetting;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WebPushSubscription;
use App\Models\WhatsAppMessage;
use App\Services\TicketService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $agent;
    protected User $user;
    protected Department $department;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed SLA settings
        SlaSetting::create([
            'priority' => Ticket::PRIORITY_MEDIUM,
            'first_response_hours' => 8,
            'resolution_hours' => 48,
        ]);
        SlaSetting::create([
            'priority' => Ticket::PRIORITY_HIGH,
            'first_response_hours' => 4,
            'resolution_hours' => 24,
        ]);

        // Seed Notification Settings
        foreach (['ticket_created', 'ticket_assigned', 'reply_added', 'status_changed', 'priority_changed'] as $evt) {
            NotificationSetting::create([
                'event' => $evt,
                'web_enabled' => true,
                'push_enabled' => true,
                'whatsapp_enabled' => true,
            ]);
        }

        $this->department = Department::create([
            'name' => 'IT Support',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'department_id' => $this->department->id,
            'name' => 'Hardware',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department_id' => $this->department->id,
            'phone' => '08110001001',
        ]);

        $this->supervisor = User::factory()->create([
            'role' => User::ROLE_SUPERVISOR,
            'department_id' => $this->department->id,
            'phone' => '08110001002',
        ]);

        $this->agent = User::factory()->create([
            'role' => User::ROLE_AGENT,
            'department_id' => $this->department->id,
            'phone' => '08110001003',
        ]);

        $this->user = User::factory()->create([
            'role' => User::ROLE_USER,
            'department_id' => $this->department->id,
            'phone' => '08123456789',
        ]);
    }

    public function test_in_app_notification_created_when_ticket_is_submitted(): void
    {
        $ticketService = app(TicketService::class);

        $ticket = $ticketService->createTicket($this->user, [
            'department_id' => $this->department->id,
            'category_id' => $this->category->id,
            'subject' => 'Printer Jam Lantai 2',
            'description' => 'Printer macet di lantai 2',
            'priority' => Ticket::PRIORITY_MEDIUM,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => Notification::TYPE_TICKET_CREATED,
        ]);
    }

    public function test_in_app_notification_created_when_ticket_is_assigned(): void
    {
        $ticketService = app(TicketService::class);

        $ticket = $ticketService->createTicket($this->user, [
            'department_id' => $this->department->id,
            'category_id' => $this->category->id,
            'subject' => 'Cannot connect to VPN',
            'description' => 'VPN TLS handshake error',
            'priority' => Ticket::PRIORITY_HIGH,
        ]);

        $ticketService->assignAgent($ticket, $this->supervisor, $this->agent);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->agent->id,
            'type' => Notification::TYPE_TICKET_ASSIGNED,
        ]);
    }

    public function test_internal_notes_do_not_notify_requester(): void
    {
        $ticketService = app(TicketService::class);

        $ticket = $ticketService->createTicket($this->user, [
            'department_id' => $this->department->id,
            'category_id' => $this->category->id,
            'subject' => 'Monitor flickers',
            'description' => 'Screen goes black periodically',
            'priority' => Ticket::PRIORITY_MEDIUM,
        ]);

        $ticketService->assignAgent($ticket, $this->supervisor, $this->agent);

        // Clear existing notifications
        Notification::truncate();

        // Agent adds an internal note
        $ticketService->addReply($ticket, $this->agent, 'Waiting for replacement power supply.', true);

        // Requester MUST NOT receive notification for internal notes
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->user->id,
            'type' => Notification::TYPE_REPLY_ADDED,
        ]);
    }

    public function test_public_reply_notifies_requester(): void
    {
        $ticketService = app(TicketService::class);

        $ticket = $ticketService->createTicket($this->user, [
            'department_id' => $this->department->id,
            'category_id' => $this->category->id,
            'subject' => 'Email activation',
            'description' => 'Need new corporate email',
            'priority' => Ticket::PRIORITY_MEDIUM,
        ]);

        $ticketService->assignAgent($ticket, $this->supervisor, $this->agent);

        // Clear notifications
        Notification::truncate();

        // Agent replies publicly
        $ticketService->addReply($ticket, $this->agent, 'Your account has been created.', false);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => Notification::TYPE_REPLY_ADDED,
        ]);
    }

    public function test_notification_unread_count_and_mark_as_read(): void
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'type' => Notification::TYPE_TICKET_CREATED,
            'title' => 'Test Notification',
            'message' => 'This is a test notification message.',
            'url' => '/dashboard',
        ]);

        $this->actingAs($this->user);

        // Check unread count endpoint
        $response = $this->getJson(route('notifications.unread-count'));
        $response->assertOk()
            ->assertJson(['unread_count' => 1]);

        // Mark as read via GET read route
        $readResponse = $this->get(route('notifications.read', $notification));
        $readResponse->assertRedirect('/dashboard');

        $this->assertNotNull($notification->fresh()->read_at);

        // Count should now be 0
        $response2 = $this->getJson(route('notifications.unread-count'));
        $response2->assertOk()
            ->assertJson(['unread_count' => 0]);
    }

    public function test_phone_number_normalization(): void
    {
        $waService = app(WhatsAppService::class);

        $this->assertEquals('628123456789', $waService->normalizePhoneNumber('08123456789'));
        $this->assertEquals('628123456789', $waService->normalizePhoneNumber('+62 812-3456-789'));
        $this->assertEquals('628123456789', $waService->normalizePhoneNumber('8123456789'));
        $this->assertNull($waService->normalizePhoneNumber(''));
        $this->assertNull($waService->normalizePhoneNumber('123')); // too short
    }

    public function test_whatsapp_message_logging_and_audit(): void
    {
        $waService = app(WhatsAppService::class);
        $waService->saveSessionData([
            'status' => WhatsAppService::STATUS_CONNECTED,
            'account' => '628110000888',
        ]);

        $log = $waService->sendMessage(
            phone: '08123456789',
            message: 'Testing WhatsApp message delivery',
            userId: $this->user->id,
            type: 'test_event'
        );

        $this->assertDatabaseHas('whatsapp_messages', [
            'id' => $log->id,
            'phone' => '628123456789',
            'status' => WhatsAppMessage::STATUS_SENT,
        ]);
    }

    public function test_whatsapp_fails_gracefully_when_disconnected(): void
    {
        $waService = app(WhatsAppService::class);
        $waService->disconnect();

        $log = $waService->sendMessage(
            phone: '08123456789',
            message: 'Testing failed delivery',
            userId: $this->user->id
        );

        $this->assertEquals(WhatsAppMessage::STATUS_FAILED, $log->status);
    }

    public function test_web_push_subscription_endpoints(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('webpush.subscribe'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-token',
            'public_key' => 'sample-public-key',
            'auth_token' => 'sample-auth-token',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('web_push_subscriptions', [
            'user_id' => $this->user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/sample-token',
        ]);
    }

    public function test_admin_whatsapp_integration_panel(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.notifications.whatsapp.index'));
        $response->assertOk()
            ->assertSee('WhatsApp Notification Integration');

        $statusResponse = $this->getJson(route('admin.notifications.whatsapp.status'));
        $statusResponse->assertOk()
            ->assertJsonStructure(['status']);
    }
}
