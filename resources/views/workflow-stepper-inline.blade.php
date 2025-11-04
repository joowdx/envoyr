@if(!empty($selectedActions))
<div class="workflow-stepper-container mt-4 mb-6 p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">🔄 Workflow Preview</h3>
        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300">
            {{ count($selectedActions) }} step{{ count($selectedActions) > 1 ? 's' : '' }}
        </div>
    </div>
    
    <!-- Filament-themed Horizontal Stepper -->
    <div class="w-full overflow-x-auto pb-4">
        <div class="relative flex items-center min-w-max py-8">
            @foreach($selectedActions as $index => $actionId)
                @php
                    $action = $actions->get($actionId);
                    $stepNumber = $index + 1;
                    $isLast = $index === count($selectedActions) - 1;
                @endphp
                
                <!-- Step Container -->
                <div class="relative flex flex-col items-center z-20" style="min-width: 150px;">
                    <!-- Step Circle -->
                    <div class="w-12 h-12 rounded-full bg-pink-500 border-4 border-white dark:border-gray-900 text-white flex items-center justify-center text-lg font-bold shadow-lg" 
                         title="{{ $action?->name ?? 'Unknown Action' }}">
                        {{ $stepNumber }}
                    </div>
                    <!-- Step Label -->
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-3 text-center leading-tight">
                        {{ $action?->name ?? 'Unknown Action' }}
                    </div>
                    
                    <!-- Connecting Line inside each step -->
                    @if(!$isLast)
                    <div class="absolute top-6 left-full w-16 h-1 bg-pink-500 z-10" style="transform: translateX(-8px);"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    
    @if(count($selectedActions) > 0)
    <div class="mt-6 text-xs text-gray-600 dark:text-gray-400">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-3 h-3 rounded-full bg-pink-500"></div>
            <span class="font-medium">Process Sequence ({{ count($selectedActions) }} actions)</span>
        </div>
        <div class="grid gap-2">
            @foreach($selectedActions as $index => $actionId)
                @php
                    $action = $actions->get($actionId);
                @endphp
                <div class="flex items-center gap-3 p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs">
                    <span class="w-6 h-6 rounded-full bg-pink-500 text-white flex items-center justify-center text-[10px] font-bold">{{ $index + 1 }}</span>
                    <span class="flex-1 font-medium text-gray-900 dark:text-gray-100">{{ $action?->name ?? 'Unknown Action' }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<style>
/* Horizontal stepper styling */
.workflow-stepper-container {
    /* No additional stepper-specific styles needed */
}

/* Hover effects for step circles */
.workflow-stepper-container .w-12:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(236, 72, 153, 0.6);
    transition: all 0.3s ease;
}

/* Animation for step appearance */
@keyframes stepFadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.workflow-stepper-container .relative.flex.flex-col {
    animation: stepFadeIn 0.5s ease-out;
    animation-fill-mode: both;
}

.workflow-stepper-container .relative.flex.flex-col:nth-child(1) { animation-delay: 0.1s; }
.workflow-stepper-container .relative.flex.flex-col:nth-child(2) { animation-delay: 0.2s; }
.workflow-stepper-container .relative.flex.flex-col:nth-child(3) { animation-delay: 0.3s; }
.workflow-stepper-container .relative.flex.flex-col:nth-child(4) { animation-delay: 0.4s; }
.workflow-stepper-container .relative.flex.flex-col:nth-child(5) { animation-delay: 0.5s; }

/* Connecting line animation */
@keyframes lineGrow {
    from {
        width: 0;
    }
    to {
        width: 4rem; /* 64px */
    }
}

.workflow-stepper-container .absolute.bg-pink-500 {
    animation: lineGrow 0.8s ease-out;
    animation-fill-mode: both;
    animation-delay: 0.6s;
}
</style>
@endif