@extends('layouts.app')

@section('title', 'SLA Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-200">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Service Level Agreement (SLA) Settings</h1>
        <p class="text-sm text-slate-500 mt-0.5">Configure target response times and maximum resolution hours for each ticket priority level.</p>
    </div>

    <!-- Explanation Box -->
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 leading-relaxed flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="font-bold mb-1">How SLA Deadlines Work in Helpdesk:</p>
            <p>When a ticket is created, its SLA target resolution deadline is automatically computed from the priority setting below. Tickets approaching less than 4 hours remaining will show an amber warning badge, while overdue tickets are highlighted in red across agent dashboards.</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.sla.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="divide-y divide-slate-100">
                @php
                    $priorities = [
                        'urgent' => ['label' => 'Urgent Priority', 'desc' => 'Severe system downtime affecting critical business operations.', 'badge' => 'bg-rose-100 text-rose-800 border-rose-200'],
                        'high' => ['label' => 'High Priority', 'desc' => 'Major functionality impaired with no viable immediate workaround.', 'badge' => 'bg-orange-100 text-orange-800 border-orange-200'],
                        'medium' => ['label' => 'Medium Priority', 'desc' => 'Standard business workflow requests or partial functional glitches.', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200'],
                        'low' => ['label' => 'Low Priority', 'desc' => 'General inquiries, minor cosmetic requests, or general maintenance.', 'badge' => 'bg-slate-100 text-slate-800 border-slate-200'],
                    ];
                @endphp

                @foreach($priorities as $key => $meta)
                @php $sla = $settings[$key] ?? null; @endphp
                <div class="py-5 first:pt-0 last:pb-0 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold border {{ $meta['badge'] }}">
                                {{ $meta['label'] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ $meta['desc'] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 md:col-span-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">First Response Target (Hours)</label>
                            <input type="number" min="1" max="500" 
                                   name="sla[{{ $key }}][first_response_hours]" 
                                   value="{{ old("sla.{$key}.first_response_hours", $sla?->first_response_hours ?? 8) }}" 
                                   required
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Resolution Target (Hours)</label>
                            <input type="number" min="1" max="1000" 
                                   name="sla[{{ $key }}][resolution_hours]" 
                                   value="{{ old("sla.{$key}.resolution_hours", $sla?->resolution_hours ?? 48) }}" 
                                   required
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pt-5 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
                    Save SLA Configurations
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
