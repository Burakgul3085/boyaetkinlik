import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
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
            ],
            refresh: true,
        }),
    ],
});
