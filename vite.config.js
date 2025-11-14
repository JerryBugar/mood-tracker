import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/admin/dashboard.css',
                'resources/js/admin/dashboard.js',
                'resources/css/admin/notifications.css',
                'resources/js/admin/notifications.js',
                'resources/css/profile/profile.css',
                'resources/js/profile/profile.js',
                'resources/css/notif/notif.css',
                'resources/js/notif/notif.js',
                'resources/js/admin/employees.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        https: false,
        host: 'localhost',
        port: 5173,
    },
});
