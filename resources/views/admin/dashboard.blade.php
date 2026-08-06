@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <x-stats-card label="Total Residents" :value="$stats['total_residents']" color="primary" icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        <x-stats-card label="Requests This Month" :value="$stats['requests_per_month']" color="blue" icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        <x-stats-card label="Pending & On-Queue" :value="$stats['pending_onqueue']" color="yellow" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stats-card label="Avg. Process Time" :value="$stats['avg_process_time'] . ' hrs'" color="green" icon="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
    </div>

    <x-card class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Daily Requests (Last 30 Days)</h2>
        <div class="relative h-64 sm:h-80"><canvas id="dailyChart"></canvas></div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requests by Resident Category</h2>
            <div class="relative h-64 sm:h-72"><canvas id="categoryChart"></canvas></div>
        </x-card>
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requests by Document Type</h2>
            <div class="relative h-64 sm:h-72"><canvas id="documentChart"></canvas></div>
        </x-card>
    </div>

    <x-card class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requests by Purpose</h2>
        <div class="relative h-64 sm:h-72"><canvas id="purposeChart"></canvas></div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Requests</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">Queue</th>
                            <th class="pb-2 font-medium">Resident</th>
                            <th class="pb-2 font-medium">Document</th>
                            <th class="pb-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRequests as $req)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2 font-mono">{{ $req->queue_number }}</td>
                            <td class="py-2">{{ $req->resident?->full_name ?? 'N/A' }}</td>
                            <td class="py-2">{{ $req->documentType?->name }}</td>
                            <td class="py-2"><x-badge :status="$req->status" /></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h2>
            <div class="space-y-2 text-sm">
                @forelse($recentLogs as $log)
                    <div class="p-3 bg-gray-50 dark:bg-gray-950 rounded-lg">
                        <p><span class="font-medium">{{ $log->user?->username ?? 'System' }}</span> {{ $log->description }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-gray-600 dark:text-gray-400 text-center py-4">No recent activity.</p>
                @endforelse
            </div>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function initAdminCharts() {
    if (typeof Chart === 'undefined') return;

    if (window._adminChartInterval) {
        clearInterval(window._adminChartInterval);
        window._adminChartInterval = null;
    }

    ['dailyChart', 'categoryChart', 'documentChart', 'purposeChart'].forEach(function (id) {
        var existing = Chart.getChart(id);
        if (existing) existing.destroy();
    });

    function isDark() { return document.documentElement.classList.contains('dark'); }
    function textColor() { return isDark() ? '#E5E7EB' : '#374151'; }
    function gridColor() { return isDark() ? '#374151' : '#E5E7EB'; }

    var dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx) {
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyRequests->keys()) !!},
                datasets: [{
                    label: 'Requests',
                    data: {!! json_encode($dailyRequests->values()) !!},
                    borderColor: '#2563EB',
                    backgroundColor: isDark() ? 'rgba(37, 99, 235, 0.2)' : 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { ticks: { color: textColor(), autoSkip: true, maxTicksLimit: 10, maxRotation: 45, minRotation: 0 }, grid: { color: gridColor() } }, y: { ticks: { color: textColor() }, grid: { color: gridColor() } } }
            }
        });
    }

    var catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($byCategory->keys()) !!},
                datasets: [{
                    data: {!! json_encode($byCategory->values()) !!},
                    backgroundColor: ['#2563EB', '#FACC15', '#10B981', '#EF4444']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: textColor() } } } }
        });
    }

    var docCtx = document.getElementById('documentChart');
    if (docCtx) {
        new Chart(docCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($byDocumentType->keys()) !!},
                datasets: [{
                    label: 'Count',
                    data: {!! json_encode($byDocumentType->values()) !!},
                    backgroundColor: '#2563EB'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { ticks: { color: textColor(), autoSkip: true, maxTicksLimit: 10, maxRotation: 45, minRotation: 0 }, grid: { color: gridColor() } }, y: { ticks: { color: textColor() }, grid: { color: gridColor() } } }
            }
        });
    }

    var purpCtx = document.getElementById('purposeChart');
    if (purpCtx) {
        new Chart(purpCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($byPurpose->keys()) !!},
                datasets: [{
                    label: 'Count',
                    data: {!! json_encode($byPurpose->values()) !!},
                    backgroundColor: '#10B981'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { ticks: { color: textColor(), autoSkip: true, maxTicksLimit: 10, maxRotation: 45, minRotation: 0 }, grid: { color: gridColor() } }, y: { ticks: { color: textColor() }, grid: { color: gridColor() } } }
            }
        });
    }

    window._adminChartInterval = setInterval(function () {
        fetch('{{ route("admin.dashboard.chart-data") }}')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var chart = Chart.getChart('dailyChart');
                if (chart) {
                    chart.data.labels = data.labels;
                    chart.data.datasets[0].data = data.data;
                    chart.update();
                }
            });
    }, 30000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminCharts);
} else {
    initAdminCharts();
}
if (!window._adminChartsBound) {
    window._adminChartsBound = true;
    window.addEventListener('livewire:navigated', initAdminCharts);
    document.addEventListener('click', function (e) {
        if (e.target.closest('#theme-toggle')) {
            initAdminCharts();
        }
    });
}
</script>
@endpush
