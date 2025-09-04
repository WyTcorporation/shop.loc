import { useEffect, useState, useMemo } from 'react';
import { CartApi, type Cart } from './api';

export default function useCart() {
    const [cart, setCart] = useState<Cart | null>(null);
    useEffect(() => { CartApi.get().then(setCart); }, []);

    const add    = async (product_id: number, qty = 1) => setCart(await CartApi.add(product_id, qty));
    const update = async (itemId: number, qty: number) => setCart(await CartApi.update(itemId, qty)); // <-- qty
    const remove = async (itemId: number) => setCart(await CartApi.remove(itemId));

    // const clear  = async () => setCart(await CartApi.clear());

    const total = useMemo(() => Number(cart?.total ?? 0), [cart]);

    return { cart, total, add, update, remove };
}
