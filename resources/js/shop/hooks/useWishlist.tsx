import React from 'react';
import { WishlistApi } from '../api';
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
    const syncMode = React.useRef<'unknown' | 'remote' | 'local'>('unknown');
    const itemsRef = React.useRef(items);

    const setList = React.useCallback((next: WishItem[] | ((prev: WishItem[]) => WishItem[])) => {
        setItems(prev => {
            const resolved = typeof next === 'function' ? (next as (prev: WishItem[]) => WishItem[])(prev) : next;
            setWishlist(resolved);
            return resolved;
        });
    }, []);

    const handleApiError = React.useCallback((error: any) => {
        const status = error?.response?.status;
        syncMode.current = status === 401 ? 'local' : 'unknown';
    }, []);

    React.useEffect(() => {
        itemsRef.current = items;
    }, [items]);

    // початковий sync з API
    React.useEffect(() => {
        let mounted = true;

        (async () => {
            try {
                const remote = await WishlistApi.list();
                if (!mounted) return;
                syncMode.current = 'remote';
                setList(remote);
            } catch (error: any) {
                if (!mounted) return;
                handleApiError(error);
            }
        })();

        return () => { mounted = false; };
    }, [handleApiError, setList]);

    // sync з іншими вкладками
    React.useEffect(() => {
        const onStorage = (e: StorageEvent) => {
            if (e.key === 'wishlist_v1') setItems(getWishlist());
        };
        window.addEventListener('storage', onStorage);
        return () => window.removeEventListener('storage', onStorage);
    }, []);

    const syncAdd = React.useCallback((productId: number, fallback: WishItem) => {
        if (syncMode.current === 'local') return;
        WishlistApi.add(productId)
            .then(item => {
                syncMode.current = 'remote';
                const payload = item ?? fallback;
                setList(prev => {
                    const rest = prev.filter(x => x.id !== payload.id);
                    return [payload, ...rest];
                });
            })
            .catch(handleApiError);
    }, [handleApiError, setList]);

    const syncRemove = React.useCallback((productId: number) => {
        if (syncMode.current === 'local') return;
        WishlistApi.remove(productId)
            .then(() => {
                syncMode.current = 'remote';
            })
            .catch(handleApiError);
    }, [handleApiError]);

    const has = React.useCallback((id: number) => items.some(x => x.id === id), [items]);

    const add = React.useCallback((p: WishItem) => {
        setList(prev => {
            const rest = prev.filter(x => x.id !== p.id);
            return [p, ...rest];
        });
        syncAdd(p.id, p);
    }, [setList, syncAdd]);

    const remove = React.useCallback((id: number) => {
        setList(prev => prev.filter(x => x.id !== id));
        syncRemove(id);
    }, [setList, syncRemove]);

    const toggle = React.useCallback((p: WishItem) => {
        if (has(p.id)) {
            remove(p.id);
        } else {
            add(p);
        }
    }, [add, has, remove]);

    const clear = React.useCallback(() => {
        const prev = itemsRef.current;
        setList([]);
        if (!prev.length || syncMode.current === 'local') {
            return;
        }
        Promise.all(prev.map(item => WishlistApi.remove(item.id)))
            .then(() => {
                syncMode.current = 'remote';
            })
            .catch(handleApiError);
    }, [handleApiError, setList]);

    const api = React.useMemo<Ctx>(() => ({
        items,
        has,
        add,
        remove,
        toggle,
        clear,
    }), [add, clear, has, items, remove, toggle]);

    return <Ctx.Provider value={api}>{children}</Ctx.Provider>;
}

export default function useWishlist() {
    const ctx = React.useContext(Ctx);
    if (!ctx) throw new Error('useWishlist must be used within WishlistProvider');
    return ctx;
}
