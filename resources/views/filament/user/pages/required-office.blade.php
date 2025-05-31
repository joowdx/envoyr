<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
    <div class="max-w-md w-full p-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <div class="flex flex-col items-center justify-center space-y-6">
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-primary-50 dark:bg-primary-500/10">
                <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <div class="text-center space-y-2">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Access Denied
                </h2>

                <p class="text-base text-gray-500 dark:text-gray-400">
                    You must be associated with both an office and a section to access this page.
                </p>
            </div>

            @if($this->isAdministrator())
                <div class="w-full pt-4">
                    <div class="bg-primary-50 dark:bg-primary-500/10 rounded-lg p-4">
                        <p class="text-sm text-primary-700 dark:text-primary-300 text-center">
                            As an administrator, you can assign a section to yourself.
                        </p>
                    </div>
                </div>

                <form wire:submit="assign" class="w-full space-y-4">
                    {{ $this->form }}

                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-center text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors duration-200">
                        Assign
                    </button>
                </form>
            @else
                <div class="w-full pt-4">
                    <div class="bg-primary-50 dark:bg-primary-500/10 rounded-lg p-4">
                        <p class="text-sm text-primary-700 dark:text-primary-300 text-center">
                            Please contact your administrator to request office and section assignment.
                        </p>
                    </div>
                </div>

                <div class="w-full pt-4">
                    {{ $this->logoutAction }}
                </div>
            @endif
        </div>
    </div>
</div> 