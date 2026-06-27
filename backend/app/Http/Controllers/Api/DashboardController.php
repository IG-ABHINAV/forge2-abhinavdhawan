<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $totalTickets = Ticket::count();
        
        $byStatus = [
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
        ];

        $byPriority = [
            'low' => Ticket::where('priority', 'low')->count(),
            'medium' => Ticket::where('priority', 'medium')->count(),
            'high' => Ticket::where('priority', 'high')->count(),
            'urgent' => Ticket::where('priority', 'urgent')->count(),
        ];

        $slaBreached = Ticket::where('sla_breached', true)->count();

        return response()->json([
            'total_tickets' => $totalTickets,
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'sla_breached' => $slaBreached,
        ]);
    }
}
