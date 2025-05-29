/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
      './resources/views/**/*.blade.php',
      './resources/js/**/*.js',
      './resources/js/**/*.vue',
      './resources/components/**/*.blade.php',
      './storage/framework/views/*.php',
      './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
  darkMode: 'class', // Enable dark mode
  theme: {
    extend: {
      colors: {
        primary: '#c83ebf',
        dark: '#0f0f0f',
        light: '#ffffff',
        grayish: '#1f1f1f',
      },
    },
  },
  content: [
    './resources/views/**/*.blade.php', // Include Blade templates
    './resources/js/**/*.js',          // Include JavaScript files
  ],
};