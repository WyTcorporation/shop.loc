import React from 'react';
import { getAnalyticsId, getConsent, setConsent } from '../ui/analytics';
import { useLocale } from '../i18n/LocaleProvider';

export default function CookieConsent() {
    const gaId = getAnalyticsId();
    const [open, setOpen] = React.useState(false);
    const { t } = useLocale();

    // показуємо банер, якщо: є GA ID і ще нема вибору
    React.useEffect(() => {
        if (!gaId) return;
        setOpen(getConsent() === null);
    }, [gaId]);

    // даємо можливість відкривати з будь-де
    React.useEffect(() => {
        function onOpen() { setOpen(true); }
        window.addEventListener('open-cookie-preferences' as any, onOpen);
        return () => window.removeEventListener('open-cookie-preferences' as any, onOpen);
    }, []);

    if (!gaId || !open) return null;

    return (
        <div
            role="dialog"
            aria-live="polite"
            aria-label={t('consent.ariaLabel')}
            className="fixed inset-x-0 bottom-0 z-50"
            data-testid="cookie-consent"
        >
            <div className="mx-auto mb-4 max-w-4xl rounded-xl border bg-white p-4 shadow-lg">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="text-sm">
                        {t('consent.message')}
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => { setConsent('denied'); setOpen(false); }}
                            className="rounded-md border px-3 py-1.5 text-sm hover:bg-gray-50"
                            data-testid="cookies-decline"
                        >
                            {t('consent.decline')}
                        </button>
                        <button
                            onClick={() => { setConsent('granted'); setOpen(false); }}
                            className="rounded-md bg-black px-3 py-1.5 text-sm text-white hover:opacity-90"
                            data-testid="cookies-accept"
                        >
                            {t('consent.accept')}
                        </button>
                    </div>
                </div>
                <div className="mt-2 text-xs text-gray-500">
                    {t('consent.note')}
                </div>
            </div>
        </div>
    );
}
