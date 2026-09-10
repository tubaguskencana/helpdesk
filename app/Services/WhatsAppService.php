<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Models\Ticket;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppService
{
    public const STATUS_DISCONNECTED = 'DISCONNECTED';
    public const STATUS_CONNECTING = 'CONNECTING';
    public const STATUS_QR_REQUIRED = 'QR_REQUIRED';
    public const STATUS_CONNECTED = 'CONNECTED';
    public const STATUS_RECONNECTING = 'RECONNECTING';
    public const STATUS_ERROR = 'ERROR';

    protected string $sessionFile = 'whatsapp/session.json';

    public function isEnabled(): bool
    {
        return config('services.whatsapp.enabled', env('WHATSAPP_ENABLED', true));
    }

    public function isMockMode(): bool
    {
        return config('services.whatsapp.mock', env('WHATSAPP_MOCK_MODE', true));
    }

    public function getServiceUrl(): string
    {
        return rtrim(config('services.whatsapp.url', env('WHATSAPP_SERVICE_URL', 'http://localhost:3000')), '/');
    }

    public function getServiceToken(): string
    {
        return config('services.whatsapp.token', env('WHATSAPP_SERVICE_TOKEN', ''));
    }

    public function getSessionData(): array
    {
        if (Storage::disk('local')->exists($this->sessionFile)) {
            try {
                return json_decode(Storage::disk('local')->get($this->sessionFile), true) ?? [];
            } catch (\Throwable) {
                return [];
            }
        }
        return [];
    }

    public function saveSessionData(array $data): void
    {
        Storage::disk('local')->put($this->sessionFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function getStatus(): array
    {
        if ($this->isMockMode()) {
            $session = $this->getSessionData();
            $status = $session['status'] ?? self::STATUS_CONNECTED; // Default connected in mock mode for instant usability
            
            return [
                'status' => $status,
                'account' => $session['account'] ?? '+62 811-0000-888',
                'connected_since' => $session['connected_since'] ?? Carbon::now()->subDays(2)->format('d M Y, H:i'),
                'error' => $session['error'] ?? null,
                'qr_code' => $session['qr_code'] ?? null,
            ];
        }

        try {
            $response = Http::timeout(5)
                ->withToken($this->getServiceToken())
                ->get("{$this->getServiceUrl()}/status");

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => self::STATUS_ERROR,
                'error' => 'WhatsApp Service responded with HTTP ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => self::STATUS_DISCONNECTED,
                'error' => 'Cannot reach WhatsApp service: ' . $e->getMessage(),
            ];
        }
    }

    public function isConnected(): bool
    {
        $status = $this->getStatus();
        return ($status['status'] ?? '') === self::STATUS_CONNECTED;
    }

    public function connect(): array
    {
        if ($this->isMockMode()) {
            // Generate a demo QR code data URL (SVG encoded)
            $qrSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="220" height="220"><rect width="100" height="100" fill="#ffffff"/><rect x="10" y="10" width="20" height="20" fill="#1e293b"/><rect x="70" y="10" width="20" height="20" fill="#1e293b"/><rect x="10" y="70" width="20" height="20" fill="#1e293b"/><rect x="40" y="40" width="20" height="20" fill="#22c55e"/><circle cx="50" cy="50" r="6" fill="#ffffff"/><rect x="40" y="15" width="10" height="10" fill="#1e293b"/><rect x="15" y="40" width="10" height="10" fill="#1e293b"/><rect x="70" y="70" width="15" height="15" fill="#1e293b"/><rect x="50" y="75" width="10" height="10" fill="#1e293b"/></svg>';
            $qrDataUri = 'data:image/svg+xml;utf8,' . rawurlencode($qrSvg);

            $session = [
                'status' => self::STATUS_CONNECTED,
                'account' => '+62 811-0000-888',
                'connected_since' => Carbon::now()->format('d M Y, H:i'),
                'qr_code' => $qrDataUri,
                'error' => null,
            ];
            $this->saveSessionData($session);

            return $session;
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->getServiceToken())
                ->post("{$this->getServiceUrl()}/session/start");

            return $response->json() ?? ['status' => self::STATUS_CONNECTING];
        } catch (\Throwable $e) {
            return [
                'status' => self::STATUS_ERROR,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function disconnect(): array
    {
        if ($this->isMockMode()) {
            $session = [
                'status' => self::STATUS_DISCONNECTED,
                'account' => null,
                'connected_since' => null,
                'qr_code' => null,
                'error' => null,
            ];
            $this->saveSessionData($session);

            return $session;
        }

        try {
            $response = Http::timeout(5)
                ->withToken($this->getServiceToken())
                ->post("{$this->getServiceUrl()}/session/disconnect");

            return $response->json() ?? ['status' => self::STATUS_DISCONNECTED];
        } catch (\Throwable $e) {
            return [
                'status' => self::STATUS_DISCONNECTED,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function normalizePhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-digit characters
        $digits = preg_replace('/[^\d]/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        // Indonesian formatting
        if (str_starts_with($digits, '08')) {
            $digits = '628' . substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '628' . substr($digits, 1);
        }

        // Must be at least 10 digits and not exceed 16 digits
        if (strlen($digits) < 10 || strlen($digits) > 16) {
            return null;
        }

        return $digits;
    }

    public function formatTicketMessage(string $type, Ticket $ticket, array $params = []): string
    {
        $appUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
        $ticketUrl = "{$appUrl}/tickets/{$ticket->ticket_number}";
        $appName = config('app.name', 'Helpdesk System');

        return match ($type) {
            'ticket_created' => "*[{$appName}] Tiket Baru Dibuat*\n\n" .
                "Tiket: *#{$ticket->ticket_number}*\n" .
                "Subjek: {$ticket->subject}\n" .
                "Prioritas: " . ucfirst($ticket->priority) . "\n\n" .
                "Tiket Anda telah berhasil didaftarkan ke sistem dan sedang menunggu penanganan.\n\n" .
                "Lihat Detail Tiket:\n{$ticketUrl}",

            'ticket_assigned' => "*[{$appName}] Penugasan Tiket*\n\n" .
                "Tiket: *#{$ticket->ticket_number}*\n" .
                "Subjek: {$ticket->subject}\n" .
                "Prioritas: " . ucfirst($ticket->priority) . "\n\n" .
                "Tiket ini telah ditugaskan kepada Anda untuk ditindaklanjuti.\n\n" .
                "Buka Tiket:\n{$ticketUrl}",

            'reply_added' => "*[{$appName}] Balasan Tiket Baru*\n\n" .
                "Tiket: *#{$ticket->ticket_number}*\n" .
                "Subjek: {$ticket->subject}\n" .
                "Dari: " . ($params['author_name'] ?? 'Petugas') . "\n\n" .
                "\"" . ($params['snippet'] ?? 'Ada balasan baru pada tiket Anda.') . "\"\n\n" .
                "Balas Tiket:\n{$ticketUrl}",

            'status_changed' => "*[{$appName}] Perubahan Status Tiket*\n\n" .
                "Tiket: *#{$ticket->ticket_number}*\n" .
                "Subjek: {$ticket->subject}\n" .
                "Status Baru: *" . strtoupper(str_replace('_', ' ', $ticket->status)) . "*\n\n" .
                "Lihat Progres Tiket:\n{$ticketUrl}",

            'priority_changed' => "*[{$appName}] Perubahan Prioritas Tiket*\n\n" .
                "Tiket: *#{$ticket->ticket_number}*\n" .
                "Prioritas Baru: *" . ucfirst($ticket->priority) . "*\n" .
                "Target SLA: " . ($ticket->sla_due_at ? $ticket->sla_due_at->format('d M H:i') : '-') . "\n\n" .
                "Periksa Tiket:\n{$ticketUrl}",

            default => "*[{$appName}] Notifikasi Tiket*\n\n" .
                "Tiket: *#{$ticket->ticket_number}*\n" .
                "Subjek: {$ticket->subject}\n\n" .
                "Terdapat pembaruan pada tiket ini.\n\n" .
                "Buka Tiket:\n{$ticketUrl}",
        };
    }

    public function sendMessage(string $phone, string $message, ?Ticket $ticket = null, ?int $userId = null, string $type = 'general'): WhatsAppMessage
    {
        $normalizedPhone = $this->normalizePhoneNumber($phone);

        // Record initial message log
        $log = WhatsAppMessage::create([
            'user_id' => $userId,
            'ticket_id' => $ticket?->id,
            'phone' => $normalizedPhone ?? $phone,
            'message' => $message,
            'notification_type' => $type,
            'status' => WhatsAppMessage::STATUS_PENDING,
        ]);

        if (!$normalizedPhone) {
            $log->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'failed_at' => Carbon::now(),
                'error_message' => 'Invalid phone number format.',
            ]);
            return $log;
        }

        if (!$this->isConnected()) {
            $log->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'failed_at' => Carbon::now(),
                'error_message' => 'WhatsApp service is not connected.',
            ]);
            return $log;
        }

        // Send via Mock Mode or External HTTP API
        if ($this->isMockMode()) {
            $log->update([
                'status' => WhatsAppMessage::STATUS_SENT,
                'sent_at' => Carbon::now(),
            ]);
            Log::info("WhatsApp (Mock) sent to {$normalizedPhone}: " . substr($message, 0, 80));
            return $log;
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->getServiceToken())
                ->post("{$this->getServiceUrl()}/message/send", [
                    'phone' => $normalizedPhone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                $log->update([
                    'status' => WhatsAppMessage::STATUS_SENT,
                    'sent_at' => Carbon::now(),
                ]);
            } else {
                $log->update([
                    'status' => WhatsAppMessage::STATUS_FAILED,
                    'failed_at' => Carbon::now(),
                    'error_message' => 'WhatsApp Service HTTP ' . $response->status() . ': ' . $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            $log->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'failed_at' => Carbon::now(),
                'error_message' => $e->getMessage(),
            ]);
        }

        return $log;
    }
}
