import { useEffect } from 'react';

type Hreflang = { lang: string; href: string };

type Props = {
    title?: string;
    description?: string;
    image?: string;          // absolute URL
    url?: string;            // якщо не задано — візьме window.location.href
    type?: 'website' | 'article' | string;
    twitterCard?: 'summary' | 'summary_large_image';
    canonical?: boolean | string; // true → current URL; string → свій URL
    siteName?: string;
    prevUrl?: string;
    nextUrl?: string;
    robots?: string;         // NEW: наприклад 'noindex,follow'
    hreflangs?: Hreflang[];  // NEW: масив alternate посилань
};

function upsertMetaName(name: string, content?: string) {
    if (!content) return () => {};
    let el = document.querySelector(`meta[name="${name}"]`) as HTMLMetaElement | null;
    const created = !el;
    if (!el) { el = document.createElement('meta'); el.name = name; document.head.appendChild(el); }
    const prev = el.content;
    el.content = content;
    return () => { if (el) el.content = prev ?? ''; if (created && el) el.remove(); };
}

function upsertMetaProp(prop: string, content?: string) {
    if (!content) return () => {};
    let el = document.querySelector(`meta[property="${prop}"]`) as HTMLMetaElement | null;
    const created = !el;
    if (!el) { el = document.createElement('meta'); el.setAttribute('property', prop); document.head.appendChild(el); }
    const prev = el.content;
    el.content = content;
    return () => { if (el) el.content = prev ?? ''; if (created && el) el.remove(); };
}

function upsertLink(rel: string, href?: string) {
    if (!href) return () => {};
    let el = document.querySelector(`link[rel="${rel}"]`) as HTMLLinkElement | null;
    const created = !el;
    if (!el) { el = document.createElement('link'); el.rel = rel; document.head.appendChild(el); }
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
                                    robots,
                                    hreflangs,
                                }: Props) {
    useEffect(() => {
        const currentUrl = url || (typeof window !== 'undefined' ? window.location.href : undefined);
        const cleanups: Array<() => void> = [];

        // <title> + description
        const prevTitle = document.title;
        if (title) document.title = title;
        cleanups.push(() => { if (title) document.title = prevTitle; });
        cleanups.push(upsertMetaName('description', description));

        // robots
        cleanups.push(upsertMetaName('robots', robots));

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

        // canonical / prev / next
        const canonicalHref = canonical === true ? currentUrl : (typeof canonical === 'string' ? canonical : undefined);
        cleanups.push(upsertLink('canonical', canonicalHref));
        cleanups.push(upsertLink('prev', prevUrl));
        cleanups.push(upsertLink('next', nextUrl));

        // hreflang alternate — видаляємо старі, додаємо свої
        // помітимо свої лінки data-seohead="true", щоб чисто прибирати
        const oldAlts = Array.from(document.querySelectorAll('link[rel="alternate"][data-seohead="true"]'));
        oldAlts.forEach(n => n.parentElement?.removeChild(n));
        const created: HTMLLinkElement[] = [];
        if (hreflangs?.length) {
            hreflangs.forEach(({ lang, href }) => {
                const link = document.createElement('link');
                link.rel = 'alternate';
                link.hreflang = lang;
                link.href = href;
                link.setAttribute('data-seohead', 'true');
                document.head.appendChild(link);
                created.push(link);
            });
        }
        cleanups.push(() => { created.forEach(n => n.parentElement?.removeChild(n)); });

        return () => cleanups.forEach(fn => fn());
    }, [title, description, image, url, type, twitterCard, canonical, siteName, prevUrl, nextUrl, robots, JSON.stringify(hreflangs)]);

    return null;
}
