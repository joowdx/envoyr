<div
    x-data="{ theme: null }"
    x-init="
        theme = localStorage.getItem('theme') || @js(config('filament.default_theme_mode'));
        $watch('theme', (value) => {
            localStorage.setItem('theme', value);
            document.documentElement.className = value === 'dark' ? 'dark' : 'light';
        });
    "
    class="fi-theme-switcher grid grid-flow-col gap-x-1"
>
    <button
        aria-label="{{ __("filament-panels::layout.actions.theme_switcher.light.label") }}"
        type="button"
        x-on:click="theme = 'light'"
        class="fi-theme-switcher-btn flex justify-center rounded-md p-2 outline-none transition duration-75 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5"
        x-bind:class="
            theme === 'light'
                ? 'fi-active bg-gray-50 text-primary-500 dark:bg-white/5 dark:text-primary-400'
                : 'text-gray-400 hover:text-gray-500 focus-visible:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:text-gray-400'
        "
    >
        <i class="fas fa-sun h-5 w-5"></i>
    </button>

    <button
        aria-label="{{ __("filament-panels::layout.actions.theme_switcher.dark.label") }}"
        type="button"
        x-on:click="theme = 'dark'"
        class="fi-theme-switcher-btn flex justify-center rounded-md p-2 outline-none transition duration-75 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5"
        x-bind:class="
            theme === 'dark'
                ? 'fi-active bg-gray-50 text-primary-500 dark:bg-white/5 dark:text-primary-400'
                : 'text-gray-400 hover:text-gray-500 focus-visible:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:text-gray-400'
        "
    >
        <i class="fas fa-moon h-5 w-5"></i>
    </button>

    <button
        aria-label="{{ __("filament-panels::layout.actions.theme_switcher.system.label") }}"
        type="button"
        x-on:click="theme = 'system'"
        class="fi-theme-switcher-btn flex justify-center rounded-md p-2 outline-none transition duration-75 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5"
        x-bind:class="
            theme === 'system'
                ? 'fi-active bg-gray-50 text-primary-500 dark:bg-white/5 dark:text-primary-400'
                : 'text-gray-400 hover:text-gray-500 focus-visible:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:text-gray-400'
        "
    >
        <i class="fas fa-desktop h-5 w-5"></i>
    </button>
</div>