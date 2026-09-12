@extends('layouts.app')

@section('title', __('common.requests'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('common.requests') }}</h1>
        <a href="{{ route('personnel.dashboard') }}"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-700bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition"> 
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"> 
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /> 
            </svg>

            Back
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="card p-4"><p class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.pending') }}</p><p class="text-xl font-bold text-accent-700 dark:text-accent-300 dark:text-accent-400">{{ $stats['pending'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.reviewing') }}</p><p class="text-xl font-bold text-primary-700 dark:text-primary-400">{{ $stats['reviewing'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.approved') }}</p><p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $stats['approved'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.completed') }}</p><p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $stats['completed'] }}</p></div>
    </div>

    <div class="card p-4 mb-6" x-data="queueFilterState()">
        <form method="GET" action="{{ route('personnel.requests') }}" @@submit="submitForm">
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <div class="flex-1 relative">
                    <input type="text" name="search" x-model="search" placeholder="{{ __('common.search') }} {{ __('common.resident_name') }} {{ __('common.or') }} {{ __('common.queue_number') }}..."
                           class="input-field w-full pl-10" value="{{ request('search') }}">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <button type="submit" class="btn-primary text-sm whitespace-nowrap">{{ __('common.search') }}</button>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">{{ __('common.date') }}:</span>
                <template x-for="preset in presets" :key="preset.value">
                    <button type="button"
                            @@click="setPreset(preset.value)"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                            :class="activePreset === preset.value ? 'bg-primary-600 text-white' : 'bg-gray-50 dark:bg-gray-950 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                            x-text="preset.label">
                    </button>
                </template>
                <input type="hidden" name="date_preset" x-model="activePreset">
            </div>

            <div x-show="showCustomRange" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" class="flex flex-wrap items-center gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.from') }}:</label>
                    <input type="date" name="date_from" x-model="dateFrom" class="input-field text-xs py-1.5 px-2">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.to') }}:</label>
                    <input type="date" name="date_to" x-model="dateTo" class="input-field text-xs py-1.5 px-2">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.status') }}:</label>
                    <select name="status" class="select-field text-xs py-1.5">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach(['pending', 'reviewing', 'approved', 'rejected', 'completed'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ __("common.{$s}") }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.category') }}:</label>
                    <select name="category" class="select-field text-xs py-1.5">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach(['regular', 'senior', 'pwd', 'pregnant'] as $c)
                            <option value="{{ $c }}" {{ request('category') == $c ? 'selected' : '' }}>{{ __("common.{$c}") }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request()->anyFilled(['search', 'status', 'category', 'date_preset', 'date_from', 'date_to']))
                    <a href="{{ route('personnel.requests') }}" class="btn-ghost text-xs">{{ __('common.clear') }}</a>
                @endif
            </div>
        </form>
    </div>

        <div class="card" x-data="{ activeTab: '{{ request('queue_tab', 'pending-review') }}' }">
        <div class="flex flex-wrap gap-2 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3 overflow-x-auto">
            <button type="button" @@click="activeTab='pending-review'"
                    :class="activeTab === 'pending-review' ? 'tab-btn active' : 'tab-btn'"
                    class="whitespace-nowrap">
                Pending Review
                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">
                    {{ $stats['pending'] ?? \App\Models\DocumentRequest::where('status', 'pending')->count() }}
                </span>
            </button>
            <button type="button" @@click="activeTab='main-queue'"
                    :class="activeTab === 'main-queue' ? 'tab-btn active' : 'tab-btn'"
                    class="whitespace-nowrap">
                Main Queue
                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                    {{ $stats['reviewing'] ?? \App\Models\DocumentRequest::where('status', 'reviewing')->count() }}
                </span>
            </button>
            <button type="button" @@click="activeTab='processing'"
                    :class="activeTab === 'processing' ? 'tab-btn active' : 'tab-btn'"
                    class="whitespace-nowrap">
                Processing
                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                    {{ \App\Models\DocumentRequest::where('status', 'on_hold')->count() }}
                </span>
            </button>
            <button type="button" @@click="activeTab='on-hold'"
                    :class="activeTab === 'on-hold' ? 'tab-btn active' : 'tab-btn'"
                    class="whitespace-nowrap">
                On Hold / Needs Correction
                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                    {{ \App\Models\DocumentRequest::where('status', 'on_hold')->count() }}
                </span>
            </button>
            <button type="button" @@click="activeTab='ready-pickup'"
                    :class="activeTab === 'ready-pickup' ? 'tab-btn active' : 'tab-btn'"
                    class="whitespace-nowrap">
                Ready for Pickup
                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                    {{ $stats['approved'] ?? \App\Models\DocumentRequest::where('status', 'approved')->count() }}
                </span>
            </button>
            <button type="button" @@click="activeTab='completed-released'"
                    :class="activeTab === 'completed-released' ? 'tab-btn active' : 'tab-btn'"
                    class="whitespace-nowrap">
                Completed / Released
                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                    {{ (\App\Models\DocumentRequest::where('status', 'completed')->count() + \App\Models\DocumentRequest::where('status', 'released')->count()) }}
                </span>
            </button>
            <button type="button" @@click="activeTab='rejected'"
                    :class="activeTab === 'rejected' ? 'tab-btn active' : 'tab-btn'"
                    class="whitespace-nowrap">
                Rejected
                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                    {{ \App\Models\DocumentRequest::where('status', 'rejected')->count() }}
                </span>
            </button>
            <button type="button" @@click="activeTab='cancelled'"
                    :class="activeTab === 'cancelled' ? 'tab-btn active' : 'tab-btn'"
                    class="whitespace-nowrap">
                Cancelled
                <span class="inline-block ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    {{ \App\Models\DocumentRequest::where('status', 'cancelled')->count() }}
                </span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-3 font-medium">{{ __('common.queue_number') }}</th>
                        <th class="pb-3 font-medium">{{ __('common.queue_position') }}</th>
                        <th class="pb-3 font-medium">{{ __('common.resident_name') }}</th>
                        <th class="pb-3 font-medium">{{ __('common.document') }}</th>
                        <th class="pb-3 font-medium">{{ __('common.purpose') }}</th>
                        <th class="pb-3 font-medium">{{ __('common.category') }}</th>
                        <th class="pb-3 font-medium">{{ __('common.status') }}</th>
                        <th class="pb-3 font-medium">{{ __('common.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
                        x-show="
                            (activeTab === 'pending-review' && '{{ $req->status }}' === 'pending') ||
                            (activeTab === 'main-queue' && '{{ $req->status }}' === 'reviewing') ||
                            (activeTab === 'processing' && '{{ $req->status }}' === 'on_hold') ||
                            (activeTab === 'on-hold' && '{{ $req->status }}' === 'on_hold') ||
                            (activeTab === 'ready-pickup' && '{{ $req->status }}' === 'approved') ||
                            (activeTab === 'completed-released' && ('{{ $req->status }}' === 'completed' || '{{ $req->status }}' === 'released')) ||
                            (activeTab === 'rejected' && '{{ $req->status }}' === 'rejected') ||
                            (activeTab === 'cancelled' && '{{ $req->status }}' === 'cancelled')
                        "
                        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0">
                        <td class="py-3 font-mono">{{ $req->queue_number }}</td>
                        <td class="py-3">{{ $req->queue_position ?? '-' }}</td>
                        <td class="py-3">{{ $req->resident?->full_name ?? 'N/A' }}</td>
                        <td class="py-3">{{ $req->documentType?->name }}</td>
                        <td class="py-3">{{ $req->purpose?->name }}</td>
                        <td class="py-3 capitalize">{{ $req->resident?->category ?? 'N/A' }}</td>
                        <td class="py-3"><span class="badge-{{ $req->status }}">{{ __("common.{$req->status}") }}</span></td>
                        <td class="py-3">
                            <a href="{{ route('personnel.request.show', $req->id) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">{{ __('common.view') }}</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-8 text-center text-gray-600 dark:text-gray-400">{{ __('common.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $requests->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function queueFilterState() {
        return {
            search: '{{ request('search') }}',
            activePreset: '{{ request('date_preset') }}',
            dateFrom: '{{ request('date_from') }}',
            dateTo: '{{ request('date_to') }}',
            activeTab: '{{ request('queue_tab', 'pending-review') }}',
            presets: [
                { value: 'today', label: '{{ __("common.today") }}' },
                { value: 'this_week', label: '{{ __("common.this_week") }}' },
                { value: 'this_month', label: '{{ __("common.this_month") }}' },
                { value: 'custom_range', label: '{{ __("common.custom_range") }}' },
            ],
            get showCustomRange() {
                return this.activePreset === 'custom_range' || this.dateFrom || this.dateTo;
            },
            setPreset(value) {
                if (value === 'custom_range') {
                    this.activePreset = 'custom_range';
                } else {
                    this.activePreset = value;
                    this.dateFrom = '';
                    this.dateTo = '';
                }
            },
            submitForm() {
                if (this.activePreset === 'custom_range' && !this.dateFrom && !this.dateTo) {
                    this.activePreset = '';
                }
            }
        };
    }
</script>
@endpush