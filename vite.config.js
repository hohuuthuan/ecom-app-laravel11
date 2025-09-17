import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
  resolve: {
    alias: {
      jquery: path.resolve(__dirname, 'node_modules/jquery/dist/jquery.js'),
    },
  },
  optimizeDeps: {
    include: ['jquery', 'select2'],
  },
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/vendor.ts',
        'resources/js/app.ts',
      ],
      refresh: true,
    }),
  ],
});
