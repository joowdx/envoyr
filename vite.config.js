import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  server: {
    host: 'localhost',
    port: 5174, // ← match this to the port Vite is using
    hmr: {
      host: 'localhost',
      port: 5174, // ← same here
    },
  },
  plugins: [
    laravel([
      'resources/css/app.css',
      'resources/js/app.js',
    ]),
  ],
});
