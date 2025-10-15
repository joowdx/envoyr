@props(['selectedActions' => [], 'actionTypes' => collect(), 'title' => 'Workflow Preview', 'validationError' => null, 'wasReordered' => false])

@if($validationError)
<div class="workflow-stepper-container mt-4 mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
    <div class="flex items-center gap-2 text-red-800">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <h4 class="font-semibold">Workflow Validation Error</h4>
            <p class="text-sm mt-1">{{ $validationError }}</p>
        </div>
    </div>
</div>
@elseif(!empty($selectedActions))
<div class="workflow-stepper-container mt-4 mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
    @if($wasReordered)
    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center gap-2 text-blue-800 text-sm">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">Actions automatically reordered to respect prerequisites</span>
        </div>
    </div>
    @endif
    
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
            {{ count($selectedActions) }} step{{ count($selectedActions) > 1 ? 's' : '' }}
        </div>
    </div>
    
    <div class="w-full overflow-x-auto pb-4">
        @php
            $stepCount = count($selectedActions);
            $stepWidth = $stepCount <= 3 ? '140px' : ($stepCount <= 5 ? '120px' : '100px');
            $containerWidth = $stepCount * (int)str_replace('px', '', $stepWidth) + 100;
        @endphp
        
        <div class="relative py-6" style="min-width: {{ $containerWidth }}px;">
            @if($stepCount > 1)
            <div class="absolute top-6 left-0 right-0 flex items-center justify-center h-12">
                <div class="flex items-center w-full" style="padding: 0 {{ (int)str_replace('px', '', $stepWidth) / 2 }}px;">
                    @for($i = 0; $i < $stepCount - 1; $i++)
                        <div class="flex-1 h-1 bg-gray-300 mx-2"></div>
                        @if($i < $stepCount - 2)
                            <div class="w-12"></div>
                        @endif
                    @endfor
                </div>
                <div class="absolute top-1/2 left-0 right-0 flex items-center justify-center -translate-y-1/2">
                    <div class="flex items-center w-full" style="padding: 0 {{ (int)str_replace('px', '', $stepWidth) / 2 }}px;">
                        @for($i = 0; $i < $stepCount - 1; $i++)
                            <div class="flex-1 h-1 bg-pink-500 mx-2 stepper-progress-line" style="animation-delay: {{ 0.8 + ($i * 0.2) }}s;"></div>
                            @if($i < $stepCount - 2)
                                <div class="w-12"></div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
            @endif
            
            <div class="flex items-start justify-between relative z-20" style="gap: 20px;">
                @foreach($selectedActions as $index => $actionId)
                    @php
                        $action = $actionTypes->get($actionId);
                        $stepNumber = $index + 1;
                    @endphp
                    
                    <div class="flex flex-col items-center text-center relative group" style="width: {{ $stepWidth }};">
                        <!-- Simple Hover Tooltip -->
                        <div class="tooltip-content absolute -top-12 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded-lg px-3 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-30 whitespace-nowrap shadow-lg">
                            <div class="font-semibold">{{ $action?->name ?? 'Unknown Action' }}</div>
                            <div class="text-gray-200 mt-1">Status: {{ $action?->status_name ?? 'No Status' }}</div>
                            <!-- Tooltip arrow -->
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                        </div>
                        
                        <div class="w-12 h-12 rounded-full bg-pink-500 border-4 border-white text-white flex items-center justify-center text-sm font-bold shadow-lg step-circle transition-all duration-200" 
                             title="{{ $action?->name ?? 'Unknown Action' }}"
                             style="animation-delay: {{ 0.1 + ($index * 0.1) }}s;">
                            {{ $stepNumber }}
                        </div>
                        
                        <div class="text-xs font-medium text-gray-900 mt-3 text-center leading-tight px-1" 
                             style="animation-delay: {{ 0.2 + ($index * 0.1) }}s;">
                            {{ $action?->name ?? 'Unknown Action' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.workflow-stepper-container .step-circle:hover {
    transform: scale(1.15);
    box-shadow: 0 8px 25px rgba(236, 72, 153, 0.6);
    transition: all 0.3s ease;
}

/* Light themed tooltip styling */
.workflow-stepper-container .tooltip-content {
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease-out;
    transform: translate(-50%, 5px);
    background-color: #374151;
    border: 1px solid #d1d5db;
}

.workflow-stepper-container .group:hover .tooltip-content {
    opacity: 1;
    visibility: visible;
    transform: translate(-50%, 0);
}

@keyframes stepSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.workflow-stepper-container .step-circle,
.workflow-stepper-container .text-xs {
    animation: stepSlideIn 0.4s ease-out;
    animation-fill-mode: both;
}

@keyframes progressGrow {
    from {
        width: 0;
        opacity: 0;
    }
    to {
        width: 100%;
        opacity: 1;
    }
}

.workflow-stepper-container .stepper-progress-line {
    animation: progressGrow 0.8s ease-out;
    animation-fill-mode: both;
}

/* Light theme focus styles */
.workflow-stepper-container .step-circle:focus {
    outline: 2px solid #ec4899;
    outline-offset: 2px;
}
</style>
@endif
