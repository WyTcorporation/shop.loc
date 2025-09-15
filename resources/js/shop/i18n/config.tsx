export const SUPPORTED_LANGS = ['uk','en'] as const;
export type Lang = typeof SUPPORTED_LANGS[number];
export const DEFAULT_LANG: Lang = 'uk';

export function normalizeLang(raw?: string | null): Lang {
    const s = (raw || '').toLowerCase();
    return (SUPPORTED_LANGS as readonly string[]).includes(s) ? (s as Lang) : DEFAULT_LANG;
}
