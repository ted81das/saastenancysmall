import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/blog.js',
                'resources/css/filament/dashboard/theme.css',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
        }),
    ],
});
