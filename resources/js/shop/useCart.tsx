import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import {CartApi, type Cart, resetCartCache} from './api';
import { useNotify } from './ui/notify';
import { useLocale } from './i18n/LocaleProvider';

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
    const { t } = useLocale();

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
            notify.success({
                title: t('notify.cart.add.success'),
                action: { label: t('notify.cart.add.action'), onClick: () => location.assign('/cart') },
            });
        } catch (e: any) {
            const msg = e?.response?.data?.message;
            if (e?.response?.status === 422 && msg === 'Not enough stock') {
                notify.error(t('notify.cart.add.outOfStock'));
            } else {
                notify.error(t('notify.cart.add.error'));
            }
            throw e;
        }
    }, [notify, t]);

    const update = useCallback(async (item_id: number, qty: number) => {
        try {
            const c = await CartApi.update(item_id, qty);
            setCart(c);
            notify.success(t('notify.cart.update.success'));
        } catch (e: any) {
            const msg = e?.response?.data?.message;
            if (e?.response?.status === 422 && msg === 'Not enough stock') {
                notify.error(t('notify.cart.update.outOfStock'));
            } else {
                notify.error(t('notify.cart.update.error'));
            }
            throw e;
        }
    }, [notify, t]);

    const remove = useCallback(async (item_id: number) => {
        const c = await CartApi.remove(item_id);
        setCart(c);
        notify.success(t('notify.cart.remove.success'));
    }, [notify, t]);

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
