<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - Helpdesk & Ticketing System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-slate-800 bg-slate-100" x-data="{ email: '{{ old('email') }}', password: '' }">

    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-600 text-white shadow-md mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Helpdesk System</h1>
            <p class="text-sm text-slate-500 mt-1">Enterprise Internal Request & Ticket Management</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-5 pb-3 border-b border-slate-100">Sign In to Your Account</h2>

            @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                {{ session('error') }}
            </div>
            @endif

            @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           x-model="email"
                           required 
                           autofocus
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('email') border-rose-500 ring-1 ring-rose-500 @enderror"
                           placeholder="name@company.com">
                    @error('email')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                    </div>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           x-model="password"
                           required 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('password') border-rose-500 ring-1 ring-rose-500 @enderror"
                           placeholder="••••••••">
                    @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-xs text-slate-600">Remember me</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition flex justify-center items-center gap-2">
                    <span>Sign In</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Quick Demo Login Switcher -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5 text-center">Quick Demo Login (Click to Fill)</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" 
                            @click="email = 'admin@helpdesk.test'; password = 'password';"
                            class="p-2 text-left rounded border border-purple-200 bg-purple-50 hover:bg-purple-100 transition text-purple-900">
                        <span class="font-bold block">Administrator</span>
                        <span class="text-[10px] text-purple-700 block truncate">admin@helpdesk.test</span>
                    </button>

                    <button type="button" 
                            @click="email = 'supervisor@helpdesk.test'; password = 'password';"
                            class="p-2 text-left rounded border border-blue-200 bg-blue-50 hover:bg-blue-100 transition text-blue-900">
                        <span class="font-bold block">Supervisor IT</span>
                        <span class="text-[10px] text-blue-700 block truncate">supervisor@helpdesk.test</span>
                    </button>

                    <button type="button" 
                            @click="email = 'agent.it@helpdesk.test'; password = 'password';"
                            class="p-2 text-left rounded border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 transition text-emerald-900">
                        <span class="font-bold block">Support Agent</span>
                        <span class="text-[10px] text-emerald-700 block truncate">agent.it@helpdesk.test</span>
                    </button>

                    <button type="button" 
                            @click="email = 'user@helpdesk.test'; password = 'password';"
                            class="p-2 text-left rounded border border-slate-200 bg-slate-50 hover:bg-slate-100 transition text-slate-800">
                        <span class="font-bold block">Requester / User</span>
                        <span class="text-[10px] text-slate-600 block truncate">user@helpdesk.test</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; {{ date('Y') }} Internal Helpdesk & Ticketing System. All rights reserved.
        </p>
    </div>

</body>
</html>
