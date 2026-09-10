@extends('layouts.app')

@section('title', 'Department Management')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, isEdit: false, formAction: '{{ route('admin.departments.store') }}', deptName: '', deptDesc: '', deptActive: true }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Departments</h1>
            <p class="text-sm text-slate-500 mt-0.5">Configure company departments handling customer and internal requests.</p>
        </div>
        <div>
            <button type="button" 
                    @click="isEdit = false; formAction = '{{ route('admin.departments.store') }}'; deptName = ''; deptDesc = ''; deptActive = true; modalOpen = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Department</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Department Name</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3 text-center">Categories</th>
                        <th class="px-5 py-3 text-center">Staff</th>
                        <th class="px-5 py-3 text-center">Total Tickets</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($departments as $dept)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-900">
                            {{ $dept->name }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-600 max-w-xs truncate">
                            {{ $dept->description ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-center text-xs font-semibold text-slate-700">
                            <span class="px-2 py-0.5 rounded bg-slate-100">{{ $dept->categories_count }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center text-xs font-semibold text-slate-700">
                            <span class="px-2 py-0.5 rounded bg-slate-100">{{ $dept->users_count }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center text-xs font-semibold text-slate-700">
                            <span class="px-2 py-0.5 rounded bg-slate-100">{{ $dept->tickets_count }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($dept->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                Inactive
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2 text-xs">
                                <button type="button" 
                                        @click="isEdit = true; formAction = '{{ route('admin.departments.update', $dept) }}'; deptName = '{{ addslashes($dept->name) }}'; deptDesc = '{{ addslashes($dept->description ?? '') }}'; deptActive = {{ $dept->is_active ? 'true' : 'false' }}; modalOpen = true"
                                        class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition">
                                    Edit
                                </button>

                                @if($dept->tickets_count === 0)
                                <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}" onsubmit="return confirm('Delete this department?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium transition">
                                        Delete
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
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="modalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4" 
         style="display: none;">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.outside="modalOpen = false">
            <h3 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100" x-text="isEdit ? 'Edit Department' : 'New Department'"></h3>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Department Name *</label>
                    <input type="text" name="name" x-model="deptName" required
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                    <textarea name="description" x-model="deptDesc" rows="3"
                              class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="deptActive" class="w-4 h-4 rounded text-blue-600">
                        <span class="text-xs font-semibold text-slate-700">Department is active</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold" x-text="isEdit ? 'Save Changes' : 'Create Department'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
