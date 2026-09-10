@extends('layouts.app')

@section('title', 'Submit New Ticket')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="pb-4 border-b border-slate-200">
        <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-blue-600 hover:underline flex items-center gap-1 mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Tickets
        </a>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Create Support Ticket</h1>
        <p class="text-sm text-slate-500 mt-0.5">Please provide clear details regarding your request so our team can resolve it efficiently.</p>
    </div>

    <!-- Form Card with Alpine data for dynamic category cascade -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8"
         x-data="{
            departments: {{ Js::from($departments) }},
            selectedDepartmentId: '{{ old('department_id', $departments->first()->id ?? '') }}',
            selectedCategoryId: '{{ old('category_id') }}',
            get filteredCategories() {
                const dept = this.departments.find(d => d.id == this.selectedDepartmentId);
                return dept ? dept.categories : [];
            },
            onDepartmentChange() {
                const cats = this.filteredCategories;
                this.selectedCategoryId = cats.length > 0 ? cats[0].id : '';
            },
            init() {
                if (!this.selectedCategoryId && this.filteredCategories.length > 0) {
                    this.selectedCategoryId = this.filteredCategories[0].id;
                }
            }
         }">
        
        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- 1. Department & Category Cascading Selectors -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="department_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Target Department <span class="text-rose-500">*</span>
                    </label>
                    <select id="department_id" 
                            name="department_id" 
                            x-model="selectedDepartmentId" 
                            @change="onDepartmentChange"
                            required
                            class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Category <span class="text-rose-500">*</span>
                    </label>
                    <select id="category_id" 
                            name="category_id" 
                            x-model="selectedCategoryId" 
                            required
                            class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        <template x-for="cat in filteredCategories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                    @error('category_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 2. Priority Selection -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Priority Level <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs" x-data="{ priority: '{{ old('priority', 'medium') }}' }">
                    <!-- Low -->
                    <label :class="priority === 'low' ? 'border-slate-400 bg-slate-100 ring-2 ring-slate-400' : 'border-slate-200 hover:bg-slate-50'"
                           class="p-3 rounded-lg border cursor-pointer transition flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-800">Low</span>
                            <input type="radio" name="priority" value="low" x-model="priority" class="sr-only">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        </div>
                        <span class="text-[11px] text-slate-500">Non-urgent, minor inquiries (72h SLA)</span>
                    </label>

                    <!-- Medium -->
                    <label :class="priority === 'medium' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500' : 'border-slate-200 hover:bg-slate-50'"
                           class="p-3 rounded-lg border cursor-pointer transition flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-blue-900">Medium</span>
                            <input type="radio" name="priority" value="medium" x-model="priority" class="sr-only">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        </div>
                        <span class="text-[11px] text-slate-500">Standard business requests (48h SLA)</span>
                    </label>

                    <!-- High -->
                    <label :class="priority === 'high' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500' : 'border-slate-200 hover:bg-slate-50'"
                           class="p-3 rounded-lg border cursor-pointer transition flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-orange-900">High</span>
                            <input type="radio" name="priority" value="high" x-model="priority" class="sr-only">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        </div>
                        <span class="text-[11px] text-slate-500">Disrupted operations (24h SLA)</span>
                    </label>

                    <!-- Urgent -->
                    <label :class="priority === 'urgent' ? 'border-rose-500 bg-rose-50 ring-2 ring-rose-500' : 'border-slate-200 hover:bg-slate-50'"
                           class="p-3 rounded-lg border cursor-pointer transition flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-rose-900">Urgent</span>
                            <input type="radio" name="priority" value="urgent" x-model="priority" class="sr-only">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                        </div>
                        <span class="text-[11px] text-slate-500">Critical system stoppage (8h SLA)</span>
                    </label>
                </div>
            </div>

            <!-- 3. Subject -->
            <div>
                <label for="subject" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Ticket Subject <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="subject" 
                       name="subject" 
                       value="{{ old('subject') }}"
                       required 
                       placeholder="e.g. Printer Finance Lantai 2 Macet & Paper Jam"
                       class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                @error('subject')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 4. Description -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Issue Description & Steps to Reproduce <span class="text-rose-500">*</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="6" 
                          required
                          placeholder="Describe the problem in detail. What occurred? What error message appeared? When did it start?"
                          class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none transition leading-relaxed">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 5. Attachments Upload -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Attachments (Optional)
                </label>
                <div class="border-2 border-dashed border-slate-300 hover:border-slate-400 rounded-xl p-5 text-center transition bg-slate-50/50">
                    <svg class="mx-auto h-9 w-9 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <div class="text-xs text-slate-600">
                        <label for="attachments" class="relative cursor-pointer font-semibold text-blue-600 hover:underline">
                            <span>Upload files</span>
                            <input id="attachments" name="attachments[]" type="file" multiple class="sr-only">
                        </label>
                        <span class="pl-1">or drag and drop</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, PDF, DOCX, ZIP up to 10MB each</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('tickets.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
