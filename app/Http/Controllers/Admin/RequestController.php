<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestPurpose;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentRequest::with(['resident', 'documentType', 'purpose']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }

        if ($request->filled('category')) {
            $query->whereHas('resident', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('queue_number', 'like', "%{$search}%")
                    ->orWhereHas('resident', function ($r) use ($search) {
                        $r->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $documentTypes = DocumentType::where('is_active', true)->orderBy('name')->get();
        $purposes = RequestPurpose::where('is_active', true)->orderBy('name')->get();

        return view('admin.requests.index', compact('requests', 'documentTypes', 'purposes'));
    }

    public function show($id)
    {
        $request = DocumentRequest::with(['resident.user', 'documentType', 'purpose', 'processedBy', 'documents'])
            ->findOrFail($id);

        return view('admin.requests.show', compact('request'));
    }
}
