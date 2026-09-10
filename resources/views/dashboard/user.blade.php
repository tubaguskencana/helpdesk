@extends('layouts.app')

@section('title', 'Requester Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header with Quick Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Requester Dashboard</h1>
            <p class="text-sm text-slate-500 mt-0.5">Welcome back, {{ auth()->user()->name }}. Track your ongoing requests and support status.</p>
        </div>
        <div>
            <a href="{{ route('tickets.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Submit Support Ticket</span>
            </a>
        </div>
    </div>

    <!-- Stat Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Open Tickets -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Open</p>
                <p class="text-2xl font-bold text-slate-900 leading-tight">{{ $stats['open_tickets'] }}</p>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">In Progress</p>
                <p class="text-2xl font-bold text-slate-900 leading-tight">{{ $stats['in_progress'] }}</p>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Pending Info</p>
                <p class="text-2xl font-bold text-slate-900 leading-tight">{{ $stats['pending'] }}</p>
            </div>
        </div>

        <!-- Resolved -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Resolved</p>
                <p class="text-2xl font-bold text-slate-900 leading-tight">{{ $stats['resolved'] }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content: Recent Tickets & Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Tickets Table (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900">My Recent Tickets</h2>
                <a href="{{ route('tickets.index', ['tab' => 'my_tickets']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View All &rarr;</a>
            </div>

            @if($recentTickets->isEmpty())
            <div class="p-8 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700">No tickets submitted yet</p>
                <p class="text-xs text-slate-500 mt-1">Submit your first ticket when you need assistance.</p>
                <div class="mt-4">
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition">Create Ticket</a>
                </div>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Ticket</th>
                            <th class="px-5 py-3">Department</th>
                            <th class="px-5 py-3">Priority</th>
                            <th class="px-5 py-3">Status</th>
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
                                <div>{{ $ticket->department->name }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $ticket->category->name }}</div>
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
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 transition">
                                    Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Recent Activities Feed (1 col) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Recent Updates</span>
                <span class="text-xs font-normal text-slate-500">Your Tickets</span>
            </h2>

            @if($recentActivities->isEmpty())
            <p class="text-xs text-slate-500 text-center py-6">No recent updates.</p>
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
