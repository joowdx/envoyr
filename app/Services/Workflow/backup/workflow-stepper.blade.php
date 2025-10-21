@props([
    'selectedActions' => [], 
    'actionTypes' => collect(), 
    'title' => 'Workflow Preview', 
    'validationError' => null, 
    'wasReordered' => false,
    'reorderMessage' => null
])

@if($validationError)
<div class="workflow-stepper-container mt-4 mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
    <div class="flex items-start gap-3 text-red-800">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <div class="flex-grow">
            <h4 class="font-semibold text-red-900">Workflow Validation Error</h4>
            <p class="text-sm mt-1 text-red-700">{{ $validationError }}</p>
        </div>
    </div>
</div>
@elseif(!empty($selectedActions))
<div class="workflow-stepper-container mt-4 mb-6 p-6 bg-gray-25 rounded-lg border border-gray-100 shadow-sm">
    {{-- Reorder notification --}}
    @if($wasReordered && $reorderMessage)
    <div class="mb-4 p-3 bg-blue-25 border border-blue-100 rounded-lg">
        <div class="flex items-start gap-2 text-blue-700 text-sm">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">{{ $reorderMessage }}</span>
        </div>
    </div>
    @endif
    
    {{-- Simple header --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
            {{ count($selectedActions) }} step{{ count($selectedActions) > 1 ? 's' : '' }}
        </div>
    </div>
    
    <div class="w-full p-4">
        <ul class="steps w-full" style="--p: #ec4899; --pc: #ffffff;">
            @foreach($selectedActions as $index => $actionId)
                @php
                    // Ensure actionId is a valid scalar value for collection access
                    $actionIdKey = is_scalar($actionId) ? (string) $actionId : null;
                    $action = $actionIdKey ? $actionTypes->get($actionIdKey) : null;
                    $hasPrerequisites = $action && $action->prerequisites->isNotEmpty();
                    $prereqInSelection = [];
                    if ($hasPrerequisites) {
                        $prereqInSelection = $action->prerequisites->whereIn('id', $selectedActions)->pluck('name')->toArray();
                    }
                @endphp
                <li class="step step-primary" @if($hasPrerequisites && !empty($prereqInSelection)) title="Depends on: {{ implode(', ', $prereqInSelection) }}" @endif>
                    <div class="flex flex-col items-center">
                        <span class="text-xs font-medium">{{ $action?->name ?? 'Unknown Action' }}</span>
                        @if($hasPrerequisites && !empty($prereqInSelection))
                            <span class="text-xs text-gray-500 mt-1">
                                🔗 {{ count($prereqInSelection) === 1 ? 'Requires' : 'After' }}: {{ implode(', ', array_slice($prereqInSelection, 0, 2)) }}{{ count($prereqInSelection) > 2 ? '...' : '' }}
                            </span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
        
        @if(collect($selectedActions)->map(fn($id) => is_scalar($id) ? $actionTypes->get((string) $id) : null)->filter(fn($action) => $action && $action->prerequisites->isNotEmpty())->count() > 0)
        <div class="mt-3 p-2 bg-blue-50 border border-blue-100 rounded text-xs text-blue-700">
            💡 <strong>Tip:</strong> Actions with 🔗 symbols have prerequisites that must be completed first. The workflow will automatically ensure proper order.
        </div>
        @endif
    </div>
</div>

<style>
/* Custom ultra-light background colors */
.bg-gray-25 {
    background-color: #fefefe;
}

.bg-blue-25 {
    background-color: #f8faff;
}

.border-gray-100 {
    border-color: #f3f4f6;
}

.border-blue-100 {
    border-color: #dbeafe;
}

.text-blue-700 {
    color: #1d4ed8;
}

/* Ensure pink color for DaisyUI steps */
.workflow-stepper-container .steps .step-primary::before {
    background-color: #ec4899 !important;
    border-color: #ec4899 !important;
    border: 2px solid #ec4899 !important;
}

.workflow-stepper-container .steps .step-primary::after {
    background-color: #ec4899 !important;
    border-color: #ec4899 !important;
}

.workflow-stepper-container .steps .step-primary {
    color: #ec4899 !important;
    border-color: #ec4899 !important;
}

/* Remove any violet/purple outlines and borders */
.workflow-stepper-container .steps .step {
    border-color: #ec4899 !important;
    outline: none !important;
    box-shadow: none !important;
}

.workflow-stepper-container .steps .step:focus {
    outline: none !important;
    box-shadow: 0 0 0 2px #ec4899 !important;
    border-color: #ec4899 !important;
}

.workflow-stepper-container .steps .step:hover {
    border-color: #ec4899 !important;
    outline: none !important;
}

/* Override DaisyUI primary color variables for steps */
.workflow-stepper-container .steps {
    --step-color: #ec4899;
    --step-bg: #ec4899;
    --step-border-color: #ec4899;
}

.workflow-stepper-container .step-primary {
    --tw-text-opacity: 1 !important;
    color: rgb(236 72 153 / var(--tw-text-opacity)) !important;
    border-color: #ec4899 !important;
}

/* Enhanced stepper for prerequisites */
.workflow-stepper-container .step {
    min-height: 4rem;
    padding: 0.5rem;
}

.workflow-stepper-container .step .flex.flex-col {
    min-height: 2.5rem;
    justify-content: center;
}

</style>
@endif
