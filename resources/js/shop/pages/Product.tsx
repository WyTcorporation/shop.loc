import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { ProductsApi } from '../api';
import useCart from '../useCart';
import { useNotify } from '../ui/notify';
import { formatPrice } from '../ui/format';

type Product = {
    id: number; slug?: string; name: string; price: number | string; stock?: number;
    images?: { url: string; alt?: string; is_primary?: boolean }[];
    preview_url?: string | null;
};

export default function ProductPage() {
    const { slug } = useParams();
    const [p, setP] = useState<Product | null>(null);
    const [qty, setQty] = useState(1);
    const { add } = useCart();
    const { success, error } = useNotify();
    const navigate = useNavigate();

    useEffect(() => {
        let on = true;
        (async () => {
            const prod = await ProductsApi.show(slug!);
            if (!on) return;
            setP(prod);
            setQty(1);
        })();
        return () => { on = false; };
    }, [slug]);

    if (!p) return <div className="max-w-6xl mx-auto p-6">Loading…</div>;

    const stock = Number(p.stock ?? 0);
    const canBuy = stock > 0;
    const primary =
        p.images?.find(i => i.is_primary) ?? (p.preview_url ? { url: p.preview_url } : undefined);

    const clampQty = (raw: number) => {
        const n = Math.max(1, Math.min(stock || 1, raw));
        return Number.isFinite(n) ? n : 1;
    };

    async function handleAdd() {
        try {
            await add(p.id, qty);
            success({
                title: 'Додано до кошика',
                action: { label: 'Відкрити кошик', onClick: () => navigate('/cart') },
            });
        } catch (e: any) {
            const message = e?.response?.data?.message || 'Не вдалося додати';
            error({ title: message });
        }
    }

    return (
        <div className="max-w-6xl mx-auto grid gap-6 p-4 md:grid-cols-2">
            <div className="border rounded-xl overflow-hidden">
                <div className="aspect-square bg-muted/40">
                    {primary ? (
                        <img src={primary.url} alt={primary.alt ?? p.name} className="h-full w-full object-cover" />
                    ) : (
                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">без фото</div>
                    )}
                </div>
            </div>

            <div className="space-y-4">
                <h1 className="text-2xl font-semibold">{p.name}</h1>
                <div className="text-xl">{formatPrice(p.price)}</div>

                {canBuy ? (
                    <div className="text-sm text-green-700">В наявності: {stock} шт.</div>
                ) : (
                    <div className="text-sm text-red-600">Немає в наявності</div>
                )}

                <div className="flex items-center gap-2">
                    <input
                        type="number"
                        min={1}
                        max={Math.max(1, stock)}
                        value={qty}
                        onChange={(e) => setQty(clampQty(Number(e.target.value)))}
                        className="h-9 w-24 rounded-md border px-3"
                    />
                    <button
                        disabled={!canBuy}
                        onClick={handleAdd}
                        className="h-9 px-4 rounded-md bg-black text-white disabled:opacity-50"
                    >
                        Додати в кошик
                    </button>
                </div>

                <div>
                    <Link to="/" className="text-sm text-gray-600 hover:underline">← До каталогу</Link>
                </div>
            </div>
        </div>
    );
}
