import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    resolve: {
        alias: {
            'opentype.js': path.resolve(__dirname, 'node_modules/opentype.js/dist/opentype.mjs'),
        },
    },
    optimizeDeps: {
        include: ['opentype.js'],
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/online-paint.js',
                'resources/js/online-experiment.js',
                'resources/js/online-experiment-color-mix.js',
                'resources/js/online-experiment-color-wheel.js',
                'resources/js/online-experiment-warm-cool.js',
                'resources/js/online-experiment-line-trace.js',
                'resources/js/online-experiment-letter-trace.js',
                'resources/js/online-experiment-number-trace.js',
            ],
            refresh: true,
        }),
    ],
});
