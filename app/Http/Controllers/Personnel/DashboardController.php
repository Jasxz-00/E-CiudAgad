<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => DocumentRequest::count(),
            'pending' => DocumentRequest::where('status', 'pending')->count(),
            'reviewing' => DocumentRequest::where('status', 'reviewing')->count(),
            'completed_today' => DocumentRequest::whereDate('completed_at', today())->count(),
        ];

        $recentRequests = DocumentRequest::with(['resident.user', 'documentType'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $queue = DocumentRequest::with(['resident.user', 'documentType', 'purpose'])
            ->whereIn('status', ['pending', 'reviewing'])
            ->orderBy('queue_position', 'asc')
            ->take(10)
            ->get();

        return view('personnel.dashboard', compact('stats', 'recentRequests', 'queue'));
    }
}
