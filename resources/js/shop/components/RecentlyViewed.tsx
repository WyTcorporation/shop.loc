import React from 'react';
import { Card } from '@/components/ui/card';
import { Link } from 'react-router-dom';
import { formatPrice } from '../ui/format';
import { useRecentlyViewed } from '../hooks/useRecentlyViewed';

export default function RecentlyViewed({ excludeSlug }: { excludeSlug?: string }) {
    const { items, clear } = useRecentlyViewed();
    const list = items
        .filter(p => (excludeSlug ? p.slug !== excludeSlug : true))
        .slice(0, 8);

    if (list.length === 0) return null;

    return (
        <section className="mt-8">
            <div className="mb-3 flex items-center justify-between">
                <h2 className="text-lg font-semibold">Ви переглядали</h2>
                <button onClick={clear} className="text-xs text-muted-foreground hover:underline">
                    Очистити
                </button>
            </div>

            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                {list.map(p => {
                    const primary =
                        p.images?.find(i => i.is_primary) ??
                        (p.preview_url ? { url: p.preview_url } : undefined);

                    return (
                        <Card key={p.slug ?? p.id} className="overflow-hidden">
                            <Link to={`/product/${p.slug ?? p.id}`} className="block" data-testid="recently-card">
                                <div className="aspect-square bg-muted/40">
                                    {primary ? (
                                        <img src={primary.url} alt={p.name} className="h-full w-full object-cover" />
                                    ) : (
                                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                                            без фото
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
        </section>
    );
}
