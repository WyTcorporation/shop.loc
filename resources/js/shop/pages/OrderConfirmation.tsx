// resources/js/shop/pages/OrderConfirmation.tsx
import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { OrdersApi } from '../api';

const money = (v: unknown) =>
    new Intl.NumberFormat('uk-UA', { style: 'currency', currency: 'EUR' })
        .format(Number(v ?? 0));

export default function OrderConfirmationPage() {
    const { number } = useParams<{ number: string }>();
    const [order, setOrder] = useState<any | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!number) return;
        OrdersApi.show(number)
            .then((o) => {
                setOrder(o);
                document.title = `Замовлення ${o.number}`;
            })
            .catch((e) => setError(e?.response?.data?.message ?? 'Помилка завантаження'));
    }, [number]);

    if (error) return <div className="p-6 text-red-600">{error}</div>;
    if (!order) return <div className="p-6">Завантаження…</div>;

    return (
        <div className="max-w-3xl mx-auto p-6 space-y-6">
            <h1 className="text-2xl font-bold">Дякуємо! Замовлення {order.number} оформлено</h1>
            <div className="text-muted-foreground">
                Ми надіслали підтвердження на <span className="font-medium">{order.email}</span>.
            </div>

            <div className="border rounded-xl p-4 space-y-4">
                {order.items?.map((it: any) => {
                    const qty = Number(it.qty ?? it.quantity ?? 0) || 0;
                    const unitPrice =
                        Number(it.price ?? it.product?.price ?? (Number(it.subtotal ?? 0) / Math.max(1, qty)));
                    const lineTotal = Number(it.subtotal ?? unitPrice * qty);

                    const img =
                        it.preview_url
                        ?? it.product?.preview_url
                        ?? it.product?.images?.find?.((im: any) => im.is_primary)?.url
                        ?? it.product?.images?.[0]?.url;

                    return (
                        <div key={it.id} className="flex items-center gap-3">
                            <div className="w-16 h-16 rounded overflow-hidden bg-muted flex-shrink-0">
                                {img ? <img src={img} alt={it.name ?? it.product?.name ?? 'product'} className="w-full h-full object-cover" /> : null}
                            </div>
                            <div className="flex-1">
                                <div className="font-medium">{it.name ?? it.product?.name ?? 'Товар'}</div>
                                <div className="text-sm text-muted-foreground">× {qty}</div>
                            </div>
                            <div className="text-right">
                                <div className="text-sm">{money(unitPrice)} / од.</div>
                                <div className="font-semibold">{money(lineTotal)}</div>
                            </div>
                        </div>
                    );
                })}

                <div className="flex justify-between border-t pt-3 text-lg font-semibold">
                    <div>Разом</div>
                    <div>{money(order.total)}</div>
                </div>
            </div>

            <div className="flex gap-3">
                <Link to="/" className="underline">Повернутися до каталогу</Link>
            </div>
        </div>
    );
}
