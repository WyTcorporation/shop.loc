declare global {
    interface Window {
        __APP_SUPPORTED_LOCALES__?: string[];
        __APP_DEFAULT_LOCALE__?: string;
    }
}

const DEFAULT_SUPPORTED = ['uk', 'en', 'ru', 'pt'] as const;

function resolveSupportedLocales(): readonly string[] {
    if (typeof window === 'undefined') {
        return DEFAULT_SUPPORTED;
    }

    const fromWindow = window.__APP_SUPPORTED_LOCALES__;
    if (!Array.isArray(fromWindow) || fromWindow.length === 0) {
        return DEFAULT_SUPPORTED;
    }

    const normalized = fromWindow
        .map(locale => locale?.toLowerCase?.() ?? '')
        .map(locale => locale.replace('_', '-'))
        .filter(Boolean);

    return normalized.length ? Array.from(new Set(normalized)) : DEFAULT_SUPPORTED;
}

function resolveDefaultLocale(supported: readonly string[]): string {
    const fallback = DEFAULT_SUPPORTED[0];

    if (typeof window === 'undefined') {
        return fallback;
    }

    const raw = window.__APP_DEFAULT_LOCALE__;
    const normalized = raw?.toLowerCase?.().replace('_', '-') ?? '';
    return supported.includes(normalized) ? normalized : fallback;
}

export const SUPPORTED_LANGS = resolveSupportedLocales();
export type Lang = (typeof SUPPORTED_LANGS)[number];
export const DEFAULT_LANG: Lang = resolveDefaultLocale(SUPPORTED_LANGS) as Lang;

const LANG_LOCALE_MAP: Record<Lang, string> = {
    uk: 'uk-UA',
    en: 'en-US',
    ru: 'ru-RU',
    pt: 'pt-PT',
};

export function normalizeLang(raw?: string | null): Lang {
    const s = (raw || '').toLowerCase();
    return (SUPPORTED_LANGS as readonly string[]).includes(s) ? (s as Lang) : DEFAULT_LANG;
}

export function resolveLocale(lang: Lang): string {
    return LANG_LOCALE_MAP[lang] ?? LANG_LOCALE_MAP[DEFAULT_LANG];
}
