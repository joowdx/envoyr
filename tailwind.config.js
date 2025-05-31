/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', // Enable dark mode
  content: [
    './resources/views/**/*.blade.php', // Blade templates
    './resources/js/**/*.js',          // JavaScript files
    './resources/**/*.vue',            // Vue files
    './resources/components/**/*.blade.php', // Blade components
    './storage/framework/views/*.php', // Cached views
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php', // Laravel pagination views
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'], // Custom font family
      },
      colors: {
        primary: '#c83ebf', // Custom primary color
        dark: '#0f0f0f',    // Custom dark color
        light: '#ffffff',   // Custom light color
        grayish: '#1f1f1f', // Custom grayish color
      },
    },
  },
  plugins: [],
};