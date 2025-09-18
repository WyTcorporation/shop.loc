import { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { DEFAULT_LANG, Lang, normalizeLang } from './config';
import { getMessages, type Messages } from './messages';

type Ctx = { lang: Lang; setLang: (l: Lang) => void; messages: Messages };
const LocaleCtx = createContext<Ctx>({
    lang: DEFAULT_LANG,
    setLang: () => {},
    messages: getMessages(DEFAULT_LANG),
});

export function useLocale() { return useContext(LocaleCtx); }

export default function LocaleProvider({ initial, children }: { initial?: string; children: any }) {
    const [lang, setLang] = useState<Lang>(normalizeLang(initial));

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

    const value = useMemo(() => ({ lang, setLang, messages: getMessages(lang) }), [lang]);
    return <LocaleCtx.Provider value={value}>{children}</LocaleCtx.Provider>;
}
