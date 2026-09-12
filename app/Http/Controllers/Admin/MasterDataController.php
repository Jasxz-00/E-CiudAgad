<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\RequestPurpose;
use App\Models\ResidentCategory;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function index()
    {
        $documentTypes = DocumentType::orderBy('name')->get();
        $purposes = RequestPurpose::orderBy('name')->get();
        $categories = ResidentCategory::orderBy('name')->get();

        return view('admin.master-data.index', compact('documentTypes', 'purposes', 'categories'));
    }

    protected function getResourceType(Request $request): string
    {
        $name = $request->route()->getName();
        if (str_contains($name, 'document-types')) {
            return 'document-types';
        }
        if (str_contains($name, 'purposes')) {
            return 'purposes';
        }
        if (str_contains($name, 'categories')) {
            return 'categories';
        }

        return 'document-types';
    }

    public function store(Request $request)
    {
        $type = $this->getResourceType($request);

        return match ($type) {
            'document-types' => $this->storeDocumentType($request),
            'purposes' => $this->storePurpose($request),
            'categories' => $this->storeCategory($request),
            default => abort(404),
        };
    }

    public function update(Request $request, $id)
    {
        $type = $this->getResourceType($request);

        return match ($type) {
            'document-types' => $this->updateDocumentType($request, $id),
            'purposes' => $this->updatePurpose($request, $id),
            'categories' => $this->updateCategory($request, $id),
            default => abort(404),
        };
    }

    private function storeDocumentType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_types,code',
            'complexity' => 'required|in:simple,moderate,complex',
            'complexity_weight' => 'required|numeric|min:0|max:99.99',
            'description' => 'nullable|string',
        ]);

        DocumentType::create($validated);

        return back()->with('success', 'Document type created.');
    }

    private function updateDocumentType(Request $request, $id)
    {
        $dt = DocumentType::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_types,code,'.$id,
            'complexity' => 'required|in:simple,moderate,complex',
            'complexity_weight' => 'required|numeric|min:0|max:99.99',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $dt->update($validated);

        return back()->with('success', 'Document type updated.');
    }

    private function storePurpose(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:request_purposes,code',
            'priority_weight' => 'required|numeric|min:0|max:99.99',
            'description' => 'nullable|string',
        ]);

        RequestPurpose::create($validated);

        return back()->with('success', 'Purpose created.');
    }

    private function updatePurpose(Request $request, $id)
    {
        $purpose = RequestPurpose::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:request_purposes,code,'.$id,
            'priority_weight' => 'required|numeric|min:0|max:99.99',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $purpose->update($validated);

        return back()->with('success', 'Purpose updated.');
    }

    private function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:resident_categories,name',
            'display_name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:99.99',
            'description' => 'nullable|string',
        ]);

        ResidentCategory::create($validated);

        return back()->with('success', 'Category created.');
    }

    private function updateCategory(Request $request, $id)
    {
        $cat = ResidentCategory::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:resident_categories,name,'.$id,
            'display_name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:99.99',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $cat->update($validated);

        return back()->with('success', 'Category updated.');
    }
}
