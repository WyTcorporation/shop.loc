import { useCallback, useEffect, useState } from 'react';
import type { Product } from '../api';

type MiniImage = { url: string; alt?: string; is_primary?: boolean };
export type MiniProduct = Pick<Product, 'id' | 'slug' | 'name' | 'price' | 'preview_url'> & {
    images?: MiniImage[];
    viewedAt: number;
};

const KEY = 'recently_viewed_v1';
const LIMIT = 12;

function load(): MiniProduct[] {
    try {
        const raw = localStorage.getItem(KEY);
        const arr = raw ? JSON.parse(raw) as MiniProduct[] : [];
        return Array.isArray(arr) ? arr : [];
    } catch {
        return [];
    }
}
function save(list: MiniProduct[]) {
    localStorage.setItem(KEY, JSON.stringify(list));
}

function toMini(p: Product): MiniProduct {
    return {
        id: p.id,
        slug: p.slug,
        name: p.name,
        price: p.price as number,
        preview_url: (p as any).preview_url, // у нас це поле приходить з бека
        images: p.images,
        viewedAt: Date.now(),
    };
}

export function useRecentlyViewed() {
    const [items, setItems] = useState<MiniProduct[]>(() => load());

    const remember = useCallback((p: Product) => {
        const entry = toMini(p);
        setItems(prev => {
            const filtered = prev.filter(
                x => (x.slug ?? x.id) !== (entry.slug ?? entry.id)
            );
            const next = [entry, ...filtered].slice(0, LIMIT);
            save(next);
            return next;
        });
    }, []);

    const clear = useCallback(() => {
        save([]);
        setItems([]);
    }, []);

    // синхронізація між вкладками
    useEffect(() => {
        const onStorage = (e: StorageEvent) => {
            if (e.key === KEY) setItems(load());
        };
        window.addEventListener('storage', onStorage);
        return () => window.removeEventListener('storage', onStorage);
    }, []);

    return { items, remember, clear };
}
