// resources/js/shop/ui/analytics.ts
type Consent = 'granted' | 'denied';

const KEY = 'cookie:analytics-consent';
const SCRIPT_ID = 'ga4-script';

const DEFAULT_CONSENT: Consent =
    (import.meta.env.VITE_COOKIE_DEFAULT === 'granted') ? 'granted' : 'denied';

declare global {
    interface Window {
        dataLayer: any[];
        gtag?: (...args: any[]) => void;
    }
}

export function getAnalyticsId(): string | undefined {
    const id = (import.meta as any).env?.VITE_GA_ID as string | undefined;
    return id && id.trim() ? id : undefined;
}

export function getConsent(): Consent | null {
    try {
        const raw = localStorage.getItem(KEY);
        return raw === 'granted' || raw === 'denied' ? raw : null;
    } catch {
        return null;
    }
}

export function getEffectiveConsent(): Consent {
    return getConsent() ?? DEFAULT_CONSENT;
}

export function setConsent(val: Consent) {
    try { localStorage.setItem(KEY, val); } catch {}
    updateConsent(val);
}

function ensureGtagBooted(id: string) {
    if (!document.getElementById(SCRIPT_ID)) {
        const s = document.createElement('script');
        s.async = true;
        s.id = SCRIPT_ID;
        s.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
        document.head.appendChild(s);
    }
    window.dataLayer = window.dataLayer || [];
    if (!window.gtag) {
        window.gtag = function () { window.dataLayer.push(arguments); };
    }
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });
}

export function updateConsent(val: Consent) {
    const id = getAnalyticsId();
    if (!id) return;

    // "Hard block": GA підвантажуємо тільки при згоді
    if (val === 'granted') {
        ensureGtagBooted(id);
        window.gtag?.('consent', 'update', { analytics_storage: 'granted' });
    } else {
        // При відмові скрипт не вантажимо; якщо вже був — оновлюємо стан
        window.gtag?.('consent', 'update', { analytics_storage: 'denied' });
    }
}

export function initAnalyticsOnLoad() {
    const id = getAnalyticsId();
    if (!id) return;
    const effective = getEffectiveConsent();
    if (effective === 'granted') {
        ensureGtagBooted(id);
    }
    // Якщо захочеш режим Consent Mode з "cookieless pings":
    // else { ensureGtagBooted(id); window.gtag?.('consent','default',{ analytics_storage:'denied' }); }
}

export function openCookiePreferences() {
    window.dispatchEvent(new CustomEvent('open-cookie-preferences'));
}

// DEBUG: виклич у консолі __debugConsent()
export function __debugConsent() {
    const saved = getConsent();
    console.debug('[analytics] DEFAULT=%s, saved=%s, effective=%s, GA_ID=%s',
        DEFAULT_CONSENT, saved, getEffectiveConsent(), getAnalyticsId());
}
