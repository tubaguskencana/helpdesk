@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Tickets</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manage, track, and filter support requests across departments.</p>
        </div>
        <div>
            <a href="{{ route('tickets.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>New Ticket</span>
            </a>
        </div>
    </div>

    <!-- Quick Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200 text-sm">
        <a href="{{ route('tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}"
           class="px-3.5 py-2 font-medium rounded-t-lg transition whitespace-nowrap flex items-center gap-2 {{ $tab === 'all' ? 'border-b-2 border-blue-600 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            <span>All Tickets</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'all' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $counts['all'] }}
            </span>
        </a>

        <a href="{{ route('tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'my_tickets'])) }}"
           class="px-3.5 py-2 font-medium rounded-t-lg transition whitespace-nowrap flex items-center gap-2 {{ $tab === 'my_tickets' ? 'border-b-2 border-blue-600 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            <span>{{ auth()->user()->isRequester() ? 'My Tickets' : 'Assigned to Me' }}</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'my_tickets' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $counts['my_tickets'] }}
            </span>
        </a>

        @if(auth()->user()->isStaff())
        <a href="{{ route('tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'unassigned'])) }}"
           class="px-3.5 py-2 font-medium rounded-t-lg transition whitespace-nowrap flex items-center gap-2 {{ $tab === 'unassigned' ? 'border-b-2 border-blue-600 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Unassigned</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'unassigned' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $counts['unassigned'] }}
            </span>
        </a>
        @endif

        <a href="{{ route('tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'open'])) }}"
           class="px-3.5 py-2 font-medium rounded-t-lg transition whitespace-nowrap flex items-center gap-2 {{ $tab === 'open' ? 'border-b-2 border-blue-600 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Open & In Progress</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'open' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $counts['open'] }}
            </span>
        </a>

        <a href="{{ route('tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'resolved'])) }}"
           class="px-3.5 py-2 font-medium rounded-t-lg transition whitespace-nowrap flex items-center gap-2 {{ $tab === 'resolved' ? 'border-b-2 border-blue-600 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Resolved</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'resolved' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $counts['resolved'] }}
            </span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('tickets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search number, subject, requester..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <!-- Priority Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Priority</label>
                <select name="priority" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Department</label>
                <select name="department_id" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-sm transition">
                    Filter
                </button>
                <a href="{{ route('tickets.index', ['tab' => $tab]) }}" class="py-1.5 px-3 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-600 text-xs font-semibold transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Ticket Table (As specified: Table layout, NOT cards) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        @if($tickets->isEmpty())
        <div class="p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-800">No tickets found</h3>
            <p class="text-xs text-slate-500 mt-1">Try adjusting your filters or search terms.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Ticket</th>
                        <th class="px-5 py-3">Subject & Category</th>
                        <th class="px-5 py-3">Requester</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Assignee</th>
                        <th class="px-5 py-3">SLA Status</th>
                        <th class="px-5 py-3 text-right">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($tickets as $ticket)
                    @php $sla = $ticket->getSlaInfo(); @endphp
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline block font-mono text-xs">
                                {{ $ticket->ticket_number }}
                            </a>
                        </td>

                        <td class="px-5 py-3.5">
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-slate-800 hover:text-blue-600 line-clamp-1 block">
                                {{ $ticket->subject }}
                            </a>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                                <span class="text-slate-600 font-medium">{{ $ticket->department->name }}</span>
                                <span>&rsaquo;</span>
                                <span>{{ $ticket->category->name }}</span>
                            </div>
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="text-xs font-semibold text-slate-800">{{ $ticket->user->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $ticket->user->department?->name ?? 'User' }}</div>
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $ticket->getPriorityBadgeClass() }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $ticket->getStatusBadgeClass() }}">
                                {{ $ticket->getStatusLabel() }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                            @if($ticket->assignedAgent)
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-semibold flex items-center justify-center text-[10px]">
                                    {{ substr($ticket->assignedAgent->name, 0, 1) }}
                                </div>
                                <span class="text-slate-700 font-medium">{{ $ticket->assignedAgent->name }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 italic">Unassigned</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium border {{ $sla['badge'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sla['state'] === 'overdue' ? 'bg-rose-600' : ($sla['state'] === 'near_deadline' ? 'bg-amber-600' : 'bg-emerald-600') }}"></span>
                                <span>{{ $sla['text'] }}</span>
                            </span>
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs text-slate-500">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50">
            {{ $tickets->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
