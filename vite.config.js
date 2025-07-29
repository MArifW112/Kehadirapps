import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build', // <-- ini penting!
        }),
    ],
    build: {
        outDir: 'public/build', // pastikan outputnya di sini
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                style: 'resources/css/app.css', // kalau mau CSS juga
            },
        },
        emptyOutDir: true,
    },
});
