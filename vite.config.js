import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],

    // ── Docker HMR Support ─────────────────────────────────
    // When running inside Docker, Vite needs to:
    //   1. Listen on all interfaces (0.0.0.0) so the host can reach it
    //   2. Use the correct HMR host/port for WebSocket connections
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',  // Browser connects to this host for HMR
            port: 5173,
        },
        watch: {
            usePolling: true,   // Required for Docker bind mounts on Windows
            interval: 1000,
        },
    },
});
