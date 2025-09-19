import React from 'react';
import { Link } from 'react-router-dom';
import { Card } from '@/components/ui/card';
import { formatPrice } from '../ui/format';
import { fetchProducts, type Product, type Paginated } from '../api';
import { useLocale } from '../i18n/LocaleProvider';

type Props = {
    categoryId?: number;
    currentSlug?: string;
    limit?: number; // по замовчуванню 4
    title?: string;
};

export default function SimilarProducts({ categoryId, currentSlug, limit = 4, title }: Props) {
    const [items, setItems] = React.useState<Product[]>([]);
    const [loading, setLoading] = React.useState(false);
    const { t } = useLocale();
    const heading = title ?? t('product.similar.title');

    React.useEffect(() => {
        let on = true;
        if (!categoryId) {
            setItems([]);
            return;
        }
        (async () => {
            setLoading(true);
            try {
                // Беремо звичайний список по категорії і фільтруємо поточний товар.
                const res: Paginated<Product> = await fetchProducts({
                    page: 1,
                    per_page: limit + 1, // трошки з запасом
                    category_id: categoryId,
                    sort: 'new',
                });
                if (!on) return;
                const arr = (res.data ?? []).filter(p => (currentSlug ? p.slug !== currentSlug : true)).slice(0, limit);
                setItems(arr);
            } finally {
                if (on) setLoading(false);
            }
        })();
        return () => { on = false; };
    }, [categoryId, currentSlug, limit]);

    return (
        <section className="mt-8" data-testid="similar-section">
            <h2 className="mb-3 text-lg font-semibold">{heading}</h2>

            {loading ? (
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4" data-testid="similar-skel">
                    {Array.from({ length: limit }).map((_, i) => (
                        <Card key={i} className="p-3">
                            <div className="mb-3 h-32 w-full rounded bg-muted/40" />
                            <div className="mb-2 h-4 w-3/4 rounded bg-muted/40" />
                            <div className="h-4 w-1/2 rounded bg-muted/40" />
                        </Card>
                    ))}
                </div>
            ) : items.length === 0 ? (
                <div className="text-sm text-muted-foreground" data-testid="similar-empty">
                    {t('product.similar.empty')}
                </div>
            ) : (
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    {items.map((p) => {
                        const primary =
                            p.images?.find(img => img.is_primary) ??
                            (p.preview_url ? { url: p.preview_url } as any : undefined);

                        return (
                            <Card key={p.id} className="overflow-hidden" data-testid="similar-card">
                                <Link to={`/product/${p.slug ?? p.id}`} className="block">
                                    <div className="aspect-square bg-muted/40">
                                        {primary ? (
                                            <img src={primary.url} alt={primary.alt ?? p.name} className="h-full w-full object-cover" />
                                        ) : (
                                            <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                                                {t('product.similar.noImage')}
                                            </div>
                                        )}
                                    </div>
                                    <div className="p-3">
                                        <div className="line-clamp-2 text-sm font-medium">{p.name}</div>
                                        <div className="mt-1 text-sm text-muted-foreground">{formatPrice(p.price)}</div>
                                    </div>
                                </Link>
                            </Card>
                        );
                    })}
                </div>
            )}
        </section>
    );
}
