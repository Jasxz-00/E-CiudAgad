<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DocumentSetting;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentSettingController extends Controller
{
    public function index()
    {
        $setting = DocumentSetting::bootstrap();
        $documentTypes = DocumentType::where('is_active', true)->orderBy('name')->get();

        return view('admin.document-settings.index', compact('setting', 'documentTypes'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'chairman_name' => ['nullable', 'string', 'max:255'],
            'barangay_name' => ['required', 'string', 'max:255'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'city_name' => ['nullable', 'string', 'max:255'],
            'barangay_address' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = DocumentSetting::bootstrap();

        $setting->update([
            'chairman_name' => $validated['chairman_name'],
            'barangay_name' => $validated['barangay_name'],
            'province_name' => $validated['province_name'] ?? null,
            'city_name' => $validated['city_name'] ?? null,
            'barangay_address' => $validated['barangay_address'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'document_settings_updated',
            'description' => 'Updated document settings',
        ]);

        return back()->with('success', 'Document settings updated successfully.');
    }
}
