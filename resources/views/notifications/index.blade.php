@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Notifications
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Stay updated on all activity regarding your tickets, assignments, and replies.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($unreadCount > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Mark All as Read ({{ $unreadCount }})
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('notifications.index') }}" 
           class="px-3.5 py-1.5 text-sm font-medium rounded-lg transition {{ !request()->has('filter') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            All Notifications
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-medium rounded-lg transition {{ request()->get('filter') === 'unread' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            <span>Unread</span>
            @if($unreadCount > 0)
            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ request()->get('filter') === 'unread' ? 'bg-blue-800 text-white' : 'bg-rose-100 text-rose-700' }}">
                {{ $unreadCount }}
            </span>
            @endif
        </a>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($notifications as $notification)
        <a href="{{ route('notifications.read', $notification) }}" 
           class="block p-4 sm:p-5 transition hover:bg-slate-50 {{ !$notification->isRead() ? 'bg-blue-50/40' : '' }}">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $notification->getIconColorClass() }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold text-slate-900 truncate">
                            {{ $notification->title }}
                        </h2>
                        <span class="text-xs text-slate-600 shrink-0">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed line-clamp-2">
                        {{ $notification->message }}
                    </p>
                </div>

                @if(!$notification->isRead())
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0 mt-2" title="Unread"></span>
                @endif
            </div>
        </a>
        @empty
        <div class="p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-700">No notifications found</h3>
            <p class="text-xs text-slate-500 mt-1">You're all caught up! New updates will appear here.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection
