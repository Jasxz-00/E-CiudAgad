<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Concern;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConcernController extends Controller
{
    public function index()
    {
        $resident = Auth::user()->resident;
        $concerns = Concern::where('resident_id', $resident->id)
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'reviewing' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('resident.concerns', compact('concerns'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $resident = Auth::user()->resident;

        $concern = Concern::create([
            'resident_id' => $resident->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        NotificationService::notifyPersonnelOfNewConcern($concern);

        return redirect()->route('resident.concerns')
            ->with('success', 'Your concern has been submitted successfully.');
    }

    public function show($id)
    {
        $resident = Auth::user()->resident;
        $concern = Concern::where('resident_id', $resident->id)
            ->findOrFail($id);

        return view('resident.concerns-show', compact('concern'));
    }
}
