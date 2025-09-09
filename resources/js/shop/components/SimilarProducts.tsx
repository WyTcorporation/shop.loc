import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { ProductsApi } from '../api';
import { formatPrice } from '../ui/format';

type ListProduct = {
    id: number;
    name: string;
    slug: string;
    category_id: number;
    preview_url?: string | null;
    price: number | string;
};

export default function SimilarProducts({
                                            categoryId,
                                            currentSlug,
                                        }: { categoryId?: number; currentSlug: string }) {
    const [items, setItems] = useState<ListProduct[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let on = true;
        (async () => {
            try {
                if (!categoryId) return;
                const res = await ProductsApi.list({
                    page: 1,
                    per_page: 12,          // небагато, вистачить щоб знайти 4 різні
                    sort: 'new',
                    category_id: categoryId,
                });

                const list: ListProduct[] = (res?.data ?? res ?? []).filter((p: any) => p?.slug !== currentSlug);
                if (on) setItems(list.slice(0, 4));
            } finally {
                if (on) setLoading(false);
            }
        })();
        return () => { on = false; };
    }, [categoryId, currentSlug]);

    if (!categoryId) return null;
    if (loading && items.length === 0) return null;
    if (items.length === 0) return null;

    return (
        <section className="mt-10">
            <h2 className="text-xl font-semibold mb-4">Схожі товари</h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {items.map(p => (
                    <Link
                        to={`/product/${p.slug}`}
                        key={p.id}
                        data-testid="similar-card"
                        className="group border rounded-xl overflow-hidden hover:shadow-sm transition"
                    >
                        <div className="aspect-[4/3] bg-gray-50 overflow-hidden">
                            {p.preview_url ? (
                                <img
                                    src={p.preview_url}
                                    alt={p.name}
                                    className="w-full h-full object-cover group-hover:scale-[1.02] transition"
                                />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center text-gray-400">
                                    no image
                                </div>
                            )}
                        </div>
                        <div className="p-3">
                            <div className="text-sm line-clamp-2">{p.name}</div>
                            <div className="mt-1 font-semibold">{formatPrice(p.price)}</div>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
}
