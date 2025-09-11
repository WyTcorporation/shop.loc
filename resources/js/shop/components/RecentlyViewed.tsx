import React from 'react';
import { Link } from 'react-router-dom';
import { Card } from '@/components/ui/card';
import { formatPrice } from '../ui/format';

type RVItem = {
    id: number;
    slug?: string;
    name: string;
    price: number | string;
    preview_url?: string | null;
};

type Props = {
    excludeSlug?: string;
    limit?: number; // 4 за замовчуванням
    title?: string;
};

function readFromStorage(): RVItem[] {
    try {
        const raw = localStorage.getItem('recently_viewed');
        const arr = raw ? JSON.parse(raw) : [];
        return Array.isArray(arr) ? arr : [];
    } catch {
        return [];
    }
}

export default function RecentlyViewed({ excludeSlug, limit = 4, title = 'Ви нещодавно переглядали' }: Props) {
    const [items, setItems] = React.useState<RVItem[]>([]);
    const [loading, setLoading] = React.useState(true);

    React.useEffect(() => {
        // миттєве читання з localStorage + мікрозатримка, щоб уникнути миготіння
        const arr = readFromStorage()
            .filter(i => (excludeSlug ? i.slug !== excludeSlug : true))
            .slice(0, limit);
        const t = setTimeout(() => { setItems(arr); setLoading(false); }, 150);
        return () => clearTimeout(t);
    }, [excludeSlug, limit]);

    return (
        <section className="mt-10" data-testid="recently-section">
            <h2 className="mb-3 text-lg font-semibold">{title}</h2>

            {loading ? (
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4" data-testid="recently-skel">
                    {Array.from({ length: limit }).map((_, i) => (
                        <Card key={i} className="p-3">
                            <div className="mb-3 h-32 w-full rounded bg-muted/40" />
                            <div className="mb-2 h-4 w-3/4 rounded bg-muted/40" />
                            <div className="h-4 w-1/2 rounded bg-muted/40" />
                        </Card>
                    ))}
                </div>
            ) : items.length === 0 ? (
                <div className="text-sm text-muted-foreground" data-testid="recently-empty">
                    Ще не переглядали жодного товару.
                </div>
            ) : (
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    {items.map((p) => (
                        <Card key={`${p.slug ?? p.id}`} className="overflow-hidden" data-testid="recently-card">
                            <Link to={`/product/${p.slug ?? p.id}`} className="block">
                                <div className="aspect-square bg-muted/40">
                                    {p.preview_url ? (
                                        <img src={p.preview_url} alt={p.name} className="h-full w-full object-cover" />
                                    ) : (
                                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">без фото</div>
                                    )}
                                </div>
                                <div className="p-3">
                                    <div className="line-clamp-2 text-sm font-medium">{p.name}</div>
                                    <div className="mt-1 text-sm text-muted-foreground">{formatPrice(p.price)}</div>
                                </div>
                            </Link>
                        </Card>
                    ))}
                </div>
            )}
        </section>
    );
}
