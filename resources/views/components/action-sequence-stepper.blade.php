@props([
    'office' => null,
    'selectedActions' => [],
    'isModal' => false,
    'fieldName' => 'action_sequence'
])

<div 
    class="action-sequence-stepper w-full"
    data-office="{!! htmlspecialchars(json_encode($office), ENT_QUOTES, 'UTF-8') !!}"
    data-selected-actions="{!! htmlspecialchars(json_encode($selectedActions), ENT_QUOTES, 'UTF-8') !!}"
    data-field-name="{{ $fieldName }}"
    data-is-modal="{{ $isModal ? 'true' : 'false' }}"
    x-data="actionSequenceStepper()"
    x-init="initFromDataAttributes()"
>
    <!-- Debug Information -->
    @if(config('app.debug'))
    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm">
        <div class="font-semibold text-yellow-800 mb-1">🐛 Debug Info:</div>
        <div class="text-yellow-700">
            <div>PHP Office Data: {{ json_encode($office) }}</div>
            <div>JS Office: <span x-text="office?.name || 'No office data'"></span></div>
            <div>Office ID: <span x-text="office?.id || 'No ID'"></span></div>
            <div>Actions Count: <span x-text="selectedActions.length"></span></div>
            <div>Field Name: {{ $fieldName }}</div>
            <div>Field Found: <span x-text="hiddenField ? 'Yes (' + hiddenField.name + ')' : 'No'"></span></div>
            <div>Component Config: <span x-text="JSON.stringify({office: office, fieldName: '{{ $fieldName }}'})"></span></div>
        </div>
    </div>
    @endif

    <!-- Stepper Progress Indicator -->
    <div class="mb-6" x-show="selectedActions.length > 0">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Workflow Sequence</h3>
            <span class="text-sm text-gray-500" x-text="`${selectedActions.length} step${selectedActions.length !== 1 ? 's' : ''}`"></span>
        </div>
        
        <!-- Steps Display -->
        <div class="space-y-3">
            <template x-for="(action, index) in selectedActions" :key="`step-${index}-${action.id}`">
                <div class="flex items-center p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                    <!-- Step Number -->
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full flex items-center justify-center text-sm font-semibold mr-3">
                        <span x-text="index + 1"></span>
                    </div>
                    
                    <!-- Action Details -->
                    <div class="flex-grow">
                        <div class="font-medium text-gray-900 dark:text-white" x-text="action.name"></div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Status: <span x-text="action.status_name || 'Processing'"></span>
                        </div>
                    </div>
                    
                    <!-- Remove Button -->
                    <button
                        type="button"
                        @click="removeStep(index)"
                        class="flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        title="Remove this step"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="selectedActions.length === 0" 
         x-transition
         class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Actions Added</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm max-w-sm mx-auto">
            Use the dropdown above to select and add actions to your workflow sequence. 
            Each action you select will appear here as a step in your workflow.
        </p>
        
        <!-- Debug info in empty state -->
        @if(config('app.debug'))
        <div class="mt-4 text-xs text-gray-400">
            <div>Office available: <span x-text="office ? 'Yes' : 'No'"></span></div>
            <div>Actions: <span x-text="selectedActions.length"></span></div>
        </div>
        @endif
    </div>

    <!-- Clear All Button -->
    <div x-show="selectedActions.length > 0" class="mt-4 text-center">
        <button
            type="button"
            @click="clearAll()"
            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium underline"
        >
            Clear All Actions
        </button>
    </div>
</div>

<script>
function actionSequenceStepper() {
    return {
        office: null,
        selectedActions: [],
        fieldName: 'action_sequence',
        isModal: false,
        hiddenField: null,

function actionSequenceStepper() {
    return {
        office: null,
        selectedActions: [],
        fieldName: 'action_sequence',
        isModal: false,
        hiddenField: null,

        initFromDataAttributes() {
            console.log('Initializing stepper from data attributes...');
            console.log('Data attributes:', {
                office: this.$el.dataset.office,
                selectedActions: this.$el.dataset.selectedActions,
                fieldName: this.$el.dataset.fieldName,
                isModal: this.$el.dataset.isModal
            });
            
            // Read data from data attributes
            try {
                const officeData = this.$el.dataset.office;
                if (officeData && officeData !== 'null') {
                    this.office = JSON.parse(officeData);
                    console.log('Parsed office data:', this.office);
                } else {
                    console.warn('No office data found in data attributes');
                    this.office = null;
                }
                
                const actionsData = this.$el.dataset.selectedActions;
                if (actionsData) {
                    this.selectedActions = JSON.parse(actionsData);
                } else {
                    this.selectedActions = [];
                }
                
                this.fieldName = this.$el.dataset.fieldName || 'action_sequence';
                this.isModal = this.$el.dataset.isModal === 'true';
                
                console.log('Stepper config after parsing:', {
                    office: this.office,
                    selectedActions: this.selectedActions,
                    fieldName: this.fieldName,
                    isModal: this.isModal
                });
                
            } catch (e) {
                console.error('Failed to parse data attributes:', e);
                console.log('Raw data attributes:', this.$el.dataset);
                this.office = null;
                this.selectedActions = [];
            }
            
            // Find the hidden field
            this.findHiddenField();
            
            // If not found immediately, try again after a short delay (for dynamic content)
            if (!this.hiddenField) {
                setTimeout(() => {
                    this.findHiddenField();
                    this.updateHiddenField();
                }, 100);
            }
            
            // Update the hidden field with initial data
            this.updateHiddenField();
            
            // Listen for external updates (e.g., from Select component)
            this.listenForUpdates();
            
            // Debug: log office data
            console.log('Stepper initialized with office:', this.office);
            console.log('Initial selected actions:', this.selectedActions);
        },

        findHiddenField() {
            // Try multiple strategies to find the field
            const strategies = [
                () => document.querySelector(`input[name="${this.fieldName}"]`),
                () => document.querySelector(`input[type="hidden"][name="${this.fieldName}"]`),
                () => document.querySelector(`input[type="hidden"][name*="action_sequence"]`),
                () => document.querySelector('input[type="hidden"][name*="action"]'),
                () => {
                    // Look within the stepper container and parent forms
                    const container = this.$el.closest('form') || this.$el.closest('[data-wizard]') || this.$el.closest('.filament-form');
                    return container?.querySelector(`input[name="${this.fieldName}"]`) || 
                           container?.querySelector(`input[type="hidden"][name*="action_sequence"]`);
                },
                () => {
                    // Look for Livewire wire:model fields
                    return document.querySelector(`input[wire\\:model="${this.fieldName}"]`) ||
                           document.querySelector(`input[wire\\:model*="action_sequence"]`);
                },
                () => {
                    // Broader search in document
                    const allHiddenFields = document.querySelectorAll('input[type="hidden"]');
                    for (const field of allHiddenFields) {
                        if (field.name && field.name.includes('action')) {
                            console.log('Found hidden field with action in name:', field.name, field);
                            return field;
                        }
                    }
                    return null;
                }
            ];

            for (let i = 0; i < strategies.length; i++) {
                try {
                    const field = strategies[i]();
                    if (field) {
                        this.hiddenField = field;
                        console.log(`Found hidden field using strategy ${i + 1}:`, field);
                        break;
                    }
                } catch (e) {
                    console.warn(`Strategy ${i + 1} failed:`, e);
                }
            }

            if (!this.hiddenField) {
                console.warn(`Could not find hidden field for: ${this.fieldName}`);
                console.log('Available hidden fields:', Array.from(document.querySelectorAll('input[type="hidden"]')).map(f => f.name));
            }
        },

        listenForUpdates() {
            console.log('Setting up listeners for updates...');
            
            // Listen for changes to the hidden field
            if (this.hiddenField) {
                console.log('Setting up MutationObserver for hidden field:', this.hiddenField);
                const observer = new MutationObserver(() => {
                    console.log('Hidden field changed, new value:', this.hiddenField.value);
                    try {
                        const newData = JSON.parse(this.hiddenField.value || '[]');
                        if (JSON.stringify(newData) !== JSON.stringify(this.selectedActions)) {
                            console.log('Updating selectedActions from hidden field:', newData);
                            this.selectedActions = [...newData];
                        }
                    } catch (e) {
                        console.warn('Failed to parse hidden field data:', e);
                    }
                });
                
                observer.observe(this.hiddenField, { 
                    attributes: true, 
                    attributeFilter: ['value'] 
                });
                
                // Also listen for input events
                this.hiddenField.addEventListener('input', (e) => {
                    console.log('Hidden field input event:', e.target.value);
                    try {
                        const newData = JSON.parse(e.target.value || '[]');
                        if (JSON.stringify(newData) !== JSON.stringify(this.selectedActions)) {
                            this.selectedActions = [...newData];
                        }
                    } catch (err) {
                        console.warn('Failed to parse field data from input event:', err);
                    }
                });
            }

            // Listen for form changes that might affect our field
            document.addEventListener('input', (e) => {
                if (e.target && e.target.name === this.fieldName) {
                    console.log('Form input event for our field:', e.target.value);
                    try {
                        const newData = JSON.parse(e.target.value || '[]');
                        if (JSON.stringify(newData) !== JSON.stringify(this.selectedActions)) {
                            this.selectedActions = [...newData];
                        }
                    } catch (err) {
                        console.warn('Failed to parse field data from form input event:', err);
                    }
                }
            });

            // Listen for Livewire updates
            if (window.Livewire) {
                console.log('Setting up Livewire listeners...');
                window.addEventListener('livewire:updated', () => {
                    console.log('Livewire updated, re-checking hidden field...');
                    this.findHiddenField();
                });
            }
        },

        removeStep(index) {
            if (index >= 0 && index < this.selectedActions.length) {
                this.selectedActions.splice(index, 1);
                this.updateHiddenField();
                this.notifyUpdate();
            }
        },

        clearAll() {
            this.selectedActions = [];
            this.updateHiddenField();
            this.notifyUpdate();
        },

        updateHiddenField() {
            if (this.hiddenField) {
                this.hiddenField.value = JSON.stringify(this.selectedActions);
                
                // Trigger change event for Livewire
                this.hiddenField.dispatchEvent(new Event('change', { bubbles: true }));
                this.hiddenField.dispatchEvent(new Event('input', { bubbles: true }));
            } else {
                // Try to find the field again
                this.findHiddenField();
                if (this.hiddenField) {
                    this.hiddenField.value = JSON.stringify(this.selectedActions);
                    this.hiddenField.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        },

        notifyUpdate() {
            // Notify Livewire if available
            if (window.Livewire) {
                try {
                    this.$wire?.set?.(this.fieldName, this.selectedActions);
                } catch (e) {
                    console.log('Livewire update failed, using fallback');
                }
            }

            // Custom event for other components
            window.dispatchEvent(new CustomEvent('stepper-updated', {
                detail: {
                    selectedActions: this.selectedActions,
                    fieldName: this.fieldName
                }
            }));
        }
    };
}
</script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('actionSequenceStepper', (config) => ({
        selectedActions: config.selectedActions || [],
        availableActions: config.availableActions || [],
        currentStep: config.currentStep || 1,
        canComplete: config.canComplete || false,
        callbacks: config.callbacks || {},

        init() {
            this.updateState();
            console.log('Stepper initialized:', {
                selectedActions: this.selectedActions.length,
                availableActions: this.availableActions.length
            });
        },

        selectAction(action) {
            // Add action to sequence
            this.selectedActions.push(action);
            
            // Update available actions
            this.updateAvailableActions();
            this.updateState();
            this.updateHiddenField();
            
            // Call callback if provided
            if (this.callbacks.onActionSelect) {
                window[this.callbacks.onActionSelect]?.(action, this.selectedActions);
            }
            
            console.log('Action selected:', action.name, 'Total:', this.selectedActions.length);
        },

        removeStep(index) {
            // Remove action from sequence
            const removedAction = this.selectedActions.splice(index, 1)[0];
            
            // Update available actions
            this.updateAvailableActions();
            this.updateState();
            this.updateHiddenField();
            
            // Call callback if provided
            if (this.callbacks.onStepRemove) {
                window[this.callbacks.onStepRemove]?.(index, this.selectedActions);
            }
            
            console.log('Action removed:', removedAction?.name, 'Total:', this.selectedActions.length);
        },

        resetSequence() {
            this.selectedActions = [];
            this.updateAvailableActions();
            this.updateState();
            this.updateHiddenField();
            console.log('Sequence reset');
        },

        completeSequence() {
            if (!this.canComplete) return;
            
            this.updateHiddenField();
            
            // Call callback if provided
            if (this.callbacks.onSequenceComplete) {
                window[this.callbacks.onSequenceComplete]?.(this.selectedActions);
            }
            
            // Dispatch event for Filament
            this.$dispatch('sequence-completed', {
                actions: this.selectedActions,
                sequence: this.selectedActions.map((action, index) => ({
                    step: index + 1,
                    action_type_id: action.id,
                    action_name: action.name,
                    status_name: action.status_name
                }))
            });
            
            // Show success message
            this.$dispatch('notify', {
                type: 'success',
                message: `Action sequence applied with ${this.selectedActions.length} steps`
            });
            
            console.log('Sequence completed:', this.selectedActions.length, 'steps');
        },

        updateAvailableActions() {
            // Filter out selected actions
            const selectedIds = this.selectedActions.map(a => a.id);
            
            this.availableActions = config.availableActions.filter(action => {
                // Don't include already selected actions
                return !selectedIds.includes(action.id);
            });
        },

        updateState() {
            this.currentStep = this.selectedActions.length + 1;
            this.canComplete = this.selectedActions.length > 0;
        },

        updateHiddenField() {
            // Update the hidden field with the action sequence (try multiple field name patterns)
            const fieldSelectors = [
                'input[name="action_sequence"]',
                'input[name="data.action_sequence"]',
                'input[data-field-name="action_sequence"]',
                'input[wire\\:model="data.action_sequence"]'
            ];
            
            let hiddenField = null;
            for (const selector of fieldSelectors) {
                hiddenField = document.querySelector(selector);
                if (hiddenField) break;
            }
            
            if (hiddenField) {
                hiddenField.value = JSON.stringify(this.selectedActions);
                hiddenField.dispatchEvent(new Event('input', { bubbles: true }));
                hiddenField.dispatchEvent(new Event('change', { bubbles: true }));
                console.log('Hidden field updated:', hiddenField.name);
            } else {
                console.log('Hidden field not found, available fields:', 
                    Array.from(document.querySelectorAll('input[type="hidden"]')).map(f => f.name)
                );
            }
            
            // Also try to set via Livewire if available
            if (window.Livewire) {
                try {
                    const component = window.Livewire.first();
                    if (component) {
                        component.set('data.action_sequence', JSON.stringify(this.selectedActions));
                        console.log('Livewire updated successfully');
                    }
                } catch (e) {
                    console.log('Livewire update failed:', e.message);
                }
            }
        }
    }));
});
</script>