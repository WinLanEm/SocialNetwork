import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        watch: {
            usePolling: true,
            interval: 1000,
            ignored: ['**/vendor/**', '**/storage/**', '**/node_modules/**','**/tmp/**']
        },
        host: '0.0.0.0',
        port: 5173,
        https:{
            key: '_docker/nginx/ssl/mykey.key',
            cert: '_docker/nginx/ssl/mycert.crt',
        },
        cors: {
            origin: [
                'https://localhost',
                'http://localhost',
                'https://localhost:443',
                'https://js.pusher.com',
                'wss://ws-eu.pusher.com',
                'https://localhost:5173'
            ],
            credentials: true
        },
        hmr: {
            host: 'localhost',
            protocol: 'wss',
            port:5173,
            clientPort:5173,
        },
        strictPort:true
    },
    base: '/',
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/Common/abstract.css',
                'resources/css/Home/home.css',
                'resources/css/User/register.css',
                'resources/css/User/authorization.css',
                'resources/js/app.js',
                'resources/js/qwe.js',
            ],
            refresh: [
                'resources/views/**',
                'app/**',
                'routes/**'
            ]
        }),
        vue(),
        tailwindcss(),

    ],
});
