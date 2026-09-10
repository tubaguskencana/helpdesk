@extends('layouts.app')

@section('title', 'Ticket Reports & Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Reports & SLA Analytics</h1>
            <p class="text-sm text-slate-500 mt-0.5">Audit support metrics, resolution rates, and export data for performance reporting.</p>
        </div>
        <div>
            <a href="{{ route('reports.export', request()->query()) }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export CSV Report</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            @if(auth()->user()->isAdmin())
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Department</label>
                <select name="department_id" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-sm transition">
                    Apply Filter
                </button>
                <a href="{{ route('reports.index') }}" class="py-1.5 px-3 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-600 text-xs font-semibold transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Tickets (Period)</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ $totalTickets }}</p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-emerald-200 bg-emerald-50/20 shadow-sm">
            <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Resolved Tickets</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $resolvedCount }}</p>
        </div>

        <div class="bg-white p-4 rounded-xl border border-rose-200 bg-rose-50/20 shadow-sm">
            <p class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Overdue SLA Target</p>
            <p class="text-3xl font-bold text-rose-700 mt-1">{{ $overdueCount }}</p>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Ticket</th>
                        <th class="px-5 py-3">Subject</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Requester</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Assigned To</th>
                        <th class="px-5 py-3">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($tickets as $t)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-5 py-3 whitespace-nowrap font-mono text-xs font-bold text-blue-600">
                            <a href="{{ route('tickets.show', $t) }}" class="hover:underline">{{ $t->ticket_number }}</a>
                        </td>
                        <td class="px-5 py-3 max-w-xs truncate font-medium text-slate-800">
                            {{ $t->subject }}
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-600">
                            {{ $t->department->name }}
                        </td>
                        <td class="px-5 py-3 text-xs font-medium text-slate-800">
                            {{ $t->user->name }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $t->getPriorityBadgeClass() }}">
                                {{ ucfirst($t->priority) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $t->getStatusBadgeClass() }}">
                                {{ $t->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-600">
                            {{ $t->assignedAgent?->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-xs text-slate-500">
                            {{ $t->created_at->format('Y-m-d H:i') }}
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
    </div>
</div>
@endsection
