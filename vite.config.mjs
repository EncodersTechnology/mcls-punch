import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import * as path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/new.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            'crypto': path.resolve(__dirname, 'node_modules/crypto-browserify'),
            'stream': path.resolve(__dirname, 'node_modules/stream-browserify'),
            'util': path.resolve(__dirname, 'node_modules/util'),
        },
    },
    build: {
        rollupOptions: {
            plugins: [],
        },
    },
});
