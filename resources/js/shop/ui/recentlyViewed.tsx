export type MiniProduct = {
    id: number;
    slug?: string | null;
    name: string;
    price: number | string;
    preview_url?: string | null;
};

const KEY = 'recently_viewed';
const LIMIT = 12;

export function getRecentlyViewed(): MiniProduct[] {
    try {
        const raw = localStorage.getItem(KEY);
        if (!raw) return [];
        const arr = JSON.parse(raw);
        if (!Array.isArray(arr)) return [];
        return arr;
    } catch {
        return [];
    }
}

export function addRecentlyViewed(p: MiniProduct) {
    try {
        const list = getRecentlyViewed();
        const filtered = list.filter(x => x.id !== p.id);
        const next = [pickMini(p), ...filtered].slice(0, LIMIT);
        localStorage.setItem(KEY, JSON.stringify(next));
    } catch {
        // no-op
    }
}

function pickMini(p: MiniProduct): MiniProduct {
    return {
        id: p.id,
        slug: p.slug ?? null,
        name: p.name,
        price: p.price,
        preview_url: p.preview_url ?? null,
    };
}
