import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';
import { AppProvider } from './providers/app-provider';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        const initialPageProps = (props.initialPage?.props ?? {}) as Record<string, unknown>;

        root.render(
            <AppProvider
                initialLocale={typeof initialPageProps.locale === 'string' ? initialPageProps.locale : undefined}
                initialCurrency={typeof initialPageProps.currency === 'string' ? initialPageProps.currency : undefined}
            >
                <App {...props} />
            </AppProvider>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
