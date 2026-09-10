<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = $user->inAppNotifications();

        if ($request->get('filter') === 'unread') {
            $query->unread();
        }

        $notifications = $query->paginate(15);
        $unreadCount = $user->unreadInAppNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function recent(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user->inAppNotifications()
            ->take(5)
            ->get()
            ->map(function (Notification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'url' => route('notifications.read', $notification),
                    'read' => $notification->isRead(),
                    'created_at' => $notification->created_at->diffForHumans(),
                    'icon_class' => $notification->getIconColorClass(),
                ];
            });

        $unreadCount = $user->unreadInAppNotifications()->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'unread_count' => $user->unreadInAppNotifications()->count(),
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        $destination = $notification->url ?: route('dashboard');

        return redirect($destination);
    }

    public function markAllAsRead(): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->unreadInAppNotifications()->update(['read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
