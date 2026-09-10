@extends('layouts.app')

@section('title', 'WhatsApp Integration & Notifications')

@section('content')
<div class="space-y-6" 
     x-data="{ 
         connectionStatus: '{{ $status['status'] }}',
         account: '{{ $status['account'] ?? '' }}',
         qrCode: '{{ $status['qr_code'] ?? '' }}',
         errorMessage: '{{ addslashes($status['error'] ?? '') }}',
         loading: false,
         init() {
             setInterval(() => {
                 if (this.connectionStatus !== 'CONNECTED') {
                     this.refreshStatus();
                 }
             }, 2500);
         },
         refreshStatus() {
             fetch('{{ route('admin.notifications.whatsapp.status') }}')
                 .then(res => res.json())
                 .then(data => {
                     this.connectionStatus = data.status;
                     this.account = data.account || '';
                     this.qrCode = data.qr_code || '';
                     this.errorMessage = data.error || '';
                 })
                 .catch(() => {});
         }
     }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.075-2.07-.497-1.748-.727-2.859-2.518-2.946-2.634-.087-.116-.708-.941-.708-1.793s.448-1.272.607-1.446c.159-.175.347-.217.463-.217l.332.007c.101.005.246-.038.376.275.145.348.492 1.201.535 1.289.043.087.072.189.014.305-.058.116-.087.188-.173.289l-.26.304c-.087.087-.174.188-.073.362.101.174.449.74 1.006 1.236.719.641 1.325.84 1.513.927.188.087.289.13.332.203.043.072.043.42-.101.825z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">WhatsApp Notification Integration</h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Manage WhatsApp session, connection status, QR authentication, and ticket notification dispatches.
                    </p>
                </div>
            </div>
        </div>

        @if($isMock)
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
            Simulator / Mock Mode Active
        </span>
        @endif
    </div>

    <!-- Daemon Status / Error Alert Banner -->
    <template x-if="errorMessage">
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="text-xs">
                <span class="font-bold block text-sm">Status Layanan WhatsApp Daemon:</span>
                <p class="mt-0.5 leading-relaxed" x-text="errorMessage"></p>
                <p class="mt-1 text-[11px] text-amber-700">
                    Host Target: <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono">{{ config('services.whatsapp.url', env('WHATSAPP_SERVICE_URL', 'http://localhost:3000')) }}</code>
                </p>
            </div>
        </div>
    </template>

    <!-- Top Status Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Connection Status Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Connection Status</p>
                <div class="mt-3 flex items-center gap-3">
                    <template x-if="connectionStatus === 'CONNECTED'">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Connected
                        </span>
                    </template>
                    <template x-if="connectionStatus === 'QR_REQUIRED'">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            Scan QR Code
                        </span>
                    </template>
                    <template x-if="connectionStatus === 'DISCONNECTED'">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                            Disconnected
                        </span>
                    </template>
                    <template x-if="connectionStatus === 'ERROR'">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            Error
                        </span>
                    </template>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-2">
                <form action="{{ route('admin.notifications.whatsapp.connect') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                        Connect / Pair Session
                    </button>
                </form>

                <form action="{{ route('admin.notifications.whatsapp.disconnect') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                        Disconnect
                    </button>
                </form>
            </div>
        </div>

        <!-- Account Info Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sender Account</p>
                <div class="mt-3">
                    <p class="text-lg font-bold text-slate-900" x-text="account ? account : 'No account paired'"></p>
                    <p class="text-xs text-slate-500 mt-1">
                        Connected since: <span class="font-medium text-slate-700">{{ $status['connected_since'] ?? 'Not connected' }}</span>
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-500">
                    All notifications will be delivered from this paired WhatsApp number.
                </p>
            </div>
        </div>

        <!-- Quick Test Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Send Test Notification</p>
            <form action="{{ route('admin.notifications.whatsapp.test') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <input type="text" name="phone" placeholder="Phone e.g. 08123456789" required
                           class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <input type="text" name="message" value="Hello! This is a test notification from Helpdesk System." required
                           class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" 
                        class="w-full px-3.5 py-1.5 text-xs font-semibold rounded-lg text-white bg-slate-800 hover:bg-slate-900 transition shadow-sm">
                    Dispatch Test WhatsApp
                </button>
            </form>
        </div>

    </div>

    <!-- QR Code Section (Visible when QR code exists or in mock mode) -->
    <template x-if="qrCode">
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center gap-8">
            <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-inner shrink-0 flex items-center justify-center min-w-[210px] min-h-[210px]">
                <img :src="qrCode" alt="WhatsApp QR Code" class="w-52 h-52 rounded-lg object-contain">
            </div>
            <div class="space-y-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Petunjuk Pairing WhatsApp
                </span>
                <h3 class="text-base font-bold text-slate-900">Scan Barcode untuk Menghubungkan</h3>
                <ol class="text-xs text-slate-600 space-y-1.5 list-decimal list-inside leading-relaxed">
                    <li>Buka aplikasi WhatsApp di smartphone Anda.</li>
                    <li>Ketuk menu <strong>Perangkat Tertaut (Linked Devices)</strong>.</li>
                    <li>Ketuk tombol <strong>Tautkan Perangkat (Link a Device)</strong>.</li>
                    <li>Arahkan kamera HP ke barcode di samping.</li>
                </ol>
                <div class="pt-2 flex items-center gap-2 text-xs text-emerald-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Menunggu scan barcode... Sesi akan otomatis terhubung begitu di-scan.</span>
                </div>
            </div>
        </div>
    </template>

    <!-- Loading state when QR is being prepared by Baileys -->
    <template x-if="!qrCode && (connectionStatus === 'CONNECTING' || connectionStatus === 'QR_REQUIRED')">
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-8 h-8 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin shrink-0"></div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Menyiapkan Barcode WhatsApp...</p>
                <p class="text-xs text-slate-500">Daemon Baileys sedang membuat barcode baru. Barcode akan muncul otomatis dalam beberapa detik.</p>
            </div>
        </div>
    </template>

    <!-- Global Notification Preferences Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Multi-Channel Event Matrix</h3>
                <p class="text-xs text-slate-500 mt-0.5">Toggle channel delivery preferences for each ticketing event.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Ticket Event</th>
                        <th class="px-6 py-3.5 text-center">In-App Web</th>
                        <th class="px-6 py-3.5 text-center">Web Push</th>
                        <th class="px-6 py-3.5 text-center">WhatsApp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $events = [
                            'ticket_created' => 'Ticket Created (New submission)',
                            'ticket_assigned' => 'Ticket Assigned (To support agent)',
                            'reply_added' => 'Ticket Reply (New comment or response)',
                            'status_changed' => 'Status Changed (Progress transition)',
                            'priority_changed' => 'Priority Changed (Urgent/High update)',
                        ];
                    @endphp

                    @foreach($events as $eventKey => $eventLabel)
                    @php
                        $setting = $settings->where('event', $eventKey)->first();
                        $webOn = $setting ? $setting->web_enabled : true;
                        $pushOn = $setting ? $setting->push_enabled : true;
                        $waOn = $setting ? $setting->whatsapp_enabled : true;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-medium text-slate-900">
                            {{ $eventLabel }}
                            <span class="block text-xs text-slate-600 font-mono mt-0.5">{{ $eventKey }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Enabled</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Enabled</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Enabled</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- WhatsApp Delivery Audit Logs Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">WhatsApp Delivery Logs</h3>
                <p class="text-xs text-slate-500 mt-0.5">Audit trail of recent automated WhatsApp messages dispatched by the system.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Recipient Phone</th>
                        <th class="px-6 py-3.5">Ticket</th>
                        <th class="px-6 py-3.5">Type</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($messages as $msg)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-mono text-xs text-slate-800">
                            {{ $msg->phone }}
                            @if($msg->user)
                            <span class="block text-[11px] text-slate-600 font-sans mt-0.5">{{ $msg->user->name }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($msg->ticket)
                            <a href="{{ route('tickets.show', $msg->ticket) }}" class="font-mono text-xs font-semibold text-blue-600 hover:underline">
                                #{{ $msg->ticket->ticket_number }}
                            </a>
                            <span class="block text-xs text-slate-600 truncate max-w-xs mt-0.5">{{ $msg->ticket->subject }}</span>
                            @else
                            <span class="text-xs text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 capitalize text-xs text-slate-700">
                            {{ str_replace('_', ' ', $msg->notification_type) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $msg->getStatusBadgeClass() }}">
                                {{ ucfirst($msg->status) }}
                            </span>
                            @if($msg->error_message)
                            <p class="text-[10px] text-rose-600 mt-1 max-w-xs truncate" title="{{ $msg->error_message }}">
                                {{ $msg->error_message }}
                            </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">
                            {{ $msg->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-xs text-slate-600">
                            No WhatsApp message logs recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($messages->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $messages->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
