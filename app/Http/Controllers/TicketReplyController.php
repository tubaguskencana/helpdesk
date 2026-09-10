<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketReplyController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TicketService $ticketService
    ) {}

    public function store(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('reply', $ticket);

        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'message' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
            'attachments.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip'],
        ]);

        // Security check: only staff can write internal notes
        $isInternal = false;
        if (!empty($validated['is_internal']) && $user->isStaff()) {
            $isInternal = true;
        }

        $this->ticketService->addReply(
            ticket: $ticket,
            user: $user,
            message: $validated['message'],
            isInternal: $isInternal,
            attachments: $request->file('attachments') ?? []
        );

        $msg = $isInternal ? 'Internal note added successfully.' : 'Reply sent successfully.';
        return back()->with('success', $msg);
    }
}
