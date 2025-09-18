import { useEffect, useMemo, useState } from 'react';
import {
    fetchCategories,
    fetchProducts,
    type Category,
    type Product,
    type PaginatedWithFacets,
    type Facets,
} from '../api';
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
import WishlistButton from '../components/WishlistButton';
import { useDocumentTitle } from '../hooks/useDocumentTitle';
import SeoHead from '../components/SeoHead';
import JsonLd from '../components/JsonLd';
import { useHreflangs } from '../hooks/useHreflangs';
import { GA } from '../ui/ga';
import useCart from '../useCart';
import { Loader2 } from 'lucide-react';

type SortKey = 'price_asc' | 'price_desc' | 'new';

const normalizeFacetValue = (value: string) => value.trim().toLowerCase();

export default function Catalog() {
    const [cats, setCats] = useState<Category[]>([]);
    const [products, setProducts] = useState<Product[]>([]);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [addingId, setAddingId] = useState<number | null>(null);
    const { add } = useCart();

    // пошук у URL
    const [q, setQ] = useQueryParam('q', '');
    const dq = useDebounce(q, 300);

    // category_id & sort у URL
    const [categoryIdParam, setCategoryParam] = useQueryParamNumber('category_id', undefined);
    const [sortParam, setSortParam] = useQueryParamEnum<SortKey>('sort', ['new', 'price_asc', 'price_desc'] as const, 'new');

    // локальні значення
    const [categoryId, setCategoryId] = useState<number | undefined>(categoryIdParam);
    const [sort, setSort] = useState<SortKey>(sortParam);

    // атрибути у URL (і СЕТЕРИ!)
    const [colorsParam, setColorsParam] = useQueryParam('color', ''); // "?color=red,blue"
    const [sizesParam,  setSizesParam]  = useQueryParam('size',  ''); // "?size=M,L"
    const selectedColorParamValues = useMemo(
        () => colorsParam ? colorsParam.split(',').filter(Boolean) : [],
        [colorsParam],
    );
    const selectedColorKeys = useMemo(() => {
        const seen = new Set<string>();
        const keys: string[] = [];
        selectedColorParamValues.forEach((value) => {
            const normalized = normalizeFacetValue(value);
            if (!normalized || seen.has(normalized)) return;
            seen.add(normalized);
            keys.push(normalized);
        });
        return keys;
    }, [selectedColorParamValues]);
    const selectedColorKeySet = useMemo(() => new Set(selectedColorKeys), [selectedColorKeys]);
    const colorRawValuesByKey = useMemo(() => {
        const map = new Map<string, string[]>();
        selectedColorParamValues.forEach((value) => {
            const normalized = normalizeFacetValue(value);
            if (!normalized) return;
            const existing = map.get(normalized);
            if (existing) existing.push(value);
            else map.set(normalized, [value]);
        });
        return map;
    }, [selectedColorParamValues]);
    const selectedSizes  = useMemo(() => sizesParam  ? sizesParam.split(',').filter(Boolean)  : [], [sizesParam]);

    // price range у URL
    const [minPriceParam, setMinPriceParam] = useQueryParamNumber('min_price', undefined);
    const [maxPriceParam, setMaxPriceParam] = useQueryParamNumber('max_price', undefined);
    const [minPrice, setMinPrice] = useState<number | undefined>(minPriceParam);
    const [maxPrice, setMaxPrice] = useState<number | undefined>(maxPriceParam);
    useEffect(() => { setMinPrice(minPriceParam); }, [minPriceParam]);
    useEffect(() => { setMaxPrice(maxPriceParam); }, [maxPriceParam]);

    // фасети
    const [facets, setFacets] = useState<Facets | null>(null);

    function toggleListParam(value: string, list: string[], setParam: (v?: string)=>void) {
        const has = list.includes(value);
        const next = has ? list.filter(v => v !== value) : [...list, value];
        setParam(next.length ? next.join(',') : undefined);
        setPage(1);
    }

    function toggleColorFacet(key: string) {
        const canonical = colorFacetMap.get(key)?.value ?? colorRawValuesByKey.get(key)?.[0] ?? key;
        const withoutKey = selectedColorParamValues.filter(
            (value) => normalizeFacetValue(value) !== key,
        );
        const has = selectedColorKeySet.has(key);
        const nextValues = has
            ? withoutKey
            : (canonical ? [...withoutKey, canonical] : withoutKey);
        setColorsParam(nextValues.length ? nextValues.join(',') : undefined);
        setPage(1);
    }

    // категорії
    useEffect(() => {
        setCategoryId(categoryIdParam);
        let ignore = false;
        (async () => {
            const c = await fetchCategories();
            if (!ignore) setCats(c);
        })();
        return () => { ignore = true; };
    }, [categoryIdParam]);

    // товари + фасети разом (щоб уникнути гонок)
    useEffect(() => {
        setSort(sortParam);
        let ignore = false;
        (async () => {
            setLoading(true);
            try {
                const res: PaginatedWithFacets<Product> = await fetchProducts({
                    page,
                    per_page: 12,
                    category_id: categoryId,
                    search: dq || undefined,
                    sort,
                    color: selectedColorApiValues,
                    size: selectedSizes,
                    min_price: minPriceParam,
                    max_price: maxPriceParam,
                    with_facets: 1,
                });
                if (!ignore) {
                    setProducts(res.data);
                    setLastPage(res.last_page);
                    setFacets(res.facets ?? {});
                    const listName = categoryId
                        ? `Каталог — ${cats.find(c => c.id === categoryId)?.name ?? `#${categoryId}`}`
                        : 'Каталог';
                    GA.view_item_list(res.data, listName);
                }
            } finally {
                if (!ignore) setLoading(false);
            }
        })();
        return () => { ignore = true; };
    }, [
        page, categoryId, sort, dq, sortParam,
        colorsParam, sizesParam, minPriceParam, maxPriceParam
    ]);

    const canPrev = page > 1;
    const canNext = page < lastPage;

    // безпечне читання фасетів
    const catById = new Map(cats.map(c => [String(c.id), c]));
    const catCounts   = facets?.['category_id'] ?? {};
    const colorCounts = facets?.['attrs.color'] ?? {};
    const sizeCounts  = facets?.['attrs.size'] ?? {};
    const categoryFacetEntries = useMemo(
        () => Object.entries(catCounts).filter(([id]) => typeof id === 'string' && /^\d+$/.test(id)),
        [catCounts],
    );
    const colorFacetMap = useMemo(() => {
        const map = new Map<string, { normalized: string; label: string; value: string; count: number }>();
        Object.entries(colorCounts).forEach(([rawValue, rawCount]) => {
            if (!rawValue || rawValue === 'null') return;
            const normalized = normalizeFacetValue(rawValue);
            if (!normalized) return;
            const trimmed = rawValue.trim();
            if (!trimmed) return;
            const display = trimmed;
            const numericCount = typeof rawCount === 'number' ? rawCount : Number(rawCount) || 0;
            const existing = map.get(normalized);
            if (existing) {
                existing.count += numericCount;
            } else {
                map.set(normalized, {
                    normalized,
                    label: display,
                    value: display,
                    count: numericCount,
                });
            }
        });
        return map;
    }, [colorCounts]);
    const colorFacetList = useMemo(() => Array.from(colorFacetMap.values()), [colorFacetMap]);
    const colorDisplayByKey = useMemo(() => {
        const map = new Map<string, string>();
        colorFacetList.forEach(({ normalized, label }) => {
            map.set(normalized, label);
        });
        return map;
    }, [colorFacetList]);
    const selectedColorApiValues = useMemo(
        () => selectedColorKeys.map((key) => colorFacetMap.get(key)?.value ?? colorRawValuesByKey.get(key)?.[0] ?? key),
        [selectedColorKeys, colorFacetMap, colorRawValuesByKey],
    );

    function clearAll() {
        setQ('');
        setCategoryId(undefined);
        setCategoryParam(undefined);
        setColorsParam(undefined);
        setSizesParam(undefined);
        setMinPrice(undefined);
        setMaxPrice(undefined);
        setMinPriceParam(undefined);
        setMaxPriceParam(undefined);
        setPage(1);
    }

    const activeChips = [
        ...(categoryId ? [{
            key: 'cat',
            label: cats.find(c => c.id === categoryId)?.name ?? `#${categoryId}`,
            onClear: () => { setCategoryId(undefined); setCategoryParam(undefined); setPage(1); },
        }] : []),
        ...selectedColorKeys.map((key) => {
            const label = colorDisplayByKey.get(key) ?? colorRawValuesByKey.get(key)?.[0] ?? key;
            return {
                key: `color:${key}`,
                label: `Колір: ${label}`,
                onClear: () => toggleColorFacet(key),
            };
        }),
        ...selectedSizes.map((s) => ({
            key: `size:${s}`,
            label: `Розмір: ${s}`,
            onClear: () => toggleListParam(s, selectedSizes, setSizesParam),
        })),
        ...(minPriceParam != null ? [{
            key: 'min',
            label: `Від: ${minPriceParam}`,
            onClear: () => { setMinPrice(undefined); setMinPriceParam(undefined); setPage(1); },
        }] : []),
        ...(maxPriceParam != null ? [{
            key: 'max',
            label: `До: ${maxPriceParam}`,
            onClear: () => { setMaxPrice(undefined); setMaxPriceParam(undefined); setPage(1); },
        }] : []),
    ];

    const currentCatName = categoryId ? (catById.get(String(categoryId))?.name ?? `#${categoryId}`) : null;
    useDocumentTitle(`Каталог${currentCatName ? ` — ${currentCatName}` : ''}${dq ? ` — ${dq}` : ''}`);

    // ---------- SEO (OG/Twitter + prev/next + breadcrumbs) ----------
    const activeCatName = categoryId ? cats.find(c => c.id === categoryId)?.name : undefined;

    const titleParts: string[] = ['Каталог'];
    if (activeCatName) titleParts.push(activeCatName);
    if (q) titleParts.push(`пошук “${q}”`);
    const pageTitle = `${titleParts.join(' — ')} — Shop`;

    const pageDescription = [
        'Каталог інтернет-магазину. Фільтри: категорія, колір, розмір, ціна.',
        activeCatName ? `Категорія: ${activeCatName}.` : '',
        q ? `Пошук: ${q}.` : '',
    ].filter(Boolean).join(' ');

    function buildUrlWith(kv: Record<string, string | number | undefined>) {
        const href = typeof window !== 'undefined' ? window.location.href : '';
        const u = new URL(href || 'http://localhost');
        Object.entries(kv).forEach(([k,v]) => {
            if (v === undefined || v === '' || v === null) u.searchParams.delete(k);
            else u.searchParams.set(k, String(v));
        });
        return u.toString();
    }

    const prevUrl = canPrev ? buildUrlWith({ page: page - 1 }) : undefined;
    const nextUrl = canNext ? buildUrlWith({ page: page + 1 }) : undefined;

    const catalogUrl = typeof window !== 'undefined'
        ? `${window.location.origin}/`
        : undefined;
    const breadcrumbLd = {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Головна', item: catalogUrl },
            { '@type': 'ListItem', position: 2, name: 'Каталог', item: typeof window !== 'undefined' ? window.location.href : undefined }
        ]
    };
    // ----------------------------------------------------------------

    const hasFilters =
        !!q ||
        !!categoryId ||
        (selectedColorKeys.length > 0) ||
        (selectedSizes.length > 0) ||
        (minPriceParam != null) ||
        (maxPriceParam != null) ||
        (sort !== 'new');

    const robotsMeta = hasFilters ? 'noindex,follow' : undefined;

    const hreflangs = useHreflangs('uk');

    return (
        <div className="mx-auto w-full max-w-7xl px-4 py-6">
            <SeoHead
                title={pageTitle}
                description={pageDescription}
                canonical
                prevUrl={prevUrl}
                nextUrl={nextUrl}
                robots={robotsMeta}
                hreflangs={hreflangs}
            />
            <JsonLd data={breadcrumbLd} />

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

                    <div className="flex gap-2 items-center">
                        <Input
                            value={q}
                            onChange={(e) => { setPage(1); setQ(e.target.value); }}
                            placeholder="Пошук товарів…"
                        />

                        <Input
                            data-testid="price-min"
                            type="number"
                            placeholder="Ціна від"
                            value={minPrice ?? ''}
                            onChange={(e) => setMinPrice(e.target.value === '' ? undefined : Number(e.target.value))}
                            className="w-48"
                        />
                        <Input
                            data-testid="price-max"
                            type="number"
                            placeholder="до"
                            value={maxPrice ?? ''}
                            onChange={(e) => setMaxPrice(e.target.value === '' ? undefined : Number(e.target.value))}
                            className="w-32"
                        />
                        <Button
                            data-testid="apply-price"
                            onClick={() => {
                                setMinPriceParam(minPrice);
                                setMaxPriceParam(maxPrice);
                                setPage(1);
                            }}
                        >
                            Застосувати
                        </Button>

                        <Button
                            variant="outline"
                            data-testid="clear-filters"
                            onClick={clearAll}
                        >
                            Скинути все
                        </Button>
                    </div>
                </div>
            </header>

            {activeChips.length > 0 && (
                <div className="mb-4 flex flex-wrap items-center gap-2" data-testid="active-filters">
                    {activeChips.map(ch => (
                        <button
                            key={ch.key}
                            type="button"
                            onClick={ch.onClear}
                            className="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs hover:bg-gray-50"
                            title="Скинути цей фільтр"
                        >
                            {ch.label}
                            <span aria-hidden>×</span>
                        </button>
                    ))}
                    <Button variant="ghost" size="sm" onClick={clearAll}>
                        Скинути все
                    </Button>
                </div>
            )}

            {/* ФАСЕТИ (debug) */}
            <div
                data-testid="facets-panel"
                className="mb-6 grid gap-3 rounded-xl border p-3 md:grid-cols-3"
            >
                {/* Категорії */}
                <div>
                    <div className="mb-2 text-sm font-medium">Категорії</div>
                    <div className="flex flex-wrap gap-2">
                        {categoryFacetEntries.map(([id, cnt]) => {
                            const c = catById.get(String(id));
                            const active = Number(categoryId) === Number(id);
                            return (
                                <button
                                    key={id}
                                    type="button"
                                    data-testid={`facet-cat-${id}`}
                                    onClick={() => {
                                        const next = active ? undefined : Number(id);
                                        setCategoryId(next);
                                        setCategoryParam(next);
                                        setPage(1);
                                    }}
                                    className={`inline-flex items-center gap-1 rounded-full border px-2 py-1 text-xs ${active ? 'bg-black text-white' : ''}`}
                                    title={`category_id=${id}`}
                                >
                                    {c?.name ?? `#${id}`} <span className="opacity-70">({cnt})</span>
                                </button>
                            );
                        })}
                        {categoryFacetEntries.length === 0 && (
                            <span className="text-xs text-muted-foreground">нема даних</span>
                        )}
                    </div>
                </div>

                {/* Колір */}
                <div>
                    <div className="mb-2 text-sm font-medium">Колір</div>
                    <div className="flex flex-wrap gap-2">
                        {colorFacetList.map(({ normalized, label, value, count }) => {
                            const active = selectedColorKeySet.has(normalized);
                            return (
                                <button
                                    key={normalized}
                                    type="button"
                                    data-testid={`facet-color-${normalized}`}
                                    onClick={() => toggleColorFacet(normalized)}
                                    className={`inline-flex items-center gap-1 rounded-full border px-2 py-1 text-xs ${active ? 'bg-black text-white' : ''}`}
                                    title={`attrs.color=${value}`}
                                >
                                    {label} <span className="opacity-70">({count})</span>
                                </button>
                            );
                        })}
                        {colorFacetList.length === 0 && (
                            <span className="text-xs text-muted-foreground">нема даних</span>
                        )}
                    </div>
                </div>

                {/* Розмір */}
                <div>
                    <div className="mb-2 text-sm font-medium">Розмір</div>
                    <div className="flex flex-wrap gap-2">
                        {Object.entries(sizeCounts).filter(([v]) => v && v !== 'null').map(([v, cnt]) => {
                            const active = selectedSizes.includes(v);
                            return (
                                <button
                                    key={v}
                                    type="button"
                                    data-testid={`facet-size-${v}`}
                                    onClick={() => toggleListParam(v, selectedSizes, setSizesParam)}
                                    className={`inline-flex items-center gap-1 rounded-full border px-2 py-1 text-xs ${active ? 'bg-black text-white' : ''}`}
                                    title={`attrs.size=${v}`}
                                >
                                    {v} <span className="opacity-70">({cnt})</span>
                                </button>
                            );
                        })}
                        {Object.keys(sizeCounts).length === 0 && (
                            <span className="text-xs text-muted-foreground">нема даних</span>
                        )}
                    </div>
                </div>
            </div>

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
                        const inStock = Number(p.stock ?? 0) > 0;

                        return (
                            <Card key={p.id} className="flex h-full flex-col overflow-hidden">
                                <Link
                                    to={`/product/${p.slug ?? p.id}`}
                                    className="flex flex-1 flex-col"
                                    data-testid="catalog-card"
                                >
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
                                        {!inStock && (
                                            <div className="mt-1 text-xs text-red-600">Немає в наявності</div>
                                        )}
                                    </div>
                                </Link>
                                <div className="flex flex-wrap items-center gap-2 border-t px-3 py-3">
                                    <WishlistButton product={p} className="flex-1 justify-center" />
                                    <Button
                                        type="button"
                                        className="flex-1"
                                        disabled={!inStock || addingId === p.id}
                                        onClick={async () => {
                                            if (!inStock) return;
                                            setAddingId(p.id);
                                            try {
                                                await add(p.id);
                                            } finally {
                                                setAddingId((current) => (current === p.id ? null : current));
                                            }
                                        }}
                                    >
                                        {inStock ? (
                                            addingId === p.id ? (
                                                <>
                                                    <Loader2 className="h-4 w-4 animate-spin" aria-hidden="true" />
                                                    <span>Купуємо…</span>
                                                </>
                                            ) : (
                                                'Купити'
                                            )
                                        ) : (
                                            <>
                                                <span>Немає в наявності</span>
                                            </>
                                        )}
                                    </Button>
                                </div>
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
