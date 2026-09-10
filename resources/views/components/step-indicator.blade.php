@props([
    'steps' => [],
    'currentStep' => 1,
    'totalSteps' => 3,
    'class' => '',
])

<div class="w-full mb-8 {{ $class }}">
    <div class="relative flex items-center justify-between">
        <div class="absolute left-0 right-5 top-5 h-0.5 bg-gray-200 dark:bg-gray-700 z-0"></div>
        <div class="absolute left-0 top-5 h-0.5 bg-primary-600 transition-all duration-500 ease-out z-0"
             style="width: {{ (($currentStep - 1) / ($totalSteps - 1)) * 100 }}%">
        </div>

        @foreach($steps as $index => $step)
            @php
                $stepNum = $index + 1;
                $isActive = $stepNum === $currentStep;
                $isCompleted = $stepNum < $currentStep;
            @endphp

            <div class="flex flex-col items-center relative z-10">
                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 font-bold text-sm transition-all duration-300 shadow-sm
                    @if($isActive) bg-primary-700 border-primary-600 text-white ring-4 ring-primary-500/20
                    @elseif($isCompleted) bg-green-600 border-green-500 text-white
                    @else bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400
                    @endif">
                    @if($isCompleted)
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        {{ $stepNum }}
                    @endif
                </div>
                <span class="mt-3 text-xs font-semibold tracking-wide text-center block transition-colors duration-300
                    @if($isActive) text-primary-700 dark:text-primary-400 font-bold
                    @elseif($isCompleted) text-green-600 dark:text-green-400
                    @else text-gray-500 dark:text-gray-400
                    @endif">
                    {{ $step }}
                </span>
            </div>
        @endforeach
    </div>
</div>
