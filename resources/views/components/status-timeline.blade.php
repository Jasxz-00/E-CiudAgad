@props([
    'status' => 'pending',
    'currentStatus' => null,
    'class' => '',
])

@php
    $statuses = [
        'pending' => ['label' => 'Submitted', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'reviewing' => ['label' => 'Under Review', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        'approved' => ['label' => 'Approved', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'processing' => ['label' => 'Processing', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
        'ready_for_pickup' => ['label' => 'Ready for Pickup', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
        'released' => ['label' => 'Released', 'icon' => 'M5 13l4 4L19 7'],
        'rejected' => ['label' => 'Rejected', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'completed' => ['label' => 'Completed', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];

    $allKeys = array_keys($statuses);
    $currentIndex = array_search($status, $allKeys);
@endphp

@if($currentIndex !== false)
    <div class="flex items-center gap-0 mb-6 {{ $class }}">
        @foreach($allKeys as $index => $key)
            @php
                $info = $statuses[$key];
                $isActive = $key === $status;
                $isPast = $index < $currentIndex;
                $isFuture = $index > $currentIndex;
            @endphp

            <div class="flex items-center flex-1 @if($isFuture) opacity-40 @endif">
                <div class="flex flex-col items-center relative">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 text-sm
                        @if($isActive) bg-primary-700 border-primary-600 text-white ring-4 ring-primary-500/20
                        @elseif($isPast) bg-green-600 border-green-500 text-white
                        @else bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400
                        @endif">
                        @if($isPast && !$isActive)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <span class="text-xs">{{ $index + 1 }}</span>
                        @endif
                    </div>
                    <span class="mt-2 text-xs font-medium text-center max-w-[60px]
                        @if($isActive) text-primary-700 dark:text-primary-400 font-bold
                        @elseif($isPast) text-green-600 dark:text-green-400
                        @else text-gray-400 dark:text-gray-500
                        @endif">
                        {{ $info['label'] }}
                    </span>
                </div>
                @if(!$isFuture && $index < count($allKeys) - 1)
                    <div class="flex-1 h-0.5 mx-1 @if($isPast) bg-green-600 @else bg-gray-200 dark:bg-gray-700 @endif"></div>
                @endif
            </div>
        @endforeach
    </div>

    @if($status === 'rejected')
        <div class="p-4 bg-red-600/10 border border-red-200 dark:border-red-800 rounded-xl mb-4">
            <p class="font-semibold text-red-600 dark:text-red-400 text-sm">Request Rejected</p>
            {{ $slot }}
        </div>
    @endif
@endif
