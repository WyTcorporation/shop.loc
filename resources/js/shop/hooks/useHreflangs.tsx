import { useMemo } from 'react';

type H = { lang: string; href: string };

export function useHreflangs(singleLangCode: string = 'uk'): H[] {
    return useMemo(() => {
        if (typeof window === 'undefined') return [];
        const href = window.location.href;
        // Сьогодні: одна мова → x-default + uk
        return [
            { lang: 'x-default', href },
            { lang: singleLangCode, href },
        ];
    }, [typeof window !== 'undefined' ? window.location.href : '']);
}
