@extends('layouts.app')

@section('title', auth()->user()->isAdmin() ? 'Administrator Dashboard' : 'Supervisor Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ auth()->user()->isAdmin() ? 'Administrator Executive Overview' : 'Department Supervisor Overview' }}
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                Monitoring system metrics, SLA adherence, and agent ticket distribution.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Reports</span>
            </a>
            <a href="{{ route('tickets.create') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>New Ticket</span>
            </a>
        </div>
    </div>

    <!-- Stat Metric Grid (6 Cards) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Tickets</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_tickets'] }}</p>
        </div>

        <div class="bg-white p-3.5 rounded-xl border border-blue-200 bg-blue-50/20 shadow-sm">
            <p class="text-[11px] font-semibold text-blue-700 uppercase tracking-wider">Open</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">{{ $stats['open'] }}</p>
        </div>

        <div class="bg-white p-3.5 rounded-xl border border-amber-200 bg-amber-50/20 shadow-sm">
            <p class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider">In Progress</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ $stats['in_progress'] }}</p>
        </div>

        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider">Unassigned</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['unassigned'] }}</p>
        </div>

        <div class="bg-white p-3.5 rounded-xl border border-rose-200 bg-rose-50/30 shadow-sm">
            <p class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider flex items-center gap-1">
                <span>Overdue SLA</span>
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
            </p>
            <p class="text-2xl font-bold text-rose-700 mt-1">{{ $stats['overdue'] }}</p>
        </div>

        <div class="bg-white p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/20 shadow-sm">
            <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Resolved Today</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $stats['resolved_today'] }}</p>
        </div>
    </div>

    <!-- Analytics Section: Departments, Categories, Agent Workload -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Active by Department -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Tickets by Department</span>
                <span class="text-xs text-slate-400 font-normal">Active</span>
            </h2>
            <div class="space-y-3">
                @foreach($departmentsData as $dept)
                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>{{ $dept->name }}</span>
                        <span class="font-bold text-slate-900">{{ $dept->tickets_count }}</span>
                    </div>
                    @php $pct = $stats['total_tickets'] > 0 ? min(100, round(($dept->tickets_count / max(1, $stats['total_tickets'])) * 100)) : 0; @endphp
                    <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top Categories -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Top Ticket Categories</span>
                <span class="text-xs text-slate-400 font-normal">Volume</span>
            </h2>
            <div class="space-y-3">
                @foreach($categoriesData as $cat)
                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span class="truncate pr-2">{{ $cat->name }}</span>
                        <span class="font-bold text-slate-900">{{ $cat->tickets_count }}</span>
                    </div>
                    @php $pct = $stats['total_tickets'] > 0 ? min(100, round(($cat->tickets_count / max(1, $stats['total_tickets'])) * 100)) : 0; @endphp
                    <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Agent Workload Distribution -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Agent Workload</span>
                <span class="text-xs text-slate-400 font-normal">Active Assigned</span>
            </h2>
            <div class="space-y-3">
                @foreach($agents as $agent)
                <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50 last:border-0">
                    <div class="min-w-0 pr-2">
                        <p class="font-semibold text-slate-800 truncate">{{ $agent->name }}</p>
                        <p class="text-[11px] text-slate-500">{{ $agent->department?->name ?? 'All' }}</p>
                    </div>
                    <div class="shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $agent->assigned_tickets_count > 3 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800' }}">
                            {{ $agent->assigned_tickets_count }} tickets
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Tickets Table & Audit Activity Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Tickets (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Latest Tickets</h2>
                    <p class="text-xs text-slate-500">Recently submitted or updated tickets across the organization.</p>
                </div>
                <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View All Tickets &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Ticket</th>
                            <th class="px-5 py-3">Requester</th>
                            <th class="px-5 py-3">Priority</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Assignee</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentTickets as $ticket)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('tickets.show', $ticket) }}" class="font-semibold text-blue-600 hover:text-blue-800 block">
                                    {{ $ticket->ticket_number }}
                                </a>
                                <span class="text-xs text-slate-700 font-medium line-clamp-1 mt-0.5">{{ $ticket->subject }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                <div>{{ $ticket->user->name }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $ticket->department->name }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border {{ $ticket->getPriorityBadgeClass() }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border {{ $ticket->getStatusBadgeClass() }}">
                                    {{ $ticket->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                @if($ticket->assignedAgent)
                                <span class="font-medium text-slate-800">{{ $ticket->assignedAgent->name }}</span>
                                @else
                                <span class="text-slate-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 transition">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Activity Feed (1 col) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Activity & Audit Trail</span>
                <span class="text-xs font-normal text-slate-500">Live logs</span>
            </h2>

            <div class="space-y-4">
                @foreach($recentActivities as $act)
                <div class="flex gap-3 text-xs">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5 {{ $act->getIconColor() }}">
                        <span class="text-[10px] font-bold">&bull;</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-slate-800 font-medium leading-tight">
                            {{ $act->description }}
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            <a href="{{ route('tickets.show', $act->ticket) }}" class="font-semibold text-blue-600 hover:underline">{{ $act->ticket->ticket_number }}</a> &bull; {{ $act->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
