import { useLocale } from '../i18n/LocaleProvider';
import { SUPPORTED_LANGS, Lang } from '../i18n/config';

function swapLangInPath(next: Lang) {
    const u = new URL(window.location.href);
    const parts = u.pathname.split('/').filter(Boolean);
    // якщо перший сегмент — мова, замінити; інакше вставити
    if (SUPPORTED_LANGS.includes(parts[0] as any)) parts[0] = next;
    else parts.unshift(next);
    u.pathname = '/' + parts.join('/');
    return u.pathname + u.search + u.hash;
}

export default function LanguageSwitcher() {
    const { lang, setLang } = useLocale();
    return (
        <div className="flex items-center gap-2">
            {SUPPORTED_LANGS.map(l => (
                <button
                    key={l}
                    onClick={() => { setLang(l); window.location.assign(swapLangInPath(l)); }}
                    className={`text-xs underline ${l===lang ? 'font-semibold' : ''}`}
                >
                    {l.toUpperCase()}
                </button>
            ))}
        </div>
    );
}
