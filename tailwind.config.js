/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
  darkMode: 'class', // Use class strategy for dark mode
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/css/**/*.css',
    './resources/**/*.vue',
    './resources/components/**/*.blade.php',
    './storage/framework/views/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/filament/**/*.blade.php',
  ],
  safelist: [
    'bg-amber-500',
    'text-amber-500',
    'hover:bg-amber-600',
    'hover:text-white',
    'dark:bg-black-900',
    'dark:text-amber-500',
    'dark:bg-neutral-900',
    'dark:bg-neutral-800',
    'shadow-md',
    'shadow-2xl',
    'rounded-lg',
    'transition',
    'duration-300',
    'font-bold',
    'font-extrabold',
    'text-2xl',
    'text-4xl',
    'text-5xl',
    'text-7xl',
    'text-lg',
    'min-h-screen',
    'sticky',
    'top-0',
    // Add any other dynamic or rarely used classes you want to guarantee are included
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Instrument Sans', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        primary: '#c83ebf',
        dark: '#0f0f0f',
        light: '#ffffff',
        neutral: {
          50: '#f9fafb',
          100: '#f3f4f6',
          200: '#e5e7eb',
          300: '#d1d5db',
          400: '#9ca3af',
          500: '#6b7280',
          600: '#4b5563',
          700: '#374151',
          800: '#1f2937',
          900: '#111827',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
