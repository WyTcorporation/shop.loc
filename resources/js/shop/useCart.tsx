import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import {CartApi, type Cart, resetCartCache} from './api';
import { useNotify } from './ui/notify';

type Ctx = {
    cart: Cart | null;
    total: number;
    add: (product_id: number, qty?: number) => Promise<void>;
    update: (item_id: number, qty: number) => Promise<void>;
    remove: (item_id: number) => Promise<void>;
    clear: () => Promise<void>;
    reload: () => Promise<void>;
};

const CartCtx = createContext<Ctx | null>(null);

export const CartProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const notify = useNotify();
    const [cart, setCart] = useState<Cart | null>(null);

    const total = useMemo(() => {
        if (!cart) return 0;
        if (typeof cart.total === 'number') return cart.total;
        return (cart.items ?? []).reduce((s, it) => s + Number(it.price ?? 0) * Number(it.qty ?? 0), 0);
    }, [cart]);

    useEffect(() => {
        CartApi.get().then(setCart).catch(() => setCart({ id: '', status: 'active', items: [], total: 0 } as any));
    }, []);

    const reload = useCallback(async () => {
        const c = await CartApi.refresh();
        setCart(c);
    }, []);

    const add = useCallback(async (product_id: number, qty = 1) => {
        try {
            const c = await CartApi.add(product_id, qty);
            setCart(c);
            notify.success('Додано до кошика', {
                key: 'cart-add',
                action: { label: 'Відкрити кошик', onClick: () => location.assign('/cart') },
                ttl: 3000,
            });
        } catch (e: any) {
            const msg = e?.response?.data?.message;
            if (e?.response?.status === 422 && msg === 'Not enough stock') {
                notify.error('Недостатньо на складі', { key: 'cart-add', ttl: 5000 });
            } else {
                notify.error('Не вдалося додати до кошика', { key: 'cart-add', ttl: 4000 });
            }
            throw e;
        }
    }, [notify]);

    const update = useCallback(async (item_id: number, qty: number) => {
        try {
            const c = await CartApi.update(item_id, qty);
            setCart(c);
            notify.success('Кількість оновлено', { key: 'cart-update', ttl: 2000 });
        } catch (e: any) {
            const msg = e?.response?.data?.message;
            if (e?.response?.status === 422 && msg === 'Not enough stock') {
                notify.error('Недостатньо на складі', { key: 'cart-update', ttl: 4000 });
            } else {
                notify.error('Помилка оновлення кошика', { key: 'cart-update', ttl: 4000 });
            }
            throw e;
        }
    }, [notify]);

    const remove = useCallback(async (item_id: number) => {
        const c = await CartApi.remove(item_id);
        setCart(c);
        notify.success('Видалено з кошика', { key: 'cart-remove', ttl: 2000 });
    }, [notify]);

    /** Після успішного замовлення просто рефрешимо — бек створить новий активний кошик і виставить нову cookie */
    const clear = useCallback(async () => {
        resetCartCache();
        const c = await CartApi.get();
        setCart(c);
    }, []);

    const value = useMemo<Ctx>(() => ({
        cart, total, add, update, remove, clear, reload
    }), [cart, total, add, update, remove, clear, reload]);

    return React.createElement(CartCtx.Provider, { value }, children);
};

export default function useCart(): Ctx {
    const ctx = useContext(CartCtx);
    if (!ctx) throw new Error('useCart must be used within <CartProvider>');
    return ctx;
}
