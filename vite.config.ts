import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export default defineConfig({
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/ts'),
      jquery: resolve(__dirname, 'node_modules/jquery/dist/jquery.js'),
    },
  },
  optimizeDeps: { include: ['jquery', 'select2'] },
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/ts/vendor.ts',
        'resources/ts/app.ts',
      ],
      refresh: true,
    }),
  ],
});
