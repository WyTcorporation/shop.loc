import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { DEFAULT_LANG, Lang, normalizeLang, resolveLocale } from './config';
import { createTranslator, getMessages, type Messages, type Translator } from './messages';

type Ctx = { lang: Lang; locale: string; setLang: (l: Lang) => void; messages: Messages; t: Translator };

const defaultMessages = getMessages(DEFAULT_LANG);
const LocaleCtx = createContext<Ctx>({
    lang: DEFAULT_LANG,
    locale: resolveLocale(DEFAULT_LANG),
    setLang: () => {},
    messages: defaultMessages,
    t: createTranslator(defaultMessages),
});

export function useLocale() { return useContext(LocaleCtx); }

export default function LocaleProvider({ initial, children }: { initial?: string; children: ReactNode }) {
    const [lang, setLang] = useState<Lang>(normalizeLang(initial));

    useEffect(() => {
        const normalized = normalizeLang(initial);
        setLang((current) => (current === normalized ? current : normalized));
    }, [initial]);

    useEffect(() => {
        // <html lang="...">
        if (typeof document !== 'undefined') {
            document.documentElement.setAttribute('lang', lang);
        }
        // cookie "lang" на рік
        try {
            const exp = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
            document.cookie = `lang=${lang}; path=/; expires=${exp}; SameSite=Lax`;
        } catch {}
    }, [lang]);

    const value = useMemo(() => {
        const messages = getMessages(lang);
        return {
            lang,
            locale: resolveLocale(lang),
            setLang,
            messages,
            t: createTranslator(messages),
        } satisfies Ctx;
    }, [lang]);

    return <LocaleCtx.Provider value={value}>{children}</LocaleCtx.Provider>;
}
