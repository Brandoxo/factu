import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '@Components': fileURLToPath(new URL('./resources/js/Components', import.meta.url)),
        },
    },
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    /* build: {
        minify: 'esbuild',
        terserOptions: {
            compress: {
                drop_console: true,
            },
        },
    },
    esbuild: {
        drop: ['console', 'debugger'],
    },
    */
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
