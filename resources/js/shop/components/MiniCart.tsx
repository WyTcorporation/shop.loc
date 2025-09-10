import React from "react";
import { Link, useLocation } from "react-router-dom";
import { Popover, PopoverContent, PopoverTrigger } from "../ui/popover";
import { Button } from "@/components/ui/button";
import useCart from "../useCart";

export default function MiniCart() {
    const { cart, total } = useCart();
    const location = useLocation();

    // закривати при зміні маршруту
    const [open, setOpen] = React.useState(false);
    React.useEffect(() => { setOpen(false); }, [location.pathname]);

    // безпечні змінні
    const count = cart?.items?.length ?? 0;
    const items = cart?.items ?? [];
    const sum = Number(total ?? 0).toFixed(2);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button variant="outline" data-testid="open-mini-cart">
                    🛒 {count}
                </Button>
            </PopoverTrigger>

            <PopoverContent data-testid="mini-cart" className="w-80" align="end">
                {count > 0 ? (
                    <div className="space-y-3">
                        <div className="max-h-64 overflow-auto divide-y">
                            {items.map(it => (
                                <div key={it.id} className="flex items-center gap-3 py-2">
                                    {it.image && (
                                        <img
                                            src={it.image}
                                            alt=""
                                            className="h-10 w-10 rounded border object-cover"
                                        />
                                    )}
                                    <div className="min-w-0 flex-1">
                                        <div className="truncate text-sm font-medium">
                                            {it.name ?? `#${it.product_id}`}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {it.qty} × {Number(it.price ?? 0).toFixed(2)}
                                        </div>
                                    </div>
                                    <div className="text-sm font-semibold">
                                        {(Number(it.qty ?? 0) * Number(it.price ?? 0)).toFixed(2)}
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="flex items-center justify-between border-t pt-2">
                            <div className="text-sm text-muted-foreground">Разом</div>
                            <div className="text-base font-semibold">{sum}</div>
                        </div>

                        <div className="flex gap-2">
                            <Link to="/cart" className="w-full">
                                <Button variant="outline" className="w-full" data-testid="mini-to-cart">
                                    Відкрити кошик
                                </Button>
                            </Link>
                            <Link to="/checkout" className="w-full">
                                <Button className="w-full" data-testid="mini-to-checkout">
                                    Оформити
                                </Button>
                            </Link>
                        </div>
                    </div>
                ) : (
                    // легкий “лоадер” поки cart ще не підвантажився або порожній
                    <div className="text-sm text-muted-foreground">
                        {cart ? "Кошик порожній" : "Завантаження…"}
                    </div>
                )}
            </PopoverContent>
        </Popover>
    );
}
