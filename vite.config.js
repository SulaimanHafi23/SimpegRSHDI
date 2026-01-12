import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const host = process.env.VITE_DEV_HOST || '0.0.0.0';
const port = Number(process.env.VITE_DEV_PORT) || 5174;
const publicHost = process.env.VITE_DEV_PUBLIC || host;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host,
        port,
        strictPort: true,
        hmr: {
            host: publicHost,
            port,
        },
    },
});
