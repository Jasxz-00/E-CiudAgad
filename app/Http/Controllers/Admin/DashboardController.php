<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Models\Resident;

class DashboardController extends Controller
{
    public function index()
    {
        $totalResidents = Resident::count();

        $requestsPerMonth = DocumentRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $pendingOnqueue = DocumentRequest::whereIn('status', ['pending', 'reviewing'])->count();

        $avgProcessTime = null;
        DocumentRequest::whereNotNull('completed_at')
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->get(['created_at', 'completed_at'])
            ->whenNotEmpty(function ($completed) use (&$avgProcessTime) {
                $avgSeconds = $completed->avg(
                    fn ($r) => (int) $r->completed_at->diffInSeconds($r->created_at)
                );
                $avgProcessTime = round($avgSeconds / 3600, 1);
            });

        $dailyRequests = DocumentRequest::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $byCategory = DocumentRequest::selectRaw('residents.category, COUNT(*) as count')
            ->join('residents', 'document_requests.resident_id', '=', 'residents.id')
            ->groupBy('residents.category')
            ->pluck('count', 'category');

        $byDocumentType = DocumentRequest::selectRaw('document_types.name, COUNT(*) as count')
            ->join('document_types', 'document_requests.document_type_id', '=', 'document_types.id')
            ->groupBy('document_types.name')
            ->pluck('count', 'name');

        $byPurpose = DocumentRequest::selectRaw('request_purposes.name, COUNT(*) as count')
            ->join('request_purposes', 'document_requests.purpose_id', '=', 'request_purposes.id')
            ->groupBy('request_purposes.name')
            ->pluck('count', 'name');

        $stats = [
            'total_residents' => $totalResidents,
            'requests_per_month' => $requestsPerMonth,
            'pending_onqueue' => $pendingOnqueue,
            'avg_process_time' => $avgProcessTime ? round($avgProcessTime, 1) : 0,
        ];

        $recentRequests = DocumentRequest::with(['resident.user', 'documentType'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentLogs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'recentRequests', 'recentLogs',
            'dailyRequests', 'byCategory', 'byDocumentType', 'byPurpose'
        ));
    }

    public function getChartData()
    {
        $dailyRequests = DocumentRequest::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        return response()->json([
            'labels' => $dailyRequests->keys(),
            'data' => $dailyRequests->values(),
        ]);
    }
}
