<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(
        public string $phone,
        public string $message,
        public ?int $ticketId = null,
        public ?int $userId = null,
        public string $type = 'general'
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        $ticket = $this->ticketId ? Ticket::find($this->ticketId) : null;

        $log = $whatsAppService->sendMessage(
            phone: $this->phone,
            message: $this->message,
            ticket: $ticket,
            userId: $this->userId,
            type: $this->type
        );

        if ($log->status === 'failed') {
            Log::warning("SendWhatsAppNotification failed: {$log->error_message}");
        }
    }
}
