<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Helpdesk System') - {{ config('app.name', 'Helpdesk') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts and Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col" x-data="{ sidebarOpen: false }">

    <!-- App Wrapper -->
    <div class="min-h-full flex flex-col">
        
        <!-- Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Left: Mobile Hamburger & Brand -->
                    <div class="flex items-center">
                        <button type="button" 
                                @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 rounded-md text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 mr-2"
                                aria-label="Toggle sidebar">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 text-lg leading-tight block tracking-tight">Helpdesk</span>
                                <span class="text-xs text-slate-600 block">Ticketing System</span>
                            </div>
                        </a>
                    </div>

                    <!-- Right: Quick Create & User Profile Menu -->
                    <div class="flex items-center gap-3">
                        <a href="{{ route('tickets.create') }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>New Ticket</span>
                        </a>

                        <!-- Notification Bell Dropdown -->
                        <div class="relative" 
                             x-data="{ 
                                 open: false, 
                                 unreadCount: {{ auth()->check() ? auth()->user()->unreadInAppNotifications()->count() : 0 }},
                                 notifications: [],
                                 loading: false,
                                 init() {
                                     // Periodically refresh unread count every 30 seconds
                                     setInterval(() => {
                                         fetch('{{ route('notifications.unread-count') }}')
                                             .then(res => res.json())
                                             .then(data => { this.unreadCount = data.unread_count; })
                                             .catch(() => {});
                                     }, 30000);
                                 },
                                 loadNotifications() {
                                     this.loading = true;
                                     fetch('{{ route('notifications.recent') }}')
                                         .then(res => res.json())
                                         .then(data => {
                                             this.unreadCount = data.unread_count;
                                             this.notifications = data.notifications;
                                             this.loading = false;
                                         })
                                         .catch(() => { this.loading = false; });
                                 },
                                 toggle() {
                                     this.open = !this.open;
                                     if (this.open) {
                                         this.loadNotifications();
                                     }
                                 },
                                 markAllRead() {
                                     fetch('{{ route('notifications.mark-all-read') }}', {
                                         method: 'POST',
                                         headers: {
                                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                             'Accept': 'application/json'
                                         }
                                     }).then(() => {
                                         this.unreadCount = 0;
                                         this.notifications.forEach(n => n.read = true);
                                     });
                                 }
                             }" 
                             @click.outside="open = false">
                            <button @click="toggle()" 
                                    type="button"
                                    class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition focus:outline-none"
                                    title="Notifications">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white ring-2 ring-white"
                                          x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                                </template>
                            </button>

                            <!-- Dropdown Window -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 sm:w-96 rounded-xl bg-white shadow-xl ring-1 ring-black/5 z-50 divide-y divide-slate-100 overflow-hidden"
                                 style="display: none;">
                                
                                <div class="px-4 py-3 flex items-center justify-between bg-slate-50/70">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-slate-800">Notifications</h3>
                                        <template x-if="unreadCount > 0">
                                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700" x-text="unreadCount + ' new'"></span>
                                        </template>
                                    </div>
                                    <template x-if="unreadCount > 0">
                                        <button @click="markAllRead()" 
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                            Mark all read
                                        </button>
                                    </template>
                                </div>

                                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                    <!-- Loading State -->
                                    <template x-if="loading">
                                        <div class="p-6 text-center text-slate-400 text-xs flex items-center justify-center gap-2">
                                            <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Loading updates...
                                        </div>
                                    </template>

                                    <!-- Notifications List -->
                                    <template x-if="!loading && notifications.length > 0">
                                        <div>
                                            <template x-for="item in notifications" :key="item.id">
                                                <a :href="item.url" 
                                                   class="block px-4 py-3 hover:bg-slate-50 transition"
                                                   :class="!item.read ? 'bg-blue-50/30' : ''">
                                                    <div class="flex items-start gap-3">
                                                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                                             :class="item.icon_class">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center justify-between gap-1">
                                                                <p class="text-xs font-semibold text-slate-800 truncate" x-text="item.title"></p>
                                                                <span class="text-[10px] text-slate-600 shrink-0" x-text="item.created_at"></span>
                                                            </div>
                                                            <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                                                        </div>
                                                        <template x-if="!item.read">
                                                            <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0 mt-1"></span>
                                                        </template>
                                                    </div>
                                                </a>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Empty State -->
                                    <template x-if="!loading && notifications.length === 0">
                                        <div class="p-8 text-center">
                                            <p class="text-xs text-slate-500">No recent notifications</p>
                                        </div>
                                    </template>
                                </div>

                                <div class="p-2.5 bg-slate-50/70 text-center">
                                    <a href="{{ route('notifications.index') }}" 
                                       class="text-xs font-medium text-blue-600 hover:text-blue-800 hover:underline block">
                                        View all notifications &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative ml-2" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" 
                                    class="flex items-center gap-2.5 p-1.5 rounded-lg text-slate-700 hover:bg-slate-100 transition focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center font-semibold text-xs text-slate-700">
                                    {{ substr(auth()->user()->name, 0, 2) }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <span class="text-sm font-medium text-slate-800 block leading-none">{{ auth()->user()->name }}</span>
                                    <span class="text-[11px] text-slate-600 block mt-1 capitalize">{{ auth()->user()->getRoleDisplayName() }}</span>
                                </div>
                                <svg class="w-4 h-4 text-slate-500 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-50 divide-y divide-slate-100"
                                 style="display: none;">
                                <div class="px-4 py-2.5">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-600 truncate">{{ auth()->user()->email }}</p>
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ auth()->user()->getRoleBadgeClass() }}">
                                            {{ auth()->user()->getRoleDisplayName() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="py-1">
                                    <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        My Profile
                                    </a>
                                </div>

                                <div class="py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body: Sidebar + Content -->
        <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex gap-6">

            <!-- Sidebar Navigation -->
            <aside :class="sidebarOpen ? 'block' : 'hidden'" 
                   class="lg:block w-64 shrink-0 fixed lg:static inset-y-0 left-0 z-40 bg-white lg:bg-transparent p-4 lg:p-0 border-r lg:border-r-0 border-slate-200 overflow-y-auto">
                
                <!-- Mobile close button -->
                <div class="flex lg:hidden justify-between items-center mb-4 pb-2 border-b border-slate-200">
                    <span class="font-bold text-slate-800">Navigation Menu</span>
                    <button @click="sidebarOpen = false" class="p-1 rounded-md text-slate-500 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-6">
                    <!-- General Navigation -->
                    <div>
                        <p class="px-3 text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Main</p>
                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span>Dashboard</span>
                            </a>

                            <a href="{{ route('tickets.index') }}" 
                               class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('tickets.*') && !request()->has('tab') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 {{ request()->routeIs('tickets.*') && !request()->has('tab') ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                    <span>All Tickets</span>
                                </div>
                            </a>

                            <a href="{{ route('tickets.index', ['tab' => 'my_tickets']) }}" 
                               class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->get('tab') === 'my_tickets' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 {{ request()->get('tab') === 'my_tickets' ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>{{ auth()->user()->isRequester() ? 'My Tickets' : 'Assigned to Me' }}</span>
                                </div>
                            </a>

                            @if(auth()->user()->isStaff())
                            <a href="{{ route('tickets.index', ['tab' => 'unassigned']) }}" 
                               class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->get('tab') === 'unassigned' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 {{ request()->get('tab') === 'unassigned' ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Unassigned</span>
                                </div>
                            </a>

                            <a href="{{ route('reports.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-5 h-5 {{ request()->routeIs('reports.*') ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span>Reports & Export</span>
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Administration Section -->
                    @if(auth()->user()->isAdmin())
                    <div>
                        <p class="px-3 text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Administration</p>
                        <div class="space-y-1">
                            <a href="{{ route('admin.users.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Users</span>
                            </a>

                            <a href="{{ route('admin.departments.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.departments.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.departments.*') ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Departments</span>
                            </a>

                            <a href="{{ route('admin.categories.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.categories.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.categories.*') ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span>Categories</span>
                            </a>

                            <a href="{{ route('admin.sla.index') }}" 
                                class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.sla.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.sla.*') ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>SLA Settings</span>
                            </a>

                            <a href="{{ route('admin.notifications.whatsapp.index') }}" 
                                class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.notifications.whatsapp.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.notifications.whatsapp.*') ? 'text-emerald-600' : 'text-slate-500' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.075-2.07-.497-1.748-.727-2.859-2.518-2.946-2.634-.087-.116-.708-.941-.708-1.793s.448-1.272.607-1.446c.159-.175.347-.217.463-.217l.332.007c.101.005.246-.038.376.275.145.348.492 1.201.535 1.289.043.087.072.189.014.305-.058.116-.087.188-.173.289l-.26.304c-.087.087-.174.188-.073.362.101.174.449.74 1.006 1.236.719.641 1.325.84 1.513.927.188.087.289.072.391-.043.101-.116.435-.508.55-.682.116-.174.232-.145.39-.087.159.058 1.013.478 1.186.565.174.087.289.13.332.203.043.072.043.42-.101.825z"/>
                                </svg>
                                <span>WhatsApp Integration</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </nav>

                <!-- Help Desk Quick Status Card in Sidebar -->
                <div class="mt-8 p-3.5 bg-slate-100 rounded-lg border border-slate-200">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-semibold text-slate-700">System Online</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Operating hours: Mon - Fri (08:00 - 17:00). SLA monitors in real time.
                    </p>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 min-w-0">
                
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
                    <div class="flex items-center gap-2 mb-1.5 font-semibold">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Please correct the errors below:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 pl-2">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @auth
    <script>
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.debug('Service Worker registration skipped/failed: ', err);
                });
            });
        }
    </script>
    @endauth

    @stack('scripts')
</body>
</html>
