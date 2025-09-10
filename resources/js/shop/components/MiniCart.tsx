import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import useCart from '../useCart';
import { formatPrice } from '../ui/format';

export default function MiniCart() {
    const { cart, total } = useCart();
    const items = cart?.items ?? [];
    const count = items.reduce((s: number, i: any) => s + Number(i?.qty ?? 0), 0);

    const [open, setOpen] = React.useState(false);
    const location = useLocation();

    const btnRef = React.useRef<HTMLButtonElement | null>(null);
    const panelRef = React.useRef<HTMLDivElement | null>(null);

    // закриваємо при зміні маршруту
    React.useEffect(() => setOpen(false), [location.pathname, location.search]);

    // закриття по кліку поза поповером
    React.useEffect(() => {
        function onDocClick(e: MouseEvent) {
            if (!open) return;
            const t = e.target as Node;
            if (panelRef.current?.contains(t)) return;
            if (btnRef.current?.contains(t)) return;
            setOpen(false);
        }
        function onKey(e: KeyboardEvent) {
            if (e.key === 'Escape') setOpen(false);
        }
        document.addEventListener('mousedown', onDocClick);
        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('mousedown', onDocClick);
            document.removeEventListener('keydown', onKey);
        };
    }, [open]);

    return (
        <div className="relative">
            <Button
                ref={btnRef}
                variant="outline"
                size="sm"
                data-testid="mini-cart-button"
                className="relative"
                title="Відкрити міні-кошик"
                onClick={() => setOpen(v => !v)}
            >
                Кошик
                <span className="ml-2 inline-flex min-w-6 items-center justify-center rounded-full border px-1 text-xs">
          {count}
        </span>
            </Button>

            <div
                ref={panelRef}
                data-testid="mini-cart-popover"
                role="dialog"
                aria-label="Міні-кошик"
                className={`absolute right-0 top-full mt-2 w-[360px] overflow-hidden rounded-lg border bg-white shadow-xl ${open ? '' : 'hidden'}`}
            >
                <div className="max-h-80 overflow-auto">
                    {items.length === 0 ? (
                        <div className="p-4 text-sm text-muted-foreground">Кошик порожній</div>
                    ) : (
                        <ul className="divide-y">
                            {items.map((it: any) => {
                                const p = it.product ?? {};
                                const img =
                                    p.preview_url ||
                                    p.images?.find((x: any) => x.is_primary)?.url ||
                                    p.images?.[0]?.url ||
                                    null;

                                return (
                                    <li key={it.id} className="flex gap-3 p-3" data-testid="mini-cart-item">
                                        <div className="h-14 w-14 overflow-hidden rounded border bg-muted/40">
                                            {img ? (
                                                <img src={img} alt="" className="h-full w-full object-cover" />
                                            ) : (
                                                <div className="flex h-full w-full items-center justify-center text-[10px] text-muted-foreground">
                                                    без фото
                                                </div>
                                            )}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate text-sm font-medium">
                                                {p.name ?? `#${it.product_id}`}
                                            </div>
                                            <div className="mt-0.5 text-xs text-muted-foreground">
                                                {it.qty} × {formatPrice(it.price)} ={' '}
                                                <span className="font-medium">
                          {formatPrice(Number(it.qty) * Number(it.price))}
                        </span>
                                            </div>
                                        </div>
                                        {p.slug ? (
                                            <Link
                                                to={`/product/${p.slug}`}
                                                className="shrink-0 text-xs text-muted-foreground hover:underline"
                                                onClick={() => setOpen(false)}
                                            >
                                                Відкрити
                                            </Link>
                                        ) : (
                                            <span className="shrink-0 text-xs text-muted-foreground"> </span>
                                        )}
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>

                <div className="flex items-center justify-between gap-3 border-t p-3">
                    <div className="text-sm">
                        Разом:{' '}
                        <span className="font-semibold">{formatPrice(total ?? 0)}</span>
                    </div>
                    <div className="flex gap-2">
                        <Link
                            to="/cart"
                            data-testid="mini-cart-open-cart"
                            className="rounded-md border px-3 py-1.5 text-sm hover:bg-gray-50"
                            onClick={() => setOpen(false)}
                        >
                            Кошик
                        </Link>
                        <Link
                            to="/checkout"
                            data-testid="mini-cart-checkout"
                            className="rounded-md bg-black px-3 py-1.5 text-sm text-white hover:opacity-90"
                            onClick={() => setOpen(false)}
                        >
                            Оформити
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
