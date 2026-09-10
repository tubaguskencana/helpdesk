@extends('layouts.app')

@section('title', 'Category Management')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, isEdit: false, formAction: '{{ route('admin.categories.store') }}', catName: '', catDept: '{{ $departments->first()->id ?? '' }}', catDesc: '', catActive: true }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Ticket Categories</h1>
            <p class="text-sm text-slate-500 mt-0.5">Organize issues into granular categories mapped to departments.</p>
        </div>
        <div>
            <button type="button" 
                    @click="isEdit = false; formAction = '{{ route('admin.categories.store') }}'; catName = ''; catDesc = ''; catActive = true; modalOpen = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Category</span>
            </button>
        </div>
    </div>

    <!-- Filter by Department -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex items-center gap-3">
            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Filter by Department:</label>
            <select name="department_id" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Category Name</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3 text-center">Tickets</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($categories as $cat)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-900">
                            {{ $cat->name }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-700 font-medium">
                            {{ $cat->department->name }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-600 max-w-xs truncate">
                            {{ $cat->description ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-center text-xs font-semibold text-slate-700">
                            <span class="px-2 py-0.5 rounded bg-slate-100">{{ $cat->tickets_count }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($cat->is_active)
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
                                        @click="isEdit = true; formAction = '{{ route('admin.categories.update', $cat) }}'; catName = '{{ addslashes($cat->name) }}'; catDept = '{{ $cat->department_id }}'; catDesc = '{{ addslashes($cat->description ?? '') }}'; catActive = {{ $cat->is_active ? 'true' : 'false' }}; modalOpen = true"
                                        class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition">
                                    Edit
                                </button>

                                @if($cat->tickets_count === 0)
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category?')">
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

        @if($categories->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="modalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4" 
         style="display: none;">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.outside="modalOpen = false">
            <h3 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100" x-text="isEdit ? 'Edit Category' : 'New Category'"></h3>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Department *</label>
                    <select name="department_id" x-model="catDept" required
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Category Name *</label>
                    <input type="text" name="name" x-model="catName" required
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                    <textarea name="description" x-model="catDesc" rows="3"
                              class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="catActive" class="w-4 h-4 rounded text-blue-600">
                        <span class="text-xs font-semibold text-slate-700">Category is active</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold" x-text="isEdit ? 'Save Changes' : 'Create Category'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
