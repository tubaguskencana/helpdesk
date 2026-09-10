<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WebPushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public string $url,
        public ?int $ticketId = null
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            return;
        }

        $subscriptions = $user->pushSubscriptions;
        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'ticket_id' => $this->ticketId,
        ]);

        foreach ($subscriptions as $subscription) {
            try {
                // If native web push endpoint is configured
                if (filter_var($subscription->endpoint, FILTER_VALIDATE_URL)) {
                    $response = Http::timeout(5)->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post($subscription->endpoint, [
                        'payload' => $payload,
                    ]);

                    // If browser returns 404 or 410 Gone, subscription is no longer valid
                    if ($response->status() === 404 || $response->status() === 410) {
                        $subscription->delete();
                        Log::info("Web push subscription expired and removed: {$subscription->id}");
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to deliver web push to subscription {$subscription->id}: " . $e->getMessage());
            }
        }
    }
}
