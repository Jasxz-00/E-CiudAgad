<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected function parseDate(?string $value, bool $isEnd = false): Carbon
    {
        $date = Carbon::parse($value ?? ($isEnd ? now()->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')));

        return $isEnd ? $date->endOfDay() : $date->startOfDay();
    }

    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $requests = DocumentRequest::with(['documentType', 'purpose', 'resident'])
            ->whereBetween('created_at', [$this->parseDate($dateFrom), $this->parseDate($dateTo, true)])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total' => $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'completed' => $requests->where('status', 'completed')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
            'released' => $requests->where('status', 'released')->count(),
        ];

        $byDocumentType = $requests->groupBy('documentType.name')->map->count();
        $byPurpose = $requests->groupBy('purpose.name')->map->count();
        $byCategory = $requests->groupBy('resident.category')->map->count();

        return view('admin.reports.index', compact(
            'requests', 'summary', 'dateFrom', 'dateTo',
            'byDocumentType', 'byPurpose', 'byCategory'
        ));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $headers = [
            'Queue No.', 'Resident', 'Document Type', 'Purpose', 'Category',
            'Status', 'Weight', 'Queue Pos.', 'Submitted', 'Completed',
        ];

        $requests = DocumentRequest::with(['resident', 'documentType', 'purpose'])
            ->whereBetween('created_at', [$this->parseDate($dateFrom), $this->parseDate($dateTo, true)])
            ->orderBy('created_at', 'desc')
            ->get();

        $callback = function () use ($headers, $requests) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);

            foreach ($requests as $req) {
                fputcsv($file, [
                    $req->queue_number,
                    $req->resident?->full_name ?? 'N/A',
                    $req->documentType?->name ?? 'N/A',
                    $req->purpose?->name ?? 'N/A',
                    ucfirst($req->resident?->category ?? 'N/A'),
                    ucfirst($req->status),
                    $req->total_weight,
                    $req->queue_position,
                    $req->created_at->format('Y-m-d H:i'),
                    $req->completed_at?->format('Y-m-d H:i') ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="eciudadagad_report_'.$dateFrom.'_to_'.$dateTo.'.csv"',
        ]);
    }
}
