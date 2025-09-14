import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { OrdersApi } from '../api';
import { formatPrice } from '../ui/format';
import SeoHead from '../components/SeoHead';
import { GA } from '../ui/ga';
import PayOrder from "@/shop/components/PayOrder";

type OrderItem = {
    id: number;
    product_id: number;
    qty: number;
    price: number | string;
    product?: { name: string; slug: string; preview_url?: string | null };
};

type Order = {
    number: string;
    total: number | string;
    email: string;
    items: OrderItem[];
};

export default function OrderConfirmation() {
    const { number } = useParams<{ number: string }>();
    const [order, setOrder] = useState<Order | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let on = true;
        (async () => {
            try {
                const o = await OrdersApi.show(number!);
                if (on) setOrder(o);
                GA.purchase(o);
            } finally {
                if (on) setLoading(false);
            }
        })();
        return () => { on = false; };
    }, [number]);

    if (loading) return <div className="max-w-6xl mx-auto p-4">Завантаження…</div>;
    if (!order) return <div className="max-w-6xl mx-auto p-4">Замовлення не знайдено.</div>;

    const items = order.items ?? [];
    const itemsTotal = items.reduce((s, i) => s + Number(i.price || 0) * Number(i.qty || 0), 0);

    const isPaid = (order as any).payment_status === 'succeeded';

    return (
        <div className="max-w-6xl mx-auto p-4 space-y-6">
            <SeoHead title={`Замовлення ${order.number} — Shop`} robots="noindex,nofollow" canonical />
            <h1 className="text-2xl font-semibold" data-testid="order-confirmed">
                Дякуємо! Замовлення {order.number} оформлено
            </h1>
            <p className="text-gray-600">
                Підтвердження надіслано на {order.email}.
                {!isPaid && ' — Оплата очікується.'}
            </p>
            <div className="border rounded-xl overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50 text-left text-sm">
                    <tr>
                        <th className="p-3">Товар</th>
                        <th className="p-3 w-24">К-сть</th>
                        <th className="p-3 w-36">Ціна</th>
                        <th className="p-3 w-36">Сума</th>
                    </tr>
                    </thead>
                    <tbody>
                    {items.map(it => (
                        <tr key={it.id} className="border-t">
                            <td className="p-3">
                                <div className="flex items-center gap-3">
                                    {it.product?.preview_url && (
                                        <img src={it.product.preview_url} alt="" className="w-14 h-14 object-cover rounded-md border" />
                                    )}
                                    <div>
                                        <div className="font-medium">{it.product?.name ?? `#${it.product_id}`}</div>
                                        {it.product?.slug && (
                                            <Link to={`/product/${it.product.slug}`} className="text-xs text-gray-500 hover:underline">
                                                Переглянути товар
                                            </Link>
                                        )}
                                    </div>
                                </div>
                            </td>
                            <td className="p-3">{it.qty}</td>
                            <td className="p-3">{formatPrice(it.price)}</td>
                            <td className="p-3">{formatPrice(Number(it.price) * Number(it.qty))}</td>
                        </tr>
                    ))}
                    </tbody>
                    <tfoot className="border-t bg-gray-50">
                    <tr>
                        <td className="p-3 text-right font-medium" colSpan={3}>Разом за товари</td>
                        <td className="p-3 font-semibold">{formatPrice(itemsTotal)}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div className="flex gap-3">
                <Link to="/" className="px-4 py-2 rounded-lg border hover:bg-gray-50">Продовжити покупки</Link>
                <Link to="/cart" className="px-4 py-2 rounded-lg border hover:bg-gray-50">Відкрити кошик</Link>
            </div>

            {!isPaid && (
                <div className="border rounded-xl p-4">
                    <h2 className="font-semibold mb-2">Оплата замовлення</h2>
                    <p className="text-sm text-gray-600 mb-3">Безпечно через Stripe. Доступні картки та локальні методи (EU).</p>
                    <PayOrder number={order.number} onPaid={() => window.location.reload()} />
                </div>
            )}
        </div>
    );
}
