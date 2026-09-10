<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isRequester()) {
            return $this->userDashboard($user);
        }

        if ($user->isAgent()) {
            return $this->agentDashboard($user);
        }

        return $this->adminDashboard($user);
    }

    protected function userDashboard(User $user): View
    {
        $myTickets = Ticket::where('user_id', $user->id);

        $stats = [
            'open_tickets' => (clone $myTickets)->where('status', Ticket::STATUS_OPEN)->count(),
            'in_progress' => (clone $myTickets)->where('status', Ticket::STATUS_IN_PROGRESS)->count(),
            'pending' => (clone $myTickets)->where('status', Ticket::STATUS_PENDING)->count(),
            'resolved' => (clone $myTickets)->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])->count(),
        ];

        $recentTickets = (clone $myTickets)
            ->with(['department', 'category', 'assignedAgent'])
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = TicketActivity::whereHas('ticket', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['ticket', 'user'])
            ->where('activity_type', '!=', 'internal_note_added')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.user', compact('stats', 'recentTickets', 'recentActivities'));
    }

    protected function agentDashboard(User $user): View
    {
        $deptId = $user->department_id;

        $assignedToMeQuery = Ticket::where('assigned_to', $user->id);

        $stats = [
            'assigned_to_me' => (clone $assignedToMeQuery)->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])->count(),
            'unassigned_dept' => Ticket::whereNull('assigned_to')
                ->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
                ->when($deptId, fn($q) => $q->where('department_id', $deptId))
                ->count(),
            'urgent_high' => (clone $assignedToMeQuery)
                ->whereIn('priority', [Ticket::PRIORITY_HIGH, Ticket::PRIORITY_URGENT])
                ->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
                ->count(),
            'near_sla' => (clone $assignedToMeQuery)
                ->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<=', Carbon::now()->addHours(4))
                ->count(),
        ];

        $myActiveTickets = (clone $assignedToMeQuery)
            ->with(['user', 'department', 'category'])
            ->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->take(6)
            ->get();

        $unassignedTickets = Ticket::whereNull('assigned_to')
            ->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
            ->when($deptId, fn($q) => $q->where('department_id', $deptId))
            ->with(['user', 'department', 'category'])
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = TicketActivity::whereHas('ticket', function ($q) use ($user, $deptId) {
            $q->where('assigned_to', $user->id)
                ->orWhere('department_id', $deptId);
        })
            ->with(['ticket', 'user'])
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.agent', compact('stats', 'myActiveTickets', 'unassignedTickets', 'recentActivities'));
    }

    protected function adminDashboard(User $user): View
    {
        $deptScope = $user->isSupervisor() ? $user->department_id : null;

        $ticketQuery = Ticket::query()->when($deptScope, fn($q) => $q->where('department_id', $deptScope));

        $stats = [
            'total_tickets' => (clone $ticketQuery)->count(),
            'open' => (clone $ticketQuery)->where('status', Ticket::STATUS_OPEN)->count(),
            'in_progress' => (clone $ticketQuery)->where('status', Ticket::STATUS_IN_PROGRESS)->count(),
            'resolved_today' => (clone $ticketQuery)->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
                ->whereDate('resolved_at', Carbon::today())
                ->count(),
            'overdue' => (clone $ticketQuery)->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', Carbon::now())
                ->count(),
            'unassigned' => (clone $ticketQuery)->whereNull('assigned_to')
                ->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
                ->count(),
        ];

        // Distribution by Department
        $departmentsData = Department::withCount(['tickets' => function ($q) {
            $q->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED]);
        }])->get();

        // Distribution by Category
        $categoriesData = Category::withCount(['tickets' => function ($q) {
            $q->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED]);
        }])->orderBy('tickets_count', 'desc')->take(6)->get();

        // Agent workload
        $agents = User::whereIn('role', [User::ROLE_AGENT, User::ROLE_SUPERVISOR])
            ->when($deptScope, fn($q) => $q->where('department_id', $deptScope))
            ->withCount(['assignedTickets' => function ($q) {
                $q->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED]);
            }])
            ->get();

        $recentTickets = (clone $ticketQuery)
            ->with(['user', 'department', 'category', 'assignedAgent'])
            ->latest()
            ->take(8)
            ->get();

        $recentActivities = TicketActivity::when($deptScope, function ($q) use ($deptScope) {
            $q->whereHas('ticket', fn($t) => $t->where('department_id', $deptScope));
        })
            ->with(['ticket', 'user'])
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.admin', compact('stats', 'departmentsData', 'categoriesData', 'agents', 'recentTickets', 'recentActivities'));
    }
}
