<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'assignee', 'comments.user']);

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('tag')) {
            // Placeholder logic if tag filtering is invoked
            // Scopes will naturally filter based on organization global scope
        }

        return $query->cursorPaginate(25);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = DB::transaction(function () use ($request) {
            $ticket = Ticket::create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => 'open',
                'priority' => $request->priority,
                'organization_id' => $request->user()->organization_id,
                'user_id' => $request->user()->id,
                'sla_breached' => false,
            ]);

            ActivityLog::create([
                'action' => 'ticket_created',
                'entity_type' => Ticket::class,
                'entity_id' => $ticket->id,
                'payload' => ['title' => $ticket->title],
                'user_id' => $request->user()->id,
                'organization_id' => $request->user()->organization_id,
            ]);

            return $ticket;
        });

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket)
    {
        return response()->json($ticket->load(['user', 'assignee', 'comments.user']));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ]);

        $oldStatus = $ticket->status;

        $ticket->update($request->only(['title', 'description', 'status', 'priority', 'assigned_to']));

        if ($request->has('status') && $request->status !== $oldStatus) {
            ActivityLog::create([
                'action' => 'ticket_status_changed',
                'entity_type' => Ticket::class,
                'entity_id' => $ticket->id,
                'payload' => ['old_status' => $oldStatus, 'new_status' => $ticket->status],
                'user_id' => $request->user()->id,
                'organization_id' => $request->user()->organization_id,
            ]);
        }

        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        ActivityLog::create([
            'action' => 'ticket_deleted',
            'entity_type' => Ticket::class,
            'entity_id' => $ticket->id,
            'payload' => ['title' => $ticket->title],
            'user_id' => auth()->user()->id,
            'organization_id' => auth()->user()->organization_id,
        ]);

        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}
