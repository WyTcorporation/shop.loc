import { useEffect } from 'react';

type Props = {
    title?: string;
    description?: string;
    image?: string;          // бажано абсолютний URL
    url?: string;            // якщо не вказати — поточний
    type?: 'website' | 'article' | string;
    twitterCard?: 'summary' | 'summary_large_image';
    canonical?: boolean | string; // true → current URL; string → ваш URL
    siteName?: string;
    prevUrl?: string;
    nextUrl?: string;
    robots?: string;         // NEW: напр. "noindex,nofollow"
};

function upsertMetaName(name: string, content: string | undefined) {
    if (!content) return () => {};
    let el = document.querySelector(`meta[name="${name}"]`) as HTMLMetaElement | null;
    const created = !el;
    if (!el) {
        el = document.createElement('meta');
        el.name = name;
        document.head.appendChild(el);
    }
    const prev = el.content;
    el.content = content;
    return () => { el && (el.content = prev ?? ''); if (created && el) el.remove(); };
}

function upsertMetaProp(prop: string, content: string | undefined) {
    if (!content) return () => {};
    let el = document.querySelector(`meta[property="${prop}"]`) as HTMLMetaElement | null;
    const created = !el;
    if (!el) {
        el = document.createElement('meta');
        el.setAttribute('property', prop);
        document.head.appendChild(el);
    }
    const prev = el.content;
    el.content = content;
    return () => { el && (el.content = prev ?? ''); if (created && el) el.remove(); };
}

function upsertLink(rel: string, href: string | undefined) {
    if (!href) return () => {};
    let el = document.querySelector(`link[rel="${rel}"]`) as HTMLLinkElement | null;
    const created = !el;
    if (!el) {
        el = document.createElement('link');
        el.rel = rel;
        document.head.appendChild(el);
    }
    const prev = el.href;
    el.href = href;
    return () => { if (el) el.href = prev ?? ''; if (created && el) el.remove(); };
}

export default function SeoHead({
                                    title,
                                    description,
                                    image,
                                    url,
                                    type = 'website',
                                    twitterCard = 'summary_large_image',
                                    canonical,
                                    siteName = 'Shop',
                                    prevUrl,
                                    nextUrl,
                                    robots, // NEW
                                }: Props) {
    useEffect(() => {
        const currentUrl = url || (typeof window !== 'undefined' ? window.location.href : undefined);
        const cleanups: Array<() => void> = [];

        // <title> + description
        const prevTitle = document.title;
        if (title) document.title = title;
        cleanups.push(() => { if (title) document.title = prevTitle; });
        cleanups.push(upsertMetaName('description', description));

        // OpenGraph
        cleanups.push(upsertMetaProp('og:title', title));
        cleanups.push(upsertMetaProp('og:description', description));
        cleanups.push(upsertMetaProp('og:type', type));
        cleanups.push(upsertMetaProp('og:url', currentUrl));
        cleanups.push(upsertMetaProp('og:image', image));
        cleanups.push(upsertMetaProp('og:site_name', siteName));

        // Twitter
        cleanups.push(upsertMetaName('twitter:card', twitterCard));
        cleanups.push(upsertMetaName('twitter:title', title));
        cleanups.push(upsertMetaName('twitter:description', description));
        cleanups.push(upsertMetaName('twitter:image', image));

        // canonical/prev/next
        const canonicalHref = canonical === true ? currentUrl
            : typeof canonical === 'string' ? canonical
                : undefined;
        cleanups.push(upsertLink('canonical', canonicalHref));
        cleanups.push(upsertLink('prev', prevUrl));
        cleanups.push(upsertLink('next', nextUrl));

        // robots (NEW)
        cleanups.push(upsertMetaName('robots', robots));

        return () => cleanups.forEach(fn => fn());
    }, [title, description, image, url, type, twitterCard, canonical, siteName, prevUrl, nextUrl, robots]);

    return null;
}
