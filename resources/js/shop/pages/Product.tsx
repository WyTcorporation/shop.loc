import React, { useEffect, useMemo, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ProductsApi, ReviewsApi, type Product, type Review } from '../api';
import useCart from '../useCart';
import { useNotify } from '../ui/notify';
import { formatPrice } from '../ui/format';
import SimilarProducts from '../components/SimilarProducts';
import { addRecentlyViewed } from '../ui/recentlyViewed';
import RecentlyViewed from '../components/RecentlyViewed';
import WishlistButton from '../components/WishlistButton';
import SeoHead from '../components/SeoHead';
import JsonLd from '../components/JsonLd';
import { useHreflangs } from '../hooks/useHreflangs';
import ImageLightbox from '../components/ImageLightbox';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '../ui/tabs';
import { GA } from '../ui/ga';
import ReviewList from '../components/ReviewList';
import ReviewForm from '../components/ReviewForm';
import { useLocale } from '../i18n/LocaleProvider';

export default function ProductPage() {
    const { slug } = useParams();
    const [p, setP] = useState<Product | null>(null);
    const [related, setRelated] = useState<Product[]>([]);
    const [loadingRelated, setLoadingRelated] = useState(false);
    const [qty, setQty] = useState(1);
    const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);
    const [reviews, setReviews] = useState<Review[]>([]);
    const [averageRating, setAverageRating] = useState<number | null>(null);
    const [loadingReviews, setLoadingReviews] = useState(false);

    const { add } = useCart();
    const { error } = useNotify();
    const hreflangs = useHreflangs('uk');
    const { t } = useLocale();

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

    // related
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

    useEffect(() => {
        if (p) GA.view_item(p);
    }, [p]);

    useEffect(() => {
        if (!p?.id) return;
        let ignore = false;
        setReviews([]);
        setAverageRating(null);
        setLoadingReviews(true);
        ReviewsApi.list(p.id)
            .then((res) => {
                if (ignore) return;
                const list = Array.isArray(res.data) ? res.data : [];
                const rawAvg = res.average_rating;
                const parsed = (() => {
                    if (rawAvg == null) return null;
                    const numeric = typeof rawAvg === 'string' ? Number(rawAvg) : rawAvg;
                    return Number.isFinite(numeric) ? Number(numeric) : null;
                })();
                setReviews(list);
                setAverageRating(parsed);
            })
            .catch((err) => {
                if (ignore) return;
                console.error('Failed to load reviews', err);
                setReviews([]);
                setAverageRating(null);
            })
            .finally(() => {
                if (ignore) return;
                setLoadingReviews(false);
            });
        return () => {
            ignore = true;
        };
    }, [p?.id]);

    // ---------- derived (hooks стабільні) ----------
    const stock = Number(p?.stock ?? 0);
    const canBuy = stock > 0;

    // повна галерея для лайтбоксу
    const gallery = useMemo(() => {
        if (!p) return [] as { url: string; alt?: string }[];
        const imgs = (p.images ?? [])
            .map(i => ({ url: i.url, alt: i.alt }))
            .filter(i => !!i.url);
        if (!imgs.length && p.preview_url) imgs.push({ url: p.preview_url });
        return imgs;
    }, [p]);

    // головне зображення
    const primaryImg = useMemo(() => {
        if (!p) return undefined as { url: string; alt?: string } | undefined;
        const specific = p.images?.find(i => i.is_primary);
        if (specific) return { url: specific.url, alt: specific.alt };
        if (p.preview_url) return { url: p.preview_url };
        return p.images?.[0] ? { url: p.images[0].url, alt: p.images[0].alt } : undefined;
    }, [p]);

    const brand = t('common.brand');

    const pageTitle = useMemo(
        () => (p
            ? t('product.seo.pageTitle', { name: p.name, price: formatPrice(p.price), brand })
            : t('product.seo.fallbackTitle', { brand })
        ),
        [p, t, brand]
    );

    const pageDescription = useMemo(
        () => (p
            ? t('product.seo.description', { name: p.name, price: formatPrice(p.price), inStock: canBuy })
            : t('product.seo.fallbackDescription')
        ),
        [p, canBuy, t]
    );

    const canonicalUrl = typeof window !== 'undefined' ? window.location.href : undefined;

    // характеристики (під різні формати бекенда)
    const specs = useMemo(() => {
        if (!p) return [] as Array<{ name: string; value: string }>;
        const raw: any = (p as any).attributes ?? (p as any).attrs ?? {};
        if (Array.isArray(raw)) {
            // масив типу [{name,value}] або [{key,value}]
            return raw.map((x: any) => ({
                name: String(x.name ?? x.key ?? ''),
                value: String(x.value ?? ''),
            })).filter(x => x.name);
        }
        if (raw && typeof raw === 'object') {
            return Object.entries(raw).map(([k,v]) => ({ name: String(k), value: String(v as any) }));
        }
        return [];
    }, [p]);

    const productLd = useMemo(() => {
        if (!p) return null;
        const main = primaryImg?.url ?? undefined;
        return {
            '@context': 'https://schema.org',
            '@type': 'Product',
            name: p.name,
            image: main ? [main] : undefined,
            sku: (p as any).sku ?? undefined,
            offers: {
                '@type': 'Offer',
                url: canonicalUrl,
                priceCurrency: 'UAH',
                price: Number(p.price) || 0,
                availability: canBuy ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            }
        };
    }, [p, primaryImg?.url, canonicalUrl, canBuy]);

    const breadcrumbLd = useMemo(() => {
        if (!p) return null;
        return {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: [
                { '@type': 'ListItem', position: 1, name: t('product.seo.breadcrumbHome'), item: typeof window !== 'undefined' ? `${window.location.origin}/` : undefined },
                { '@type': 'ListItem', position: 2, name: t('product.seo.breadcrumbCatalog'), item: typeof window !== 'undefined' ? `${window.location.origin}/` : undefined },
                { '@type': 'ListItem', position: 3, name: p.name, item: canonicalUrl }
            ]
        };
    }, [p, canonicalUrl]);
    // -----------------------------------------------

    if (!p) return <div className="max-w-6xl mx-auto p-6">{t('common.loading')}</div>;

    const clampQty = (raw: number) =>
        Math.max(1, Math.min(stock || 1, Number.isFinite(raw) ? raw : 1));

    async function handleAdd() {
        try {
            await add(p.id, qty);
            GA.add_to_cart(p, qty);
        } catch (e: any) {
            const fallbackMessage = t('product.toasts.added.error');
            const description = e?.response?.data?.message;
            error({ title: fallbackMessage, description });
        }
    }

    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    const ogImage = origin ? `${origin}/og/product/${p.slug}.png` : primaryImg?.url;

    const onPrev = () => {
        if (lightboxIndex == null || gallery.length === 0) return;
        setLightboxIndex((lightboxIndex - 1 + gallery.length) % gallery.length);
    };
    const onNext = () => {
        if (lightboxIndex == null || gallery.length === 0) return;
        setLightboxIndex((lightboxIndex + 1) % gallery.length);
    };

    const handleReviewSubmitted = (review: Review) => {
        setReviews((prev) => [review, ...prev]);
    };

    return (
        <div className="max-w-6xl mx-auto grid gap-6 p-4 md:grid-cols-2">
            <SeoHead
                title={pageTitle}
                description={pageDescription}
                image={ogImage}
                hreflangs={hreflangs}
                canonical
            />
            {productLd && <JsonLd data={productLd} />}
            {breadcrumbLd && <JsonLd data={breadcrumbLd} />}

            {/* left: image + thumbnails */}
            <div className="space-y-3">
                <div className="border rounded-xl overflow-hidden cursor-zoom-in" onClick={() => gallery.length && setLightboxIndex(0)}>
                    <div className="aspect-square bg-muted/40">
                        {primaryImg ? (
                            <img src={primaryImg.url} alt={primaryImg.alt ?? p.name} className="h-full w-full object-cover" />
                        ) : (
                            <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                                {t('product.gallery.noImage')}
                            </div>
                        )}
                    </div>
                </div>

                {gallery.length > 1 && (
                    <div className="grid grid-cols-5 gap-2">
                        {gallery.map((g, i) => (
                            <button
                                key={i}
                                type="button"
                                className="aspect-square overflow-hidden rounded border hover:opacity-90"
                                onClick={() => setLightboxIndex(i)}
                                aria-label={t('product.gallery.openImage', { index: i + 1 })}
                            >
                                <img src={g.url} alt={g.alt ?? ''} className="h-full w-full object-cover" />
                            </button>
                        ))}
                    </div>
                )}
            </div>

            {/* right: details */}
            <div className="space-y-4">
                <h1 className="text-2xl font-semibold flex items-center gap-3">
                    {p.name}
                    <WishlistButton product={p} />
                </h1>

                <div className="text-xl">{formatPrice(p.price)}</div>

                <div className="text-sm text-muted-foreground">
                    {loadingReviews
                        ? t('product.reviews.summary.loading')
                        : averageRating != null
                            ? (
                                <>
                                    {t('product.reviews.summary.label')}{' '}
                                    <span className="font-medium">{averageRating.toFixed(1)}</span>{' '}
                                    {t('product.reviews.summary.of', { max: 5 })}
                                </>
                            )
                            : t('product.reviews.summary.empty')}
                </div>

                {canBuy ? (
                    <div className="text-sm text-green-700">{t('product.stock.available', { count: stock })}</div>
                ) : (
                    <div className="text-sm text-red-600">{t('product.stock.unavailable')}</div>
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
                        {t('product.actions.addToCart')}
                    </button>
                </div>

                {/* TABS: Опис / Характеристики / Доставка */}
                <Tabs defaultValue="desc" className="mt-6">
                    <TabsList className="grid w-full grid-cols-3">
                        <TabsTrigger value="desc">{t('product.tabs.description')}</TabsTrigger>
                        <TabsTrigger value="specs">{t('product.tabs.specs')}</TabsTrigger>
                        <TabsTrigger value="delivery">{t('product.tabs.delivery')}</TabsTrigger>
                    </TabsList>

                    <TabsContent value="desc" className="mt-3 text-sm leading-relaxed">
                        {((p as any).description && String((p as any).description).trim().length)
                            ? <div dangerouslySetInnerHTML={{ __html: String((p as any).description) }} />
                            : <div className="text-muted-foreground">{t('product.description.empty')}</div>}
                    </TabsContent>

                    <TabsContent value="specs" className="mt-3">
                        {specs.length === 0 ? (
                            <div className="text-sm text-muted-foreground">{t('product.specs.empty')}</div>
                        ) : (
                            <dl className="grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2">
                                {specs.map((s, i) => (
                                    <div key={`${s.name}-${i}`} className="border-b py-2">
                                        <dt className="text-xs uppercase tracking-wide text-muted-foreground">{s.name}</dt>
                                        <dd className="text-sm">{s.value}</dd>
                                    </div>
                                ))}
                            </dl>
                        )}
                    </TabsContent>

                    <TabsContent value="delivery" className="mt-3 text-sm">
                        <ul className="list-disc pl-5 space-y-1">
                            <li>{t('product.delivery.items.novaPoshta')}</li>
                            <li>{t('product.delivery.items.courier')}</li>
                            <li>{t('product.delivery.items.payment')}</li>
                            <li>{t('product.delivery.items.returns')}</li>
                        </ul>
                    </TabsContent>
                </Tabs>

                <div className="space-y-6 border-t pt-6">
                    <ReviewList reviews={reviews} averageRating={averageRating} loading={loadingReviews} />
                    <ReviewForm productId={p.id} onSubmitted={handleReviewSubmitted} />
                </div>

                <div>
                    <Link to="/" className="text-sm text-gray-600 hover:underline">{t('product.actions.backToCatalog')}</Link>
                </div>

                {related.length > 0 && (
                    <div className="text-xs text-muted-foreground mb-2">
                        {t('product.similar.count', { count: related.length })}
                    </div>
                )}
                <SimilarProducts categoryId={p.category_id} currentSlug={p.slug} />
                <RecentlyViewed excludeSlug={p.slug} />
            </div>

            {/* Lightbox overlay */}
            <ImageLightbox
                images={gallery}
                openIndex={lightboxIndex}
                onClose={() => setLightboxIndex(null)}
                onPrev={onPrev}
                onNext={onNext}
            />
        </div>
    );
}
