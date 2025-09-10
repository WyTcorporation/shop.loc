import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { ProductsApi, type Product } from '../api';
import useCart from '../useCart';
import { useNotify } from '../ui/notify';
import { formatPrice } from '../ui/format';
import SimilarProducts from '../components/SimilarProducts';
import { addRecentlyViewed } from '../ui/recentlyViewed';
import RecentlyViewed from '../components/RecentlyViewed';
import WishlistButton from '../components/WishlistButton';

export default function ProductPage() {
    const { slug } = useParams();
    const [p, setP] = useState<Product | null>(null);

    const [qty, setQty] = useState(1);
    const { add } = useCart();
    const { success, error } = useNotify();
    const navigate = useNavigate();

    // Завантаження продукту
    useEffect(() => {
        let on = true;
        (async () => {
            if (!slug) return;
            try {
                const prod = await ProductsApi.show(slug);
                if (!on) return;
                setP(prod);
                setQty(1);
            } catch {
                // опційно: редирект на каталог
                // navigate('/', { replace: true });
            }
        })();
        return () => { on = false; };
    }, [slug]);

    // Додати у "нещодавно переглянуті"
    useEffect(() => {
        if (!p) return;
        addRecentlyViewed({
            id: p.id,
            slug: p.slug,
            name: p.name,
            price: p.price,
            preview_url:
                p.preview_url ??
                p.images?.find(i => i.is_primary)?.url ??
                p.images?.[0]?.url ??
                null,
        });
    }, [p]);

    if (!p) return <div className="max-w-6xl mx-auto p-6">Завантаження…</div>;

    const stock = Number(p.stock ?? 0);
    const canBuy = stock > 0;
    const primary =
        p.images?.find(i => i.is_primary) ??
        (p.preview_url ? { url: p.preview_url, alt: p.name } : undefined);

    const clampQty = (raw: number) =>
        Math.max(1, Math.min(stock || 1, Number.isFinite(raw) ? raw : 1));

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
            {/* ліва колонка: зображення */}
            <div className="border rounded-xl overflow-hidden">
                <div className="aspect-square bg-muted/40">
                    {primary ? (
                        <img
                            src={primary.url}
                            alt={primary.alt ?? p.name}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                            без фото
                        </div>
                    )}
                </div>
            </div>

            {/* права колонка: деталі */}
            <div className="space-y-4">
                <div className="flex items-start justify-between gap-3">
                    <h1 className="text-2xl font-semibold" data-testid="product-title">
                        {p.name}
                    </h1>
                    <WishlistButton product={p} />
                </div>

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
                        data-testid="qty-input"
                        disabled={!canBuy}
                    />
                    <button
                        disabled={!canBuy}
                        onClick={handleAdd}
                        className="h-9 px-4 rounded-md bg-black text-white disabled:opacity-50"
                        data-testid="add-to-cart"
                    >
                        Додати в кошик
                    </button>
                </div>

                <div>
                    <Link to="/" className="text-sm text-gray-600 hover:underline">
                        ← До каталогу
                    </Link>
                </div>

                {/* Схожі товари + Нещодавно переглянуті */}
                <SimilarProducts categoryId={p.category_id} currentSlug={p.slug} />
                <RecentlyViewed excludeSlug={p.slug} />
            </div>
        </div>
    );
}
