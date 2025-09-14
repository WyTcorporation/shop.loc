import React from 'react';
import { getAnalyticsId, getConsent, setConsent } from '../ui/analytics';

export default function CookieConsent() {
    const gaId = getAnalyticsId();
    const [open, setOpen] = React.useState(false);

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
            aria-label="Налаштування cookies"
            className="fixed inset-x-0 bottom-0 z-50"
            data-testid="cookie-consent"
        >
            <div className="mx-auto mb-4 max-w-4xl rounded-xl border bg-white p-4 shadow-lg">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="text-sm">
                        Ми використовуємо cookies для аналітики (GA4). Натисніть «Дозволити», щоб увімкнути.
                        Ви можете змінити вибір будь-коли.
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => { setConsent('denied'); setOpen(false); }}
                            className="rounded-md border px-3 py-1.5 text-sm hover:bg-gray-50"
                            data-testid="cookies-decline"
                        >
                            Відхилити
                        </button>
                        <button
                            onClick={() => { setConsent('granted'); setOpen(false); }}
                            className="rounded-md bg-black px-3 py-1.5 text-sm text-white hover:opacity-90"
                            data-testid="cookies-accept"
                        >
                            Дозволити
                        </button>
                    </div>
                </div>
                <div className="mt-2 text-xs text-gray-500">
                    Обов’язкові cookies не відслідковують вас. Аналітика вмикається лише за згодою.
                </div>
            </div>
        </div>
    );
}
