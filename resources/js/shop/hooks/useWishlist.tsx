import React from 'react';
import type {AxiosError} from 'axios';
import {WishlistApi} from '../api';
import {getWishlist, setWishlist, type WishItem} from '../ui/wishlist';
import useAuth from './useAuth';

type Ctx = {
    items: WishItem[];
    has: (id: number) => boolean;
    add: (p: WishItem) => void;
    remove: (id: number) => void;
    toggle: (p: WishItem) => void;
    clear: () => void;
    isLoading: boolean;
    error: string | null;
};

const Ctx = React.createContext<Ctx | null>(null);

function dedupe(items: WishItem[]): WishItem[] {
    const seen = new Set<number>();
    const result: WishItem[] = [];
    for (const item of items) {
        if (seen.has(item.id)) continue;
        seen.add(item.id);
        result.push(item);
    }
    return result;
}

export function WishlistProvider({children}: {children: React.ReactNode}) {
    const {isAuthenticated, isReady, user, token} = useAuth();
    const [items, setItems] = React.useState<WishItem[]>(() => dedupe(getWishlist()));
    const [isLoading, setIsLoading] = React.useState(false);
    const [error, setError] = React.useState<string | null>(null);
    const syncMode = React.useRef<'unknown' | 'remote' | 'local'>('local');
    const itemsRef = React.useRef(items);

    const setList = React.useCallback((next: WishItem[] | ((prev: WishItem[]) => WishItem[])) => {
        setItems(prev => {
            const resolved = typeof next === 'function' ? (next as (prev: WishItem[]) => WishItem[])(prev) : next;
            const normalized = dedupe(resolved);
            setWishlist(normalized);
            return normalized;
        });
    }, []);

    const handleApiError = React.useCallback((error: unknown) => {
        const err = error as AxiosError<{ message?: string }>;
        const status = err?.response?.status;
        const fallback =
            status === 401
                ? 'Увійдіть, щоб синхронізувати обране.'
                : 'Не вдалося синхронізувати список бажаного.';
        const message = err?.response?.data?.message ?? err?.message ?? fallback;
        console.error('Wishlist API error', error);
        syncMode.current = status === 401 ? 'local' : 'unknown';
        setError(message);
        return status;
    }, []);

    React.useEffect(() => {
        itemsRef.current = items;
    }, [items]);

    const authUserId = user?.id ?? null;

    React.useEffect(() => {
        if (!isReady) {
            if (token) {
                setIsLoading(true);
            }
            return;
        }

        if (!isAuthenticated) {
            syncMode.current = 'local';
            setError(null);
            setIsLoading(false);
            setItems(dedupe(getWishlist()));
            return;
        }

        let cancelled = false;

        const syncWithRemote = async () => {
            setIsLoading(true);
            setError(null);
            try {
                const remote = await WishlistApi.list();
                if (cancelled) return;

                const local = dedupe(getWishlist());
                const remoteIds = new Set(remote.map(item => item.id));
                const extras = local.filter(item => !remoteIds.has(item.id));

                let merged = [...remote];

                if (extras.length) {
                    const results = await Promise.allSettled(
                        extras.map(item => WishlistApi.add(item.id))
                    );
                    if (cancelled) return;

                    let hadFailures = false;
                    results.forEach((result, index) => {
                        const fallback = extras[index];
                        if (result.status === 'fulfilled') {
                            const payload = result.value ?? fallback;
                            merged = [payload, ...merged.filter(x => x.id !== payload.id)];
                        } else {
                            hadFailures = true;
                            console.error('Failed to sync wishlist item', result.reason);
                        }
                    });

                    if (hadFailures) {
                        setError('Деякі товари не вдалося синхронізувати зі списком бажаного.');
                    }
                }

                if (cancelled) return;
                setList(merged);
                syncMode.current = 'remote';
            } catch (error) {
                if (cancelled) return;
                const status = handleApiError(error);
                if (status === 401) {
                    setItems(dedupe(getWishlist()));
                }
            } finally {
                if (!cancelled) {
                    setIsLoading(false);
                }
            }
        };

        syncWithRemote();

        return () => {
            cancelled = true;
        };
    }, [authUserId, handleApiError, isAuthenticated, isReady, setList, token]);

    React.useEffect(() => {
        const onStorage = (e: StorageEvent) => {
            if (e.key === 'wishlist_v1') {
                setItems(dedupe(getWishlist()));
            }
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
                setError(null);
            })
            .catch(handleApiError);
    }, [handleApiError, setList]);

    const syncRemove = React.useCallback((productId: number) => {
        if (syncMode.current === 'local') return;
        WishlistApi.remove(productId)
            .then(() => {
                syncMode.current = 'remote';
                setError(null);
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
        setError(null);
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
        isLoading,
        error,
    }), [add, clear, error, has, isLoading, items, remove, toggle]);

    return <Ctx.Provider value={api}>{children}</Ctx.Provider>;
}

export default function useWishlist() {
    const ctx = React.useContext(Ctx);
    if (!ctx) throw new Error('useWishlist must be used within WishlistProvider');
    return ctx;
}
