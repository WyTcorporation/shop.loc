import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import path from 'node:path';

const isVitest = process.env.VITEST === 'true';
const useWayfinder = !isVitest && process.env.WAYFINDER !== 'off';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        watch: {
            usePolling: true,
            interval: 350,
            ignored: [
                '**/node_modules/**',
                '**/vendor/**',
                '**/storage/**',
                '**/.git/**',
                '**/public/build/**',
                '**/public/storage/**',
            ],
            awaitWriteFinish: { stabilityThreshold: 150, pollInterval: 100 },
        },
    },
    plugins: [
        ...(isVitest
            ? []
            : [
                  laravel({
                      input: [
                          'resources/css/app.css',
                          'resources/js/app.tsx',
                          'resources/js/shop/main.tsx',
                      ],
                      ssr: 'resources/js/ssr.tsx',
                      // refresh: true,
                      refresh: ['resources/views/**/*.blade.php'],
                  }),
                  ...(useWayfinder
                      ? [
                            wayfinder({
                                formVariants: true,
                            }),
                        ]
                      : []),
              ]),
        react(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: ['resources/js/test/setupTests.ts'],
        include: ['resources/js/**/*.{test,spec}.{ts,tsx}'],
        exclude: ['tests/**'],
    },
});
