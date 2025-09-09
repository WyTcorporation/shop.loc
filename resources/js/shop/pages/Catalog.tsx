import { useEffect, useMemo, useState } from 'react';
import { fetchCategories, fetchProducts, Category, Product, Paginated } from '../api';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { Link } from 'react-router-dom';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { useQueryParam } from '../hooks/useQueryParam';
import { useQueryParamNumber } from '../hooks/useQueryParamNumber';
import { useQueryParamEnum } from '../hooks/useQueryParamEnum';
import { useDebounce } from '../hooks/useDebounce';
import { formatPrice } from '../ui/format';

type SortKey = 'price_asc' | 'price_desc' | 'new';

export default function Catalog() {
    const [cats, setCats] = useState<Category[]>([]);
    const [products, setProducts] = useState<Product[]>([]);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(true);

    // єдине поле пошуку: sync з URL
    const [q, setQ] = useQueryParam('q', '');
    const dq = useDebounce(q, 300);

    // category_id & sort у URL
    const [categoryIdParam, setCategoryParam] = useQueryParamNumber('category_id', undefined);
    const [sortParam, setSortParam] = useQueryParamEnum<SortKey>('sort', ['new','price_asc','price_desc'] as const, 'new');

    // локальні стейти підхоплюють значення з URL
    const [categoryId, setCategoryId] = useState<number | undefined>(categoryIdParam);
    const [sort, setSort] = useState<SortKey>(sortParam);

    // категорії (без глобального loading)
    useEffect(() => {
        setCategoryId(categoryIdParam);
        let ignore = false;
        (async () => {
            const c = await fetchCategories();
            if (!ignore) setCats(c);
        })();
        return () => { ignore = true; };
    }, [categoryIdParam]);

    // товари
    useEffect(() => {
        setSort(sortParam);
        let ignore = false;
        (async () => {
            setLoading(true);
            try {
                const res: Paginated<Product> = await fetchProducts({
                    page,
                    per_page: 12,
                    category_id: categoryId,
                    search: dq || undefined,
                    sort,
                });
                if (!ignore) {
                    setProducts(res.data);
                    setLastPage(res.last_page);
                }
            } finally {
                if (!ignore) setLoading(false);
            }
        })();
        return () => { ignore = true; };
    }, [page, categoryId, sort, dq,sortParam]);

    const canPrev = page > 1;
    const canNext = page < lastPage;

    return (
        <div className="mx-auto w-full max-w-7xl px-4 py-6">
            <header className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 className="text-2xl font-semibold tracking-tight">Каталог</h1>

                <div className="flex flex-col gap-3 sm:flex-row">
                    <div className="flex gap-2">
                        <Select
                            value={String(categoryId ?? 'all')}
                            onValueChange={(v) => {
                                setPage(1);
                                const next = v === 'all' ? undefined : Number(v);
                                setCategoryId(next);
                                setCategoryParam(next);
                            }}
                        >
                            <SelectTrigger className="w-48">
                                <SelectValue placeholder="Категорія" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Всі категорії</SelectItem>
                                {cats.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={sort}
                            onValueChange={(v) => {
                                const next = v as SortKey;
                                setPage(1);
                                setSort(next);
                                setSortParam(next);
                            }}
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="new">Новинки</SelectItem>
                                <SelectItem value="price_asc">Ціна ↑</SelectItem>
                                <SelectItem value="price_desc">Ціна ↓</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="flex gap-2">
                        <Input
                            value={q}
                            onChange={(e) => { setPage(1); setQ(e.target.value); }}
                            placeholder="Пошук товарів…"
                        />
                        <Button variant="secondary" onClick={() => { setQ(''); setPage(1); }}>
                            Скинути
                        </Button>
                    </div>
                </div>
            </header>

            {loading ? (
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                    {Array.from({ length: 8 }).map((_, i) => (
                        <Card key={i} className="p-3">
                            <Skeleton className="mb-3 h-40 w-full" />
                            <Skeleton className="mb-2 h-4 w-3/4" />
                            <Skeleton className="h-4 w-1/2" />
                        </Card>
                    ))}
                </div>
            ) : products.length === 0 ? (
                <div className="text-muted-foreground">Нічого не знайдено. Спробуйте змінити фільтри.</div>
            ) : (
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                    {products.map((p) => {
                        const primary =
                            p.images?.find((img) => img.is_primary) ??
                            (p.images && p.images.length > 0 ? p.images[0] : undefined);

                        return (
                            <Card key={p.id} className="overflow-hidden">
                                <Link to={`/product/${p.slug ?? p.id}`} className="block" data-testid="catalog-card">
                                    <div className="aspect-square bg-muted/40">
                                        {primary ? (
                                            <img
                                                src={primary.url}
                                                alt={primary.alt ?? p.name}
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
                                        <div className="mt-1 text-sm text-muted-foreground">{formatPrice(p.price)}</div>
                                    </div>
                                </Link>
                            </Card>
                        );
                    })}
                </div>
            )}

            <footer className="mt-8 flex items-center justify-center gap-3">
                <Button variant="outline" disabled={!canPrev} onClick={() => setPage((x) => Math.max(1, x - 1))}>
                    Назад
                </Button>
                <span className="text-sm text-muted-foreground">
          Сторінка {page} із {lastPage}
        </span>
                <Button variant="outline" disabled={!canNext} onClick={() => setPage((x) => x + 1)}>
                    Далі
                </Button>
            </footer>
        </div>
    );
}
