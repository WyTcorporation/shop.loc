export type WishItem = {
    id: number;
    slug?: string | null;
    name: string;
    price: number | string;
    preview_url?: string | null;
};

const KEY = 'wishlist_v1';

export function getWishlist(): WishItem[] {
    try {
        const raw = localStorage.getItem(KEY);
        if (!raw) return [];
        const arr = JSON.parse(raw);
        return Array.isArray(arr) ? arr : [];
    } catch { return []; }
}

export function setWishlist(items: WishItem[]) {
    try { localStorage.setItem(KEY, JSON.stringify(items)); } catch {}
}

export function toggleWishlist(p: WishItem): { added: boolean; items: WishItem[] } {
    const list = getWishlist();
    const exists = list.some(x => x.id === p.id);
    const next = exists ? list.filter(x => x.id !== p.id) : [pick(p), ...list];
    setWishlist(next);
    return { added: !exists, items: next };
}

export function isInWishlist(id: number): boolean {
    return getWishlist().some(x => x.id === id);
}

function pick(p: WishItem): WishItem {
    return {
        id: p.id,
        slug: p.slug ?? null,
        name: p.name,
        price: p.price,
        preview_url: p.preview_url ?? null,
    };
}
