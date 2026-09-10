@extends('layouts.app')

@section('title', 'Agent Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Support Agent Dashboard</h1>
            <p class="text-sm text-slate-500 mt-0.5">Welcome, {{ auth()->user()->name }} ({{ auth()->user()->department?->name ?? 'General Agent' }}). Manage your workload and ticket queue.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tickets.index', ['tab' => 'unassigned']) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Unassigned Queue</span>
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

    <!-- Agent Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Assigned To Me -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Assigned to Me</p>
                <p class="text-2xl font-bold text-slate-900 leading-tight">{{ $stats['assigned_to_me'] }}</p>
            </div>
        </div>

        <!-- Unassigned In Department -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Unassigned Queue</p>
                <p class="text-2xl font-bold text-slate-900 leading-tight">{{ $stats['unassigned_dept'] }}</p>
            </div>
        </div>

        <!-- Urgent / High Priority -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">High / Urgent</p>
                <p class="text-2xl font-bold text-slate-900 leading-tight">{{ $stats['urgent_high'] }}</p>
            </div>
        </div>

        <!-- Approaching SLA -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Near SLA (&lt;4h)</p>
                <p class="text-2xl font-bold text-slate-900 leading-tight">{{ $stats['near_sla'] }}</p>
            </div>
        </div>
    </div>

    <!-- Main Workspace Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Active Assigned Tickets (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Assigned to Me Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Active Tickets Assigned to Me</h2>
                        <p class="text-xs text-slate-500">Tickets requiring your response or resolution.</p>
                    </div>
                    <a href="{{ route('tickets.index', ['tab' => 'my_tickets']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View All &rarr;</a>
                </div>

                @if($myActiveTickets->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm font-medium text-slate-700">No active assigned tickets right now</p>
                    <p class="text-xs text-slate-500 mt-1">Check the unassigned queue to claim incoming tickets.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Ticket</th>
                                <th class="px-5 py-3">Requester</th>
                                <th class="px-5 py-3">Priority</th>
                                <th class="px-5 py-3">SLA Status</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($myActiveTickets as $ticket)
                            @php $sla = $ticket->getSlaInfo(); @endphp
                            <tr class="hover:bg-slate-50/75 transition">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-semibold text-blue-600 hover:text-blue-800 block">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                    <span class="text-xs text-slate-700 font-medium line-clamp-1 mt-0.5">{{ $ticket->subject }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600">
                                    <div class="font-medium text-slate-800">{{ $ticket->user->name }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $ticket->department->name }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border {{ $ticket->getPriorityBadgeClass() }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium border {{ $sla['badge'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $sla['state'] === 'overdue' ? 'bg-rose-600' : ($sla['state'] === 'near_deadline' ? 'bg-amber-600' : 'bg-emerald-600') }}"></span>
                                        {{ $sla['text'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 px-3 py-1 rounded bg-blue-50 hover:bg-blue-100 transition">
                                        Open
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Unassigned Department Tickets -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Unassigned Department Queue</h2>
                        <p class="text-xs text-slate-500">Tickets awaiting assignment in your department.</p>
                    </div>
                    <a href="{{ route('tickets.index', ['tab' => 'unassigned']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View Queue &rarr;</a>
                </div>

                @if($unassignedTickets->isEmpty())
                <p class="p-6 text-xs text-slate-500 text-center">No unassigned tickets in queue.</p>
                @else
                <div class="divide-y divide-slate-100">
                    @foreach($unassignedTickets as $ticket)
                    <div class="p-4 hover:bg-slate-50 transition flex items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <a href="{{ route('tickets.show', $ticket) }}" class="font-semibold text-blue-600 hover:underline text-xs">
                                    {{ $ticket->ticket_number }}
                                </a>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ $ticket->getPriorityBadgeClass() }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                                <span class="text-xs text-slate-400">&bull; {{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="text-sm font-medium text-slate-800 truncate">{{ $ticket->subject }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">By {{ $ticket->user->name }} ({{ $ticket->department->name }} &bull; {{ $ticket->category->name }})</p>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                                    Claim Ticket
                                </button>
                            </form>
                            <a href="{{ route('tickets.show', $ticket) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                View
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar: Recent Activities Stream (1 Col) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Recent Department Activity</span>
                <span class="text-xs font-normal text-slate-500">Live feed</span>
            </h2>

            @if($recentActivities->isEmpty())
            <p class="text-xs text-slate-500 text-center py-6">No recent department updates.</p>
            @else
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
                            Ticket <a href="{{ route('tickets.show', $act->ticket) }}" class="font-semibold text-blue-600 hover:underline">{{ $act->ticket->ticket_number }}</a> &bull; {{ $act->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
