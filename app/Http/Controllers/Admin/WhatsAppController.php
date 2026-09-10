<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

    public function index(): View
    {
        $status = $this->whatsAppService->getStatus();
        $isEnabled = $this->whatsAppService->isEnabled();
        $isMock = $this->whatsAppService->isMockMode();

        $messages = WhatsAppMessage::with(['user', 'ticket'])
            ->latest()
            ->paginate(15);

        $settings = NotificationSetting::all();

        return view('admin.notifications.whatsapp', compact('status', 'isEnabled', 'isMock', 'messages', 'settings'));
    }

    public function status(): JsonResponse
    {
        return response()->json($this->whatsAppService->getStatus());
    }

    public function connect(): JsonResponse|RedirectResponse
    {
        $result = $this->whatsAppService->connect();

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        if (($result['status'] ?? '') === WhatsAppService::STATUS_ERROR || !empty($result['error'])) {
            return back()->with('error', $result['error'] ?? 'Gagal menghubungkan ke WhatsApp service.');
        }

        return back()->with('success', 'Permintaan sesi WhatsApp berhasil dikirim. Silakan scan QR code.');
    }

    public function disconnect(): JsonResponse|RedirectResponse
    {
        $result = $this->whatsAppService->disconnect();

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        if (!empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'WhatsApp berhasil di-disconnect.');
    }

    public function testMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:500',
        ]);

        $log = $this->whatsAppService->sendMessage(
            phone: $validated['phone'],
            message: $validated['message'],
            type: 'admin_test'
        );

        if ($log->status === WhatsAppMessage::STATUS_SENT) {
            return back()->with('success', "Test WhatsApp message sent successfully to {$log->phone}.");
        }

        return back()->with('error', "Failed to send WhatsApp message: {$log->error_message}");
    }

    public function toggleSetting(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event' => 'required|string',
            'channel' => 'required|in:web,push,whatsapp',
            'enabled' => 'required|boolean',
        ]);

        $setting = NotificationSetting::firstOrCreate(
            ['event' => $validated['event']],
            [
                'web_enabled' => true,
                'push_enabled' => true,
                'whatsapp_enabled' => true,
            ]
        );

        $channelField = "{$validated['channel']}_enabled";
        $setting->update([$channelField => $validated['enabled']]);

        return back()->with('success', "Notification preference for {$validated['event']} updated.");
    }
}
