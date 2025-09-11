import { useEffect } from 'react';

type JsonLdProps = { data: any };

export default function JsonLd({ data }: JsonLdProps) {
    useEffect(() => {
        const el = document.createElement('script');
        el.type = 'application/ld+json';
        el.setAttribute('data-json-ld', '1');
        el.text = JSON.stringify(data);
        document.head.appendChild(el);
        return () => { el.remove(); };
    }, [data]);

    return null;
}
