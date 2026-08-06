<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WFQConfiguration;
use App\Services\WFQService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WFQController extends Controller
{
    protected array $allowedTypes = [
        'category_weight',
        'complexity_weight',
        'purpose_weight',
    ];

    public function index()
    {
        $configs = WFQConfiguration::orderBy('config_type')->orderBy('config_key')->get();
        $grouped = $configs->groupBy('config_type');

        return view('admin.wfq.index', [
            'grouped' => $grouped,
            'allowedTypes' => $this->allowedTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'config_type' => ['required', 'string', Rule::in($this->allowedTypes)],
            'config_key' => ['required', 'string', 'max:100'],
            'config_value' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0.0001', 'max:99.9999'],
            'is_active' => ['boolean'],
        ]);

        $exists = WFQConfiguration::where('config_type', $validated['config_type'])
            ->where('config_key', $validated['config_key'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['config_key' => 'A configuration with this type and key already exists.'])
                ->withInput();
        }

        WFQConfiguration::create([
            'config_type' => $validated['config_type'],
            'config_key' => trim($validated['config_key']),
            'config_value' => $validated['config_value'],
            'weight' => $validated['weight'],
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('success', 'WFQ configuration added successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'config_value' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0.0001', 'max:99.9999'],
            'is_active' => ['boolean'],
        ]);

        $config = WFQConfiguration::findOrFail($id);
        $config->update([
            'config_value' => $validated['config_value'],
            'weight' => $validated['weight'],
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('success', 'WFQ configuration updated successfully.');
    }

    public function destroy($id)
    {
        $config = WFQConfiguration::findOrFail($id);
        $config->delete();

        return back()->with('success', 'WFQ configuration deleted successfully.');
    }

    public function recalculate()
    {
        $service = new WFQService;
        $service->recalculateQueue();

        return back()->with('success', 'Queue recalculated successfully.');
    }
}