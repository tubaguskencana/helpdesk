@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">User Management</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manage employee accounts, assign roles, and configure departmental access.</p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add New User</span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search User</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, title..."
                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Role</label>
                <select name="role" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">All Roles</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Requester (User)</option>
                    <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Support Agent</option>
                    <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-sm transition">
                    Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="py-1.5 px-3 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-600 text-xs font-semibold transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                    {{ substr($u->name, 0, 2) }}
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-900 block leading-tight">{{ $u->name }}</span>
                                    <span class="text-xs text-slate-500">{{ $u->email }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border {{ $u->getRoleBadgeClass() }}">
                                {{ $u->getRoleDisplayName() }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5 text-xs text-slate-600">
                            {{ $u->department?->name ?? 'None / Global' }}
                        </td>

                        <td class="px-5 py-3.5 text-xs text-slate-600">
                            <div>{{ $u->job_title ?? '-' }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $u->phone ?? '-' }}</div>
                        </td>

                        <td class="px-5 py-3.5">
                            @if($u->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                Inactive
                            </span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2 text-xs">
                                <a href="{{ route('admin.users.edit', $u) }}" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition">
                                    Edit
                                </a>

                                @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle', $u) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-2.5 py-1 rounded {{ $u->is_active ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} font-medium transition">
                                        {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
