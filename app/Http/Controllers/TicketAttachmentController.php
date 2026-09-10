<?php

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketAttachmentController extends Controller
{
    use AuthorizesRequests;

    public function download(TicketAttachment $attachment): StreamedResponse|Response
    {
        $ticket = $attachment->ticket;
        $this->authorize('view', $ticket);

        /** @var User $user */
        $user = Auth::user();

        // If attachment belongs to an internal note, requester cannot download
        if ($attachment->reply && $attachment->reply->is_internal && !$user->isStaff()) {
            abort(403, 'Unauthorized access to internal attachment.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
