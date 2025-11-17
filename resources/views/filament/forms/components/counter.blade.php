<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{ 
            state: $wire.$entangle(@js($getStatePath())),
            min: @js($getMinValue() ?? 1),
            max: @js($getMaxValue() ?? 1000),
            step: @js($getStep() ?? 1),
            disabled: @js($isDisabled()),
            increment() {
                if (!this.disabled && this.state < this.max) {
                    this.state += this.step;
                }
            },
            decrement() {
                if (!this.disabled && this.state > this.min) {
                    this.state -= this.step;
                }
            },
            validateInput() {
                if (this.state < this.min) this.state = this.min;
                if (this.state > this.max) this.state = this.max;
                if (this.state < 1) this.state = 1;
            },
            blockNonNumeric(event) {
                const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'];
                
                if (allowedKeys.includes(event.key)) {
                    return;
                }
                
                if (!/[0-9]/.test(event.key)) {
                    event.preventDefault();
                }
            }
        }"
        {{ $getExtraAttributeBag()->class([
            'fi-fo-counter flex items-center gap-2',
        ]) }}
    >
        <!-- Input Field -->
        <input
            x-model.number="state"
            x-on:blur="validateInput()"
            x-on:keydown="blockNonNumeric($event)"
            :disabled="disabled"
            min="1"
            :max="max"
            :step="step"
            :class="{
                'fi-input block w-20 rounded-lg border-gray-300 bg-white text-gray-900 text-center shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500': !disabled,
                'fi-input block w-20 rounded-lg border-gray-300 bg-gray-100 text-gray-500 text-center shadow-sm cursor-not-allowed': disabled
            }"
        />

        <!-- Stacked Buttons -->
        <div class="flex flex-col">
            <!-- Increment Button -->
            <button
                type="button"
                x-on:click="increment()"
                :disabled="disabled || state >= max"
                class="flex h-5 w-6 items-center justify-center rounded-t-md border border-gray-300 bg-gray-50 text-gray-600 text-xs shadow-sm transition-colors hover:bg-gray-100 hover:text-gray-800 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/20 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                </svg>
            </button>

            <!-- Decrement Button -->
            <button
                type="button"
                x-on:click="decrement()"
                :disabled="disabled || state <= 1"
                class="flex h-5 w-6 items-center justify-center rounded-b-md border border-gray-300 border-t-0 bg-gray-50 text-gray-600 text-xs shadow-sm transition-colors hover:bg-gray-100 hover:text-gray-800 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/20 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>
    </div>
</x-dynamic-component>
