<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TicketController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TicketService $ticketService
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Ticket::query()->with(['user', 'department', 'category', 'assignedAgent']);

        // Base role-based scoping
        if ($user->isRequester()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isSupervisor()) {
            if ($user->department_id) {
                $query->where('department_id', $user->department_id);
            }
        } elseif ($user->isAgent()) {
            // Can see assigned tickets, department tickets, or unassigned department tickets
            if ($user->department_id) {
                $query->where(function ($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('department_id', $user->department_id);
                });
            } else {
                $query->where('assigned_to', $user->id);
            }
        }

        // Quick Tabs
        $tab = $request->query('tab', 'all');
        if ($tab === 'my_tickets') {
            if ($user->isRequester()) {
                $query->where('user_id', $user->id);
            } else {
                $query->where('assigned_to', $user->id);
            }
        } elseif ($tab === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($tab === 'open') {
            $query->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS]);
        } elseif ($tab === 'resolved') {
            $query->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED]);
        }

        // Filter: Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter: Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter: Priority
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // Filter: Department
        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        // Filter: Category
        if ($catId = $request->input('category_id')) {
            $query->where('category_id', $catId);
        }

        // Filter: Assigned To
        if ($assignedTo = $request->input('assigned_to')) {
            if ($assignedTo === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $assignedTo);
            }
        }

        $tickets = $query->latest('updated_at')->paginate(15)->withQueryString();

        // Dropdown filter data
        $departments = Department::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $agents = User::whereIn('role', [User::ROLE_AGENT, User::ROLE_SUPERVISOR, User::ROLE_ADMIN])
            ->where('is_active', true)
            ->get();

        // Counts for tab badges
        $countsQuery = Ticket::query();
        if ($user->isRequester()) {
            $countsQuery->where('user_id', $user->id);
        } elseif ($user->isSupervisor() && $user->department_id) {
            $countsQuery->where('department_id', $user->department_id);
        } elseif ($user->isAgent() && $user->department_id) {
            $countsQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('department_id', $user->department_id);
            });
        }

        $counts = [
            'all' => (clone $countsQuery)->count(),
            'my_tickets' => $user->isRequester()
                ? (clone $countsQuery)->count()
                : (clone $countsQuery)->where('assigned_to', $user->id)->count(),
            'unassigned' => (clone $countsQuery)->whereNull('assigned_to')->count(),
            'open' => (clone $countsQuery)->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS])->count(),
            'resolved' => (clone $countsQuery)->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])->count(),
        ];

        return view('tickets.index', compact('tickets', 'departments', 'categories', 'agents', 'counts', 'tab'));
    }

    public function create(): View
    {
        $this->authorize('create', Ticket::class);

        $departments = Department::where('is_active', true)
            ->with(['categories' => fn($q) => $q->where('is_active', true)])
            ->get();

        return view('tickets.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Ticket::class);

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'attachments.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip'],
        ]);

        $ticket = $this->ticketService->createTicket(
            user: Auth::user(),
            data: $validated,
            attachments: $request->file('attachments') ?? []
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} has been successfully submitted.");
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        /** @var User $user */
        $user = Auth::user();

        // Load replies - filter out internal notes if user is requester!
        $ticket->load([
            'user',
            'department',
            'category',
            'assignedAgent',
            'attachments' => fn($q) => $q->whereNull('reply_id'),
            'activities.user',
        ]);

        $repliesQuery = $ticket->replies()->with(['user', 'attachments'])->oldest();

        if ($user->isRequester()) {
            $repliesQuery->where('is_internal', false);
        }

        $replies = $repliesQuery->get();

        // Agents list for assignment dropdown
        $availableAgents = User::whereIn('role', [User::ROLE_AGENT, User::ROLE_SUPERVISOR, User::ROLE_ADMIN])
            ->where('is_active', true)
            ->when($ticket->department_id, function ($q) use ($ticket) {
                $q->where(function ($sub) use ($ticket) {
                    $sub->where('department_id', $ticket->department_id)
                        ->orWhere('role', User::ROLE_ADMIN);
                });
            })
            ->get();

        return view('tickets.show', compact('ticket', 'replies', 'availableAgents'));
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('updateStatus', $ticket);

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,pending,resolved,closed'],
        ]);

        $this->ticketService->updateStatus($ticket, Auth::user(), $validated['status']);

        return back()->with('success', "Ticket status updated to " . ucfirst(str_replace('_', ' ', $validated['status'])));
    }

    public function updatePriority(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('updatePriority', $ticket);

        $validated = $request->validate([
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $this->ticketService->updatePriority($ticket, Auth::user(), $validated['priority']);

        return back()->with('success', "Ticket priority updated to " . ucfirst($validated['priority']));
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('assign', $ticket);

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $agent = $validated['assigned_to'] ? User::find($validated['assigned_to']) : null;

        $this->ticketService->assignAgent($ticket, Auth::user(), $agent);

        $msg = $agent ? "Ticket assigned to {$agent->name}." : "Ticket unassigned.";
        return back()->with('success', $msg);
    }
}
