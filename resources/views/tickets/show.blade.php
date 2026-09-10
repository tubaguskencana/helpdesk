@extends('layouts.app')

@section('title', "{$ticket->ticket_number} - {$ticket->subject}")

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('tickets.index') }}" class="font-semibold text-blue-600 hover:underline flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Tickets
            </a>
            <span class="text-slate-400">/</span>
            <span class="font-mono font-bold text-slate-700">{{ $ticket->ticket_number }}</span>
        </div>

        <div class="flex items-center gap-2">
            @php $sla = $ticket->getSlaInfo(); @endphp
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $sla['badge'] }}">
                <span class="w-2 h-2 rounded-full {{ $sla['state'] === 'overdue' ? 'bg-rose-600 animate-ping' : ($sla['state'] === 'near_deadline' ? 'bg-amber-600' : 'bg-emerald-600') }}"></span>
                <span>SLA: {{ $sla['text'] }}</span>
            </span>
        </div>
    </div>

    <!-- 2-Column Responsive Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- LEFT COLUMN: Conversation, Original Issue, Internal Notes & Reply Form (2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Ticket Subject Header & Original Description Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                            {{ $ticket->ticket_number }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $ticket->getPriorityBadgeClass() }}">
                            {{ ucfirst($ticket->priority) }} Priority
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold border {{ $ticket->getStatusBadgeClass() }}">
                            {{ $ticket->getStatusLabel() }}
                        </span>
                    </div>

                    <h1 class="text-xl font-bold text-slate-900 leading-snug">{{ $ticket->subject }}</h1>

                    <div class="flex items-center gap-2 mt-2 text-xs text-slate-500">
                        <span>Submitted by <strong class="text-slate-700">{{ $ticket->user->name }}</strong></span>
                        <span>&bull;</span>
                        <span>{{ $ticket->created_at->format('M d, Y H:i') }} ({{ $ticket->created_at->diffForHumans() }})</span>
                    </div>
                </div>

                <!-- Description Text -->
                <div class="p-6 bg-slate-50/40 text-sm text-slate-800 leading-relaxed whitespace-pre-line">
                    {{ $ticket->description }}
                </div>

                <!-- Initial Attachments (if any) -->
                @if($ticket->attachments->isNotEmpty())
                <div class="p-4 border-t border-slate-100 bg-white">
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        Attachments ({{ $ticket->attachments->count() }})
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ticket->attachments as $att)
                        <a href="{{ route('attachments.download', $att) }}" 
                           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-medium text-slate-700 transition">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span class="max-w-[150px] truncate">{{ $att->file_name }}</span>
                            <span class="text-[10px] text-slate-400">({{ $att->getFormattedSize() }})</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Conversation & Activity Timeline -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 px-1">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Conversation & Activity Timeline</h2>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                @if($replies->isEmpty())
                <div class="p-6 rounded-xl border border-dashed border-slate-200 bg-white text-center text-xs text-slate-500">
                    No replies or notes recorded yet.
                </div>
                @endif

                @foreach($replies as $reply)
                    @if($reply->is_internal)
                    <!-- INTERNAL NOTE (Highlighted Amber Box, Visible ONLY to Staff) -->
                    <div class="p-4 rounded-xl border border-amber-300 bg-amber-50/70 shadow-xs relative">
                        <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-amber-200/60">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-amber-200 text-amber-900 font-bold text-xs flex items-center justify-center">
                                    {{ substr($reply->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-amber-900">{{ $reply->user->name }}</span>
                                    <span class="text-[11px] text-amber-700 ml-1">({{ $reply->user->getRoleDisplayName() }})</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-300">
                                    <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    INTERNAL NOTE &bull; Hidden from Requester
                                </span>
                                <span class="text-[11px] text-amber-700">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="text-sm text-amber-950 whitespace-pre-line leading-relaxed pl-9">
                            {{ $reply->message }}
                        </div>

                        <!-- Attachments on note -->
                        @if($reply->attachments->isNotEmpty())
                        <div class="mt-3 pl-9 flex flex-wrap gap-2 pt-2 border-t border-amber-200/50">
                            @foreach($reply->attachments as $att)
                            <a href="{{ route('attachments.download', $att) }}" 
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-amber-100 hover:bg-amber-200 text-[11px] font-medium text-amber-900 transition border border-amber-200">
                                <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>{{ $att->file_name }}</span>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @else
                    <!-- PUBLIC REPLY (White Card with Avatar) -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-white shadow-xs">
                        <div class="flex items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full {{ $reply->user->isStaff() ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }} font-bold text-xs flex items-center justify-center">
                                    {{ substr($reply->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-900">{{ $reply->user->name }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium ml-1 border {{ $reply->user->getRoleBadgeClass() }}">
                                        {{ $reply->user->getRoleDisplayName() }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400">{{ $reply->created_at->format('M d, H:i') }} ({{ $reply->created_at->diffForHumans() }})</span>
                        </div>

                        <div class="text-sm text-slate-800 whitespace-pre-line leading-relaxed pl-10">
                            {{ $reply->message }}
                        </div>

                        <!-- Attachments on reply -->
                        @if($reply->attachments->isNotEmpty())
                        <div class="mt-3 pl-10 flex flex-wrap gap-2 pt-2 border-t border-slate-100">
                            @foreach($reply->attachments as $att)
                            <a href="{{ route('attachments.download', $att) }}" 
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-50 hover:bg-slate-100 text-xs font-medium text-slate-700 transition border border-slate-200">
                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>{{ $att->file_name }}</span>
                                <span class="text-[10px] text-slate-400">({{ $att->getFormattedSize() }})</span>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>

            <!-- REPLY / INTERNAL NOTE FORM -->
            @if(!$ticket->isResolvedOrClosed() || auth()->user()->isStaff())
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6"
                 x-data="{ isInternal: false }">
                
                @if(auth()->user()->isStaff())
                <!-- Staff Tab Switcher: Public Reply vs Internal Note -->
                <div class="flex items-center gap-2 mb-4 border-b border-slate-200 pb-2">
                    <button type="button" 
                            @click="isInternal = false" 
                            :class="!isInternal ? 'border-blue-600 text-blue-700 font-bold border-b-2' : 'text-slate-500 hover:text-slate-800'"
                            class="pb-2 px-3 text-xs uppercase tracking-wider transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        <span>Public Reply</span>
                    </button>

                    <button type="button" 
                            @click="isInternal = true" 
                            :class="isInternal ? 'border-amber-600 text-amber-800 font-bold border-b-2' : 'text-slate-500 hover:text-slate-800'"
                            class="pb-2 px-3 text-xs uppercase tracking-wider transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>🔒 Add Internal Note</span>
                    </button>
                </div>
                @else
                <h3 class="text-sm font-bold text-slate-800 mb-3">Post a Reply</h3>
                @endif

                <form method="POST" action="{{ route('tickets.replies.store', $ticket) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="is_internal" :value="isInternal ? 1 : 0">

                    <!-- Info Alert when Internal Note is selected -->
                    <div x-show="isInternal" class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>This note will only be visible to Support Agents, Supervisors, and Administrators.</span>
                    </div>

                    <div>
                        <textarea name="message" 
                                  rows="4" 
                                  required
                                  :placeholder="isInternal ? 'Write internal technical notes, observations, or handover remarks...' : 'Type your message to the requester or support team...'"
                                  :class="isInternal ? 'border-amber-300 focus:ring-amber-500 focus:border-amber-500 bg-amber-50/20' : 'border-slate-300 focus:ring-blue-500 focus:border-blue-500'"
                                  class="w-full px-3.5 py-2.5 text-sm rounded-lg border focus:ring-2 focus:outline-none transition leading-relaxed"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                        <div>
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 cursor-pointer transition">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span>Attach Files</span>
                                <input type="file" name="attachments[]" multiple class="sr-only" @change="$dispatch('file-chosen')">
                            </label>
                            <span class="text-[11px] text-slate-400 ml-2">PNG, JPG, PDF, ZIP (max 10MB)</span>
                        </div>

                        <button type="submit" 
                                :class="isInternal ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-5 py-2 rounded-lg text-white font-semibold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                            <span x-text="isInternal ? 'Save Internal Note' : 'Send Public Reply'"></span>
                        </button>
                    </div>
                </form>
            </div>
            @else
            <!-- Ticket Closed Notice for Requester -->
            <div class="p-6 rounded-xl border border-slate-200 bg-slate-100 text-center text-xs text-slate-600">
                This ticket has been marked as {{ $ticket->getStatusLabel() }}.
                @can('updateStatus', $ticket)
                <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="inline-block mt-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="open">
                    <button type="submit" class="font-bold text-blue-600 hover:underline">Click here to reopen this ticket</button>
                </form>
                @endcan
            </div>
            @endif
        </div>

        <!-- RIGHT COLUMN: Ticket Information & Staff Control Actions (1 col) -->
        <div class="space-y-6">

            <!-- Ticket Information Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Ticket Information
                </h2>

                <div class="space-y-3 text-xs">
                    <!-- Status -->
                    <div>
                        <span class="text-slate-500 block mb-1">Status</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold border {{ $ticket->getStatusBadgeClass() }}">
                            {{ $ticket->getStatusLabel() }}
                        </span>
                    </div>

                    <!-- Priority -->
                    <div>
                        <span class="text-slate-500 block mb-1">Priority</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold border {{ $ticket->getPriorityBadgeClass() }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>

                    <!-- SLA Remaining -->
                    <div>
                        <span class="text-slate-500 block mb-1">SLA Resolution Target</span>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-semibold border {{ $sla['badge'] }}">
                                <span class="w-2 h-2 rounded-full {{ $sla['state'] === 'overdue' ? 'bg-rose-600' : ($sla['state'] === 'near_deadline' ? 'bg-amber-600' : 'bg-emerald-600') }}"></span>
                                {{ $sla['text'] }}
                            </span>
                        </div>
                        @if($ticket->sla_due_at)
                        <p class="text-[11px] text-slate-400 mt-1">Due: {{ $ticket->sla_due_at->format('M d, Y H:i') }}</p>
                        @endif
                    </div>

                    <!-- Department & Category -->
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-500 block mb-0.5">Department</span>
                        <span class="font-semibold text-slate-800 block">{{ $ticket->department->name }}</span>
                        <span class="text-slate-500 block mt-2 mb-0.5">Category</span>
                        <span class="font-semibold text-slate-800 block">{{ $ticket->category->name }}</span>
                    </div>

                    <!-- Requester Details -->
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-500 block mb-1">Requester</span>
                        <p class="font-bold text-slate-800 text-sm">{{ $ticket->user->name }}</p>
                        <p class="text-slate-500 text-[11px]">{{ $ticket->user->email }}</p>
                        @if($ticket->user->phone)
                        <p class="text-slate-500 text-[11px] mt-0.5">{{ $ticket->user->phone }}</p>
                        @endif
                    </div>

                    <!-- Assigned Agent -->
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-500 block mb-1">Assigned Support Agent</span>
                        @if($ticket->assignedAgent)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-[10px]">
                                {{ substr($ticket->assignedAgent->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800">{{ $ticket->assignedAgent->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $ticket->assignedAgent->department?->name ?? 'Staff' }}</p>
                            </div>
                        </div>
                        @else
                        <span class="text-amber-700 font-semibold italic bg-amber-50 px-2 py-1 rounded border border-amber-200 block text-center">
                            Unassigned
                        </span>
                        @endif
                    </div>

                    <!-- Key Dates -->
                    <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-500 space-y-1">
                        <div class="flex justify-between">
                            <span>Opened:</span>
                            <span class="font-medium text-slate-700">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        @if($ticket->first_replied_at)
                        <div class="flex justify-between">
                            <span>First Reply:</span>
                            <span class="font-medium text-slate-700">{{ $ticket->first_replied_at->format('M d, Y H:i') }}</span>
                        </div>
                        @endif
                        @if($ticket->resolved_at)
                        <div class="flex justify-between">
                            <span>Resolved:</span>
                            <span class="font-medium text-emerald-700 font-semibold">{{ $ticket->resolved_at->format('M d, Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- STAFF CONTROLS (Only visible to Staff: Agent, Supervisor, Admin) -->
            @if(auth()->user()->isStaff())
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-5">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Staff Controls
                </h2>

                <!-- 1. Assignment Form -->
                @can('assign', $ticket)
                <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="space-y-2">
                    @csrf
                    @method('PATCH')
                    <label class="block text-xs font-semibold text-slate-700">Assign To Agent</label>
                    <div class="flex gap-2">
                        <select name="assigned_to" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Unassigned --</option>
                            @foreach($availableAgents as $agent)
                            <option value="{{ $agent->id }}" {{ $ticket->assigned_to === $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }} ({{ $agent->getRoleDisplayName() }})
                            </option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            Save
                        </button>
                    </div>
                </form>
                @endcan

                <!-- 2. Status Update Form -->
                @can('updateStatus', $ticket)
                <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="space-y-2 pt-3 border-t border-slate-100">
                    @csrf
                    @method('PATCH')
                    <label class="block text-xs font-semibold text-slate-700">Change Status</label>
                    <div class="flex gap-2">
                        <select name="status" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            Update
                        </button>
                    </div>
                </form>
                @endcan

                <!-- 3. Priority Update Form -->
                @can('updatePriority', $ticket)
                <form method="POST" action="{{ route('tickets.priority', $ticket) }}" class="space-y-2 pt-3 border-t border-slate-100">
                    @csrf
                    @method('PATCH')
                    <label class="block text-xs font-semibold text-slate-700">Change Priority</label>
                    <div class="flex gap-2">
                        <select name="priority" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            Apply
                        </button>
                    </div>
                </form>
                @endcan
            </div>
            @endif

            <!-- Requester Actions (When Resolved: Close or Reopen) -->
            @if(auth()->user()->isRequester() && $ticket->status === 'resolved')
            <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-5 space-y-3">
                <p class="text-xs font-bold text-emerald-900">Is your issue resolved?</p>
                <p class="text-[11px] text-emerald-700">Support has marked this request as resolved. You can close it or request reopening if the issue persists.</p>
                <div class="flex gap-2 pt-1">
                    <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="w-full py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            Confirm & Close
                        </button>
                    </form>
                    <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="open">
                        <button type="submit" class="w-full py-1.5 rounded-lg border border-emerald-300 bg-white hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition">
                            Reopen
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Audit Trail Activity Log -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Audit Log History
                </h2>
                <div class="space-y-3 mt-3">
                    @foreach($ticket->activities as $act)
                    <div class="text-[11px]">
                        <p class="text-slate-800 font-medium leading-snug">{{ $act->description }}</p>
                        <p class="text-slate-400 text-[10px] mt-0.5">{{ $act->created_at->format('M d, H:i') }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
