import React from 'react';
import { Link } from 'react-router-dom';
import { Card } from '@/components/ui/card';
import { formatPrice } from '../ui/format';
import { getRecentlyViewed, type MiniProduct } from '../ui/recentlyViewed';

export default function RecentlyViewed({
                                           excludeSlug,
                                           max = 8,
                                       }: { excludeSlug?: string | null; max?: number }) {
    const [items, setItems] = React.useState<MiniProduct[]>([]);

    React.useEffect(() => {
        const all = getRecentlyViewed();
        const filtered = excludeSlug ? all.filter(i => i.slug !== excludeSlug) : all;
        setItems(filtered.slice(0, max));
    }, [excludeSlug, max]);

    if (items.length === 0) return null;

    return (
        <section className="mt-10" data-testid="recently-viewed">
            <h2 className="mb-3 text-lg font-semibold">Нещодавно переглянуті</h2>
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-6">
                {items.map((p) => {
                    const href = `/product/${p.slug ?? p.id}`;
                    return (
                        <Card key={`${p.id}-${p.slug ?? ''}`} className="overflow-hidden">
                            <Link to={href} className="block">
                                <div className="aspect-square bg-muted/40">
                                    {p.preview_url ? (
                                        <img
                                            src={p.preview_url}
                                            alt={p.name}
                                            className="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                    ) : (
                                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                                            без фото
                                        </div>
                                    )}
                                </div>
                                <div className="p-3">
                                    <div className="line-clamp-2 text-sm font-medium">{p.name}</div>
                                    <div className="mt-1 text-sm text-muted-foreground">
                                        {formatPrice(p.price)}
                                    </div>
                                </div>
                            </Link>
                        </Card>
                    );
                })}
            </div>
        </section>
    );
}
