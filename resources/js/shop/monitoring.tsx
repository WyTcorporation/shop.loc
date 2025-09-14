// resources/js/shop/monitoring.ts
import * as Sentry from '@sentry/react';

export function initMonitoring() {
    const dsn = (import.meta as any).env?.VITE_SENTRY_DSN as string | undefined;
    if (!dsn) return; // якщо DSN не заданий — тихий no-op

    const env = import.meta.env.MODE;
    const release = (import.meta as any).env?.VITE_RELEASE as string | undefined;

    // Якщо користувач відмовився від cookies — не шлемо події
    const allow = (() => {
        try {
            const c = localStorage.getItem('cookie:analytics-consent');
            return c === 'granted';
        } catch { return false; }
    })();

    if (!allow) return;

    Sentry.init({
        dsn,
        environment: env,
        release,
        integrations: [
            Sentry.browserTracingIntegration(),
            // Легка сесійна реплейка для помилок
            Sentry.replayIntegration({ maskAllText: true, blockAllMedia: true }),
        ],
        tracesSampleRate: 0.1,
        replaysSessionSampleRate: 0.0,
        replaysOnErrorSampleRate: 1.0,
    });
}

export function reportError(err: unknown, extra?: Record<string, any>) {
    try {
        // не падати, якщо Sentry не ініціалізовано
        // @ts-ignore
        if (typeof window !== 'undefined' && window.SENTRY_RELEASE !== undefined) {
            Sentry.captureException(err, extra ? { extra } : undefined);
        }
    } catch {}
}
