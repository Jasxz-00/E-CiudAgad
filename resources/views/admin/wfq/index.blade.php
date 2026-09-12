@extends('layouts.app')

@section('title', 'WFQ Configuration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 min-w-0">WFQ Configuration</h1>
        <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('admin.wfq.recalculate') }}" class="inline">
                @csrf
                <button type="submit" class="btn-accent text-sm" onclick="return confirm('Recalculate queue? This will reorder all pending requests.')">Recalculate Queue</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $titles = [
            'category_weight' => 'Resident Category Weights',
            'purpose_weight' => 'Purpose Priority Weights',
        ];
    @endphp

    <div class="card mb-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Queue priority weights are read-only and centralized in <span class="font-mono">config/queue.php</span>. The combined weight is W = resident weight + purpose weight. Document type does not affect priority (service length L = 1 for all requests).</p>
    </div>

    @forelse($grouped as $type => $configs)
    @continue($type === 'complexity_weight')
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-4">{{ $titles[$type] ?? ucfirst($type) }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-3 font-medium">Key</th>
                        <th class="pb-3 font-medium">Label</th>
                        <th class="pb-3 font-medium">Weight</th>
                        <th class="pb-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($configs as $config)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-3 font-mono text-xs">{{ $config->config_key }}</td>
                        <td class="py-3">{{ $config->config_value }}</td>
                        <td class="py-3 font-mono">{{ number_format((float) $config->weight, 4) }}</td>
                        <td class="py-3">{!! $config->is_active ? '<span class="text-green-600 dark:text-green-400 font-medium">Active</span>' : '<span class="text-red-600 dark:text-red-400 font-medium">Inactive</span>' !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="card">
        <p class="text-sm text-gray-600 dark:text-gray-400">No WFQ configurations available.</p>
    </div>
    @endforelse
</div>
@endsection