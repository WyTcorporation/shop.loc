import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { OrdersApi, CartApi } from '../api';
import useCart from '../useCart';
import { useNotify } from '../ui/notify';
import SeoHead from '../components/SeoHead';

export default function CheckoutPage() {
    const nav = useNavigate();
    const notify = useNotify();
    const { clear } = useCart();

    const [email, setEmail] = useState('test@example.com');
    const [name, setName] = useState('John');
    const [city, setCity] = useState('Kyiv');
    const [addr, setAddr] = useState('Street 1');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string|null>(null);

    useEffect(() => {
        (async () => {
            try {
                const cart = await CartApi.get();
                const itemsCount = cart?.items?.length ?? 0;
                if (!cart || cart.status !== 'active' || itemsCount === 0) {
                    // @ts-ignore
                    notify.error('Кошик порожній або завершений', { key: 'checkout-guard' });
                    nav('/cart', { replace: true });
                }
            } catch {
                // @ts-ignore
                notify.error('Проблема з кошиком', { key: 'checkout-guard' });
                nav('/cart', { replace: true });
            }
        })();
    }, [nav, notify]);

    const submit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            const res = await OrdersApi.create({
                email,
                shipping_address: { name, city, addr },
            });

            // @ts-ignore
            notify.success('Замовлення оформлено', { key: 'order', ttl: 3000 });
            nav(`/order/${res.number}`, { replace: true });

            try { await clear?.(); } catch {/* no-op */}

        } catch (e: any) {
            setError(e?.response?.data?.message || 'Failed to place order');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-md mx-auto p-6 space-y-4">
            <SeoHead title="Оформлення замовлення — Shop" robots="noindex,nofollow" canonical />
            <h1 className="text-2xl font-semibold">Checkout</h1>
            {error && <div className="p-2 bg-red-50 border border-red-200 rounded text-red-700">{error}</div>}
            <form onSubmit={submit} className="space-y-3">
                <input className="border rounded px-3 py-2 w-full" placeholder="Email" data-testid="email" value={email} onChange={e=>setEmail(e.target.value)} />
                <input className="border rounded px-3 py-2 w-full" placeholder="Name" value={name} data-testid="shipping-name" onChange={e=>setName(e.target.value)} />
                <input className="border rounded px-3 py-2 w-full" placeholder="City" value={city} data-testid="shipping-city" onChange={e=>setCity(e.target.value)} />
                <input className="border rounded px-3 py-2 w-full" placeholder="Address" value={addr} data-testid="shipping-addr" onChange={e=>setAddr(e.target.value)} />
                <button disabled={loading} className="px-4 py-2 rounded bg-black text-white" data-testid="place-order">{loading ? 'Placing…' : 'Place order'}</button>
            </form>
        </div>
    );
}
