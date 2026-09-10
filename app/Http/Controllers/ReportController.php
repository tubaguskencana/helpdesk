<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isStaff()) {
            abort(403);
        }

        $deptScope = $user->isSupervisor() ? $user->department_id : $request->input('department_id');

        $query = Ticket::query()->with(['user', 'department', 'category', 'assignedAgent']);

        if ($deptScope) {
            $query->where('department_id', $deptScope);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $totalTickets = (clone $query)->count();
        $resolvedCount = (clone $query)->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])->count();
        $overdueCount = (clone $query)->whereNotIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', Carbon::now())
            ->count();

        $departments = Department::where('is_active', true)->get();
        $tickets = $query->latest()->paginate(20)->withQueryString();

        return view('reports.index', compact('tickets', 'totalTickets', 'resolvedCount', 'overdueCount', 'departments'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isStaff()) {
            abort(403);
        }

        $deptScope = $user->isSupervisor() ? $user->department_id : $request->input('department_id');

        $query = Ticket::query()->with(['user', 'department', 'category', 'assignedAgent']);

        if ($deptScope) {
            $query->where('department_id', $deptScope);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $tickets = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tickets-report-' . date('Ymd-His') . '.csv"',
        ];

        return response()->stream(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ticket Number', 'Subject', 'Requester', 'Department', 'Category', 'Priority', 'Status', 'Assigned To', 'Created At', 'Resolved At']);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket->ticket_number,
                    $ticket->subject,
                    $ticket->user?->name ?? 'N/A',
                    $ticket->department?->name ?? 'N/A',
                    $ticket->category?->name ?? 'N/A',
                    ucfirst($ticket->priority),
                    ucfirst(str_replace('_', ' ', $ticket->status)),
                    $ticket->assignedAgent?->name ?? 'Unassigned',
                    $ticket->created_at->format('Y-m-d H:i'),
                    $ticket->resolved_at ? $ticket->resolved_at->format('Y-m-d H:i') : '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
