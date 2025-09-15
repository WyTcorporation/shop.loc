import * as Sentry from '@sentry/react';

const DSN = (import.meta as any).env?.VITE_SENTRY_DSN as string | undefined;
const TRACES = Number((import.meta as any).env?.VITE_SENTRY_TRACES ?? 0.1);

if (import.meta.env.PROD && DSN) {
    Sentry.init({
        dsn: DSN,
        integrations: [
            Sentry.browserTracingIntegration?.() || undefined,
        ].filter(Boolean),
        tracesSampleRate: isNaN(TRACES) ? 0.1 : TRACES,
        beforeSend(event) {
            const msg = event.exception?.values?.[0]?.value || '';
            if (typeof msg === 'string' && /Loading chunk \d+ failed/i.test(msg)) return null;
            return event;
        },
    });
}

export { Sentry };
