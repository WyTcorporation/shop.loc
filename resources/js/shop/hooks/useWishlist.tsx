import React from 'react';
import { getWishlist, setWishlist, type WishItem } from '../ui/wishlist';

type Ctx = {
    items: WishItem[];
    has: (id: number) => boolean;
    add: (p: WishItem) => void;
    remove: (id: number) => void;
    toggle: (p: WishItem) => void;
    clear: () => void;
};

const Ctx = React.createContext<Ctx | null>(null);

export function WishlistProvider({ children }: { children: React.ReactNode }) {
    const [items, setItems] = React.useState<WishItem[]>(() => getWishlist());

    // sync з іншими вкладками
    React.useEffect(() => {
        const onStorage = (e: StorageEvent) => {
            if (e.key === 'wishlist_v1') setItems(getWishlist());
        };
        window.addEventListener('storage', onStorage);
        return () => window.removeEventListener('storage', onStorage);
    }, []);

    const api = React.useMemo<Ctx>(() => ({
        items,
        has: (id) => items.some(x => x.id === id),
        add: (p) => { const next = [p, ...items.filter(x => x.id !== p.id)]; setItems(next); setWishlist(next); },
        remove: (id) => { const next = items.filter(x => x.id !== id); setItems(next); setWishlist(next); },
        toggle: (p) => {
            const exists = items.some(x => x.id === p.id);
            const next = exists ? items.filter(x => x.id !== p.id) : [p, ...items];
            setItems(next); setWishlist(next);
        },
        clear: () => { setItems([]); setWishlist([]); },
    }), [items]);

    return <Ctx.Provider value={api}>{children}</Ctx.Provider>;
}

export default function useWishlist() {
    const ctx = React.useContext(Ctx);
    if (!ctx) throw new Error('useWishlist must be used within WishlistProvider');
    return ctx;
}
