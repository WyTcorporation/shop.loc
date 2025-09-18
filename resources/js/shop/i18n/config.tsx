export const SUPPORTED_LANGS = ['uk', 'en', 'ru', 'pt'] as const;
export type Lang = typeof SUPPORTED_LANGS[number];
export const DEFAULT_LANG: Lang = 'uk';

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
