<div>
    @if(!empty($getSelectedActions()))
        @include('components.workflow-stepper', [
            'selectedActions' => $getSelectedActions(),
            'actionTypes' => $getActionTypes()
        ])
    @else
        <div class="text-sm text-gray-500 italic p-4 text-center bg-gray-50 rounded-lg">
            Select actions above to see workflow preview
        </div>
    @endif
</div>