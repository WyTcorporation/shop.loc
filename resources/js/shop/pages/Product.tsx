import React, { useEffect, useMemo, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { ProductsApi, type Product } from '../api';
import useCart from '../useCart';
import { useNotify } from '../ui/notify';
import { formatPrice } from '../ui/format';
import { Card } from '@/components/ui/card';
import SimilarProducts from '../components/SimilarProducts';
import { addRecentlyViewed } from '../ui/recentlyViewed';
import RecentlyViewed from '../components/RecentlyViewed';
import WishlistButton from '../components/WishlistButton';
import SeoHead from '../components/SeoHead';
import JsonLd from '../components/JsonLd';

export default function ProductPage() {
    const { slug } = useParams();
    const [p, setP] = useState<Product | null>(null);

    const [related, setRelated] = useState<Product[]>([]);
    const [loadingRelated, setLoadingRelated] = useState(false);

    const [qty, setQty] = useState(1);
    const { add } = useCart();
    const { success, error } = useNotify();
    const navigate = useNavigate();

    // fetch product
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

    // recently viewed
    useEffect(() => {
        if (!p) return;
        addRecentlyViewed({
            id: p.id,
            slug: p.slug,
            name: p.name,
            price: p.price,
            preview_url: p.preview_url ?? p.images?.find(i => i.is_primary)?.url ?? p.images?.[0]?.url ?? null,
        });
    }, [p]);

    // related products
    useEffect(() => {
        let on = true;
        (async () => {
            if (!p?.category_id) {
                setRelated([]);
                return;
            }
            setLoadingRelated(true);
            try {
                const items = await ProductsApi.related(p.category_id, p.id, 4);
                if (on) setRelated(items);
            } finally {
                if (on) setLoadingRelated(false);
            }
        })();
        return () => { on = false; };
    }, [p?.id, p?.category_id]);

    // ---- SAFE DERIVED VALUES (hooks must run every render) ----
    const stock = Number(p?.stock ?? 0);
    const canBuy = stock > 0;

    const primaryImg = useMemo(
        () => p ? (p.images?.find(i => i.is_primary) ?? (p.preview_url ? { url: p.preview_url } : undefined)) : undefined,
        [p]
    );
    const primaryImgUrl = primaryImg?.url ?? undefined;

    const pageTitle = useMemo(
        () => p ? `${p.name} — ${formatPrice(p.price)} — Shop` : 'Товар — Shop',
        [p]
    );
    const pageDescription = useMemo(
        () => p
            ? `Купити ${p.name} за ${formatPrice(p.price)}. ${canBuy ? 'В наявності.' : 'Наразі немає в наявності.'} Замовити онлайн.`
            : 'Картка товару в магазині.',
        [p, canBuy]
    );
    const canonicalUrl = typeof window !== 'undefined' ? window.location.href : undefined;

    const productLd = useMemo(() => {
        if (!p) return null;
        return {
            '@context': 'https://schema.org',
            '@type': 'Product',
            name: p.name,
            image: primaryImgUrl ? [primaryImgUrl] : undefined,
            sku: (p as any).sku ?? undefined,
            offers: {
                '@type': 'Offer',
                url: canonicalUrl,
                priceCurrency: 'UAH',
                price: Number(p.price) || 0,
                availability: canBuy ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            }
        };
    }, [p, primaryImgUrl, canonicalUrl, canBuy]);

    const breadcrumbLd = useMemo(() => {
        if (!p) return null;
        return {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: [
                { '@type': 'ListItem', position: 1, name: 'Головна', item: typeof window !== 'undefined' ? `${window.location.origin}/` : undefined },
                { '@type': 'ListItem', position: 2, name: 'Каталог', item: typeof window !== 'undefined' ? `${window.location.origin}/` : undefined },
                { '@type': 'ListItem', position: 3, name: p.name, item: canonicalUrl }
            ]
        };
    }, [p, canonicalUrl]);
    // -----------------------------------------------------------

    // early return is OK now (all hooks above already executed on every render)
    if (!p) return <div className="max-w-6xl mx-auto p-6">Loading…</div>;

    const clampQty = (raw: number) => Math.max(1, Math.min(stock || 1, Number.isFinite(raw) ? raw : 1));

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
            <SeoHead
                title={pageTitle}
                description={pageDescription}
                image={primaryImgUrl}
                canonical
            />
            {productLd && <JsonLd data={productLd} />}
            {breadcrumbLd && <JsonLd data={breadcrumbLd} />}

            {/* left: image */}
            <div className="border rounded-xl overflow-hidden">
                <div className="aspect-square bg-muted/40">
                    {primaryImg ? (
                        <img src={primaryImg.url} alt={(primaryImg as any).alt ?? p.name} className="h-full w-full object-cover" />
                    ) : (
                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">без фото</div>
                    )}
                </div>
            </div>

            {/* right: details */}
            <div className="space-y-4">
                <h1 className="text-2xl font-semibold flex items-center gap-3">
                    {p.name}
                    <WishlistButton product={p} />
                </h1>

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
                    <Link to="/" className="text-sm text-gray-600 hover:underline">← До каталогу</Link>
                </div>

                {related.length > 0 && (
                    <div className="text-xs text-muted-foreground mb-2">
                        Знайдено схожих: {related.length}
                    </div>
                )}
                <SimilarProducts categoryId={p.category_id} currentSlug={p.slug} />
                <RecentlyViewed excludeSlug={p.slug} />
            </div>
        </div>
    );
}
