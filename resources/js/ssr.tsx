import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';
import { AppProvider } from './providers/app-provider';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
        setup: ({ App, props }) => {
            const initialPageProps = (props.initialPage?.props ?? {}) as Record<string, unknown>;

            return (
                <AppProvider
                    initialLocale={typeof initialPageProps.locale === 'string' ? initialPageProps.locale : undefined}
                    initialCurrency={typeof initialPageProps.currency === 'string' ? initialPageProps.currency : undefined}
                >
                    <App {...props} />
                </AppProvider>
            );
        },
    }),
);
