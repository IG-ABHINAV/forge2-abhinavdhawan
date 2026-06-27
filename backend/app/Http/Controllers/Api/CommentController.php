<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Ticket $ticket)
    {
        return response()->json($ticket->comments()->with('user')->get());
    }

    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'body' => 'required|string',
            'is_internal' => 'sometimes|boolean',
        ]);

        $comment = Comment::create([
            'body' => $request->body,
            'is_internal' => $request->is_internal ?? false,
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'organization_id' => $request->user()->organization_id,
        ]);

        ActivityLog::create([
            'action' => 'comment_added',
            'entity_type' => Comment::class,
            'entity_id' => $comment->id,
            'payload' => ['ticket_id' => $ticket->id],
            'user_id' => $request->user()->id,
            'organization_id' => $request->user()->organization_id,
        ]);

        return response()->json($comment->load('user'), 201);
    }
}
