@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Admin Dashboard</h1>

    <table class="w-full text-sm mb-8">
        <thead>
            <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                <th class="pb-3 font-medium">Metric</th>
                <th class="pb-3 font-medium">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b border-gray-200 dark:border-gray-700">
                <td class="py-3">Total Residents</td>
                <td class="py-3 font-bold">{{ $stats['total_residents'] }}</td>
            </tr>
            <tr class="border-b border-gray-200 dark:border-gray-700">
                <td class="py-3">Requests This Month</td>
                <td class="py-3 font-bold">{{ $stats['requests_per_month'] }}</td>
            </tr>
            <tr class="border-b border-gray-200 dark:border-gray-700">
                <td class="py-3">Pending & On-Queue</td>
                <td class="py-3 font-bold">{{ $stats['pending_onqueue'] }}</td>
            </tr>
            <tr class="border-b border-gray-200 dark:border-gray-700">
                <td class="py-3">Avg. Process Time</td>
                <td class="py-3 font-bold">{{ $stats['avg_process_time'] }} hrs</td>
            </tr>
        </tbody>
    </table>

    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Daily Requests (Last 30 Days)</h2>
        <div class="relative h-64 sm:h-80"><canvas id="dailyChart"></canvas></div>
    </div>

    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requests by Resident Category</h2>
        <div class="relative h-64 sm:h-72"><canvas id="categoryChart"></canvas></div>
    </div>

    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requests by Document Type</h2>
        <div class="relative h-64 sm:h-72"><canvas id="documentChart"></canvas></div>
    </div>

    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requests by Purpose</h2>
        <div class="relative h-64 sm:h-72"><canvas id="purposeChart"></canvas></div>
    </div>

    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Requests</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-3 font-medium">Queue</th>
                        <th class="pb-3 font-medium">Resident</th>
                        <th class="pb-3 font-medium">Document</th>
                        <th class="pb-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRequests as $req)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-3 font-mono">{{ $req->queue_number }}</td>
                        <td class="py-3">{{ $req->resident?->full_name ?? 'N/A' }}</td>
                        <td class="py-3">{{ $req->documentType?->name }}</td>
                        <td class="py-3"><x-badge :status="$req->status" /></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-600 dark:text-gray-400">No recent requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-3 font-medium">User</th>
                        <th class="pb-3 font-medium">Description</th>
                        <th class="pb-3 font-medium">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-3">{{ $log->user?->username ?? 'System' }}</td>
                        <td class="py-3">{{ $log->description }}</td>
                        <td class="py-3 text-xs text-gray-600 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-600 dark:text-gray-400">No recent activity.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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