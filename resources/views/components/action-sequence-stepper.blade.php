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

    <!-- Workflow Sequence Header -->
    <div class="mb-6" x-show="selectedActions.length > 0">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Document Processing Workflow</h3>
            <span class="text-sm text-gray-500" x-text="`${selectedActions.length} step${selectedActions.length !== 1 ? 's' : ''}`"></span>
        </div>
        
        <!-- Flowbite Stepper -->
        <ol class="items-center w-full space-y-4 sm:flex sm:space-x-8 sm:space-y-0 rtl:space-x-reverse">
            <template x-for="(action, index) in selectedActions" :key="`step-${index}-${action.id}`">
                <li class="flex items-center space-x-2.5 rtl:space-x-reverse group relative"
                    :class="getStepClasses(index)">
                    
                    <!-- Step Number Circle -->
                    <span class="flex items-center justify-center w-8 h-8 border rounded-full shrink-0"
                          :class="getStepCircleClasses(index)">
                        <span x-text="index + 1"></span>
                    </span>
                    
                    <!-- Step Content -->
                    <span class="flex-1">
                        <h3 class="font-medium leading-tight" x-text="getActionName(action)"></h3>
                        <p class="text-sm" x-text="getActionStatus(action)"></p>
                    </span>
                    
                    <!-- Remove Button (appears on hover) -->
                    <button
                        type="button"
                        @click="removeStep(index)"
                        class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs hover:bg-red-600 focus:outline-none opacity-0 group-hover:opacity-100 transition-opacity z-10"
                        title="Remove this step">
                        ×
                    </button>
                </li>
            </template>
        </ol>
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

        // === INITIALIZATION METHODS ===
        initFromDataAttributes() {
            this.parseDataAttributes();
            this.setupHiddenField();
            this.initializeListeners();
            this.logInitialization();
        },

        parseDataAttributes() {
            try {
                const element = this.$el;
                
                // Parse office data
                const officeData = element.dataset.office;
                this.office = (officeData && officeData !== 'null') ? JSON.parse(officeData) : null;
                
                // Parse selected actions
                const actionsData = element.dataset.selectedActions;
                this.selectedActions = actionsData ? JSON.parse(actionsData) : [];
                
                // Set configuration
                this.fieldName = element.dataset.fieldName || 'action_sequence';
                this.isModal = element.dataset.isModal === 'true';
                
            } catch (error) {
                console.error('Failed to parse stepper data attributes:', error);
                this.office = null;
                this.selectedActions = [];
            }
        },

        setupHiddenField() {
            this.findHiddenField();
            
            if (!this.hiddenField) {
                setTimeout(() => {
                    this.findHiddenField();
                    this.updateHiddenField();
                }, 100);
            }
            
            this.updateHiddenField();
        },

        initializeListeners() {
            this.listenForHiddenFieldChanges();
            this.listenForFormChanges();
            this.listenForLivewireUpdates();
        },

        logInitialization() {
            console.log('ActionSequenceStepper initialized:', {
                office: this.office?.name || 'No office',
                actions: this.selectedActions.length,
                field: this.fieldName,
                hiddenField: !!this.hiddenField
            });
        },

        // === UI HELPER METHODS FOR FLOWBITE STEPPER ===
        getStepClasses(index) {
            // Apply blue color to active steps, gray to inactive
            if (index === 0 || this.selectedActions.length === 1) {
                return 'text-blue-600 dark:text-blue-500';
            }
            return 'text-gray-500 dark:text-gray-400';
        },

        getStepCircleClasses(index) {
            // Style the step number circles
            if (index === 0 || this.selectedActions.length === 1) {
                return 'border-blue-600 dark:border-blue-500';
            }
            return 'border-gray-500 dark:border-gray-400';
        },

        getActionName(action) {
            return action.name || action.action_name || 'Unknown Action';
        },

        getActionStatus(action) {
            const status = action.status_name || action.resulting_status || 'Processing';
            return `Status: ${status}`;
        },

        // === ACTION MANAGEMENT METHODS ===
        removeStep(index) {
            if (this.isValidIndex(index)) {
                this.selectedActions.splice(index, 1);
                this.syncData();
            }
        },

        clearAll() {
            this.selectedActions = [];
            this.syncData();
        },

        isValidIndex(index) {
            return index >= 0 && index < this.selectedActions.length;
        },

        syncData() {
            this.updateHiddenField();
            this.notifyUpdate();
        },

        // === FIELD MANAGEMENT METHODS ===

        findHiddenField() {
            const strategies = [
                () => document.querySelector(`input[name="${this.fieldName}"]`),
                () => document.querySelector(`input[type="hidden"][name="${this.fieldName}"]`),
                () => this.findFieldInContainer(),
                () => this.findFieldByWireModel(),
                () => this.findFieldByPattern()
            ];

            for (let i = 0; i < strategies.length; i++) {
                try {
                    const field = strategies[i]();
                    if (field) {
                        this.hiddenField = field;
                        console.log(`Hidden field found using strategy ${i + 1}:`, field.name);
                        return;
                    }
                } catch (error) {
                    console.warn(`Strategy ${i + 1} failed:`, error);
                }
            }

            console.warn(`Could not find hidden field for: ${this.fieldName}`);
        },

        findFieldInContainer() {
            const container = this.$el.closest('form') || 
                            this.$el.closest('[data-wizard]') || 
                            this.$el.closest('.filament-form');
            
            return container?.querySelector(`input[name="${this.fieldName}"]`) || 
                   container?.querySelector(`input[type="hidden"][name*="action_sequence"]`);
        },

        findFieldByWireModel() {
            return document.querySelector(`input[wire\\:model="${this.fieldName}"]`) ||
                   document.querySelector(`input[wire\\:model*="action_sequence"]`);
        },

        findFieldByPattern() {
            const hiddenFields = document.querySelectorAll('input[type="hidden"]');
            return Array.from(hiddenFields).find(field => 
                field.name && field.name.includes('action')
            );
        },

        // === EVENT LISTENER METHODS ===
        listenForHiddenFieldChanges() {
            if (!this.hiddenField) return;
            
            const observer = new MutationObserver(() => this.handleHiddenFieldChange());
            observer.observe(this.hiddenField, { 
                attributes: true, 
                attributeFilter: ['value'] 
            });
            
            this.hiddenField.addEventListener('input', (e) => this.handleHiddenFieldInput(e));
        },

        listenForFormChanges() {
            document.addEventListener('input', (e) => {
                if (e.target && e.target.name === this.fieldName) {
                    this.handleFormInput(e);
                }
            });
        },

        listenForLivewireUpdates() {
            if (window.Livewire) {
                window.addEventListener('livewire:updated', () => {
                    this.findHiddenField();
                });
            }
        },

        // === EVENT HANDLERS ===
        handleHiddenFieldChange() {
            try {
                const newData = JSON.parse(this.hiddenField.value || '[]');
                if (JSON.stringify(newData) !== JSON.stringify(this.selectedActions)) {
                    this.selectedActions = [...newData];
                }
            } catch (error) {
                console.warn('Failed to parse hidden field data:', error);
            }
        },

        handleHiddenFieldInput(event) {
            try {
                const newData = JSON.parse(event.target.value || '[]');
                if (JSON.stringify(newData) !== JSON.stringify(this.selectedActions)) {
                    this.selectedActions = [...newData];
                }
            } catch (error) {
                console.warn('Failed to parse field data from input event:', error);
            }
        },

        handleFormInput(event) {
            try {
                const newData = JSON.parse(event.target.value || '[]');
                if (JSON.stringify(newData) !== JSON.stringify(this.selectedActions)) {
                    this.selectedActions = [...newData];
                }
            } catch (error) {
                console.warn('Failed to parse field data from form input event:', error);
            }
        },

        // === DATA SYNCHRONIZATION METHODS ===
        updateHiddenField() {
            if (!this.hiddenField) {
                this.findHiddenField();
                if (!this.hiddenField) return;
            }
            
            try {
                this.hiddenField.value = JSON.stringify(this.selectedActions);
                this.triggerFieldEvents();
            } catch (error) {
                console.error('Error updating hidden field:', error);
            }
        },

        triggerFieldEvents() {
            this.hiddenField.dispatchEvent(new Event('change', { bubbles: true }));
            this.hiddenField.dispatchEvent(new Event('input', { bubbles: true }));
        },

        notifyUpdate() {
            this.notifyLivewire();
            this.dispatchCustomEvent();
        },

        notifyLivewire() {
            if (window.Livewire) {
                try {
                    this.$wire?.set?.(this.fieldName, this.selectedActions);
                } catch (error) {
                    console.log('Livewire update failed, using fallback');
                }
            }
        },

        dispatchCustomEvent() {
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