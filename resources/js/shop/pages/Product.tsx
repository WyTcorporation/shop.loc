import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { ProductsApi, type Product, fetchCategories, type Category } from '../api';
import useCart from '../useCart';
import { useNotify } from '../ui/notify';
import { formatPrice } from '../ui/format';
import SimilarProducts from '../components/SimilarProducts';
import { addRecentlyViewed } from '../ui/recentlyViewed';
import RecentlyViewed from '../components/RecentlyViewed';
import WishlistButton from '../components/WishlistButton';
import Breadcrumbs from '../components/Breadcrumbs';
import { Skeleton } from '@/components/ui/skeleton';
import ImageLightbox, { type LightboxImage } from '../components/ImageLightbox'
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

    const [lbOpen, setLbOpen] = useState(false);
    const [lbIndex, setLbIndex] = useState(0);

    // Категорії (щоб показати назву у breadcrumbs)
    const [cats, setCats] = useState<Category[]>([]);
    useEffect(() => {
        let on = true;
        (async () => {
            try {
                const c = await fetchCategories();
                if (on) setCats(c);
            } catch { /* no-op */ }
        })();
        return () => { on = false; };
    }, []);

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

    // ---------- SKELETON while loading ----------
    if (!p) {
        return (
            <div className="max-w-6xl mx-auto grid gap-6 p-4 md:grid-cols-2">
                <div>
                    <Skeleton className="aspect-square w-full rounded-xl" />
                </div>
                <div className="space-y-4">
                    <Skeleton className="h-6 w-2/3" />
                    <Skeleton className="h-5 w-32" />
                    <div className="flex items-center gap-2">
                        <Skeleton className="h-9 w-24" />
                        <Skeleton className="h-9 w-36" />
                        <Skeleton className="h-9 w-40" />
                    </div>
                    <Skeleton className="h-4 w-40" />
                    <Skeleton className="h-6 w-1/2" />
                    <Skeleton className="h-6 w-2/3" />
                </div>
            </div>
        );
    }

    const stock = Number(p.stock ?? 0);
    const canBuy = stock > 0;
    const primary =
        p.images?.find(i => i.is_primary) ?? (p.preview_url ? { url: p.preview_url } : undefined);

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

    const catName = cats.find(c => Number(c.id) === Number(p.category_id))?.name;

    const gallery: LightboxImage[] = [
        ...(primary ? [{ url: primary.url, alt: primary.alt ?? p.name }] : []),
        ...(p.images?.filter(i => !i.is_primary).map(i => ({ url: i.url, alt: i.alt ?? p.name })) ?? []),
    ];

// SEO для Product
    const primaryImg = p.images?.find(i => i.is_primary)?.url
        ?? p.preview_url
        ?? p.images?.[0]?.url
        ?? undefined;

    const productTitle = `${p.name} — Купити — Shop`;
    const productDesc  = `${p.name}. Ціна ${formatPrice(p.price)}. ${stock > 0 ? `В наявності ${stock} шт.` : 'Немає в наявності'}`;
    const productUrl   = typeof window !== 'undefined' ? window.location.href : undefined;

// JSON-LD Product
    const productLd = {
        '@context': 'https://schema.org',
        '@type': 'Product',
        name: p.name,
        image: primaryImg ? [primaryImg] : undefined,
        description: productDesc,
        sku: p.sku,
        offers: {
            '@type': 'Offer',
            url: productUrl,
            priceCurrency: 'EUR',           // якщо інша валюта — підстав свій код
            price: Number(p.price) || 0,
            availability: stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            itemCondition: 'https://schema.org/NewCondition'
        }
    };

// JSON-LD Breadcrumbs (Головна → Каталог → Продукт)
    const breadcrumbProductLd = {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Головна', item: typeof window !== 'undefined' ? `${window.location.origin}/` : undefined },
            { '@type': 'ListItem', position: 2, name: 'Каталог', item: typeof window !== 'undefined' ? `${window.location.origin}/` : undefined },
            { '@type': 'ListItem', position: 3, name: p.name, item: productUrl }
        ]
    };


    return (
        <div className="max-w-6xl mx-auto grid gap-6 p-4 md:grid-cols-2">
            <SeoHead
                title={productTitle}
                description={productDesc}
                canonical
                image={primaryImg}
            />
            <JsonLd data={productLd} />
            <JsonLd data={breadcrumbProductLd} />

            {/* ліва колонка: зображення */}
            <div className="border rounded-xl overflow-hidden">
                <div className="aspect-square bg-muted/40">
                    {primary ? (
                        <img
                            src={primary.url}
                            alt={primary.alt ?? p.name}
                            className="h-full w-full cursor-zoom-in object-cover"
                            onClick={() => { setLbIndex(0); setLbOpen(true); }}
                        />
                    ) : (
                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">без фото</div>
                    )}
                </div>

                {/* міні-галерея під основним фото (якщо є ще картинки) */}
                {gallery.length > 1 && (
                    <div className="grid grid-cols-6 gap-2 p-3">
                        {gallery.map((g, i) => (
                            <button
                                key={i}
                                className={`overflow-hidden rounded-md border ${i === lbIndex ? 'ring-2 ring-black' : ''}`}
                                onClick={() => { setLbIndex(i); setLbOpen(true); }}
                                aria-label={`Open image ${i + 1}`}
                            >
                                <img src={g.url} alt="" className="h-16 w-full object-cover" />
                            </button>
                        ))}
                    </div>
                )}
            </div>

            {/* права колонка: деталі */}
            <div className="space-y-4">
                <div className="flex items-start justify-between gap-3">
                    <h1 className="text-2xl font-semibold">{p.name}</h1>
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
            {lbOpen && gallery.length > 0 && (
                <ImageLightbox
                    images={gallery}
                    index={lbIndex}
                    onClose={() => setLbOpen(false)}
                    onIndexChange={setLbIndex}
                />
            )}
        </div>
    );
}
