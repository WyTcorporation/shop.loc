import { useEffect, useState } from 'react';
import {useParams, Link, useSearchParams} from 'react-router-dom';
import { OrdersApi, refreshOrderStatus, type Product } from '../api';
import { formatPrice } from '../ui/format';
import SeoHead from '../components/SeoHead';
import { GA } from '../ui/ga';
import PayOrder from "@/shop/components/PayOrder";
import OrderChat from '../components/OrderChat';
import { Button } from '@/components/ui/button';

type OrderItem = {
    id: number;
    product_id: number;
    qty: number;
    price: number | string;
    product?: Product | null;
};

type Shipment = {
    tracking_number?: string | null;
    status?: string | null;
};

type Order = {
    id: number;
    number: string;
    total: number | string;
    email: string;
    status?: string | null;
    payment_status?: string | null;
    items: OrderItem[];
    shipment?: Shipment | null;
    shipping_address?: {
        name?: string | null;
        city?: string | null;
        addr?: string | null;
        postal_code?: string | null;
        phone?: string | null;
    };
    billing_address?: {
        name?: string | null;
        company?: string | null;
        tax_id?: string | null;
        city?: string | null;
        addr?: string | null;
        postal_code?: string | null;
    } | null;
    currency?: string | null;
};

export default function OrderConfirmation() {
    const { number } = useParams<{ number: string }>();
    const [order, setOrder] = useState<Order | null>(null);
    const [loading, setLoading] = useState(true);
    const [chatOpen, setChatOpen] = useState(false);

    const [sp] = useSearchParams();
    const payment_intent = sp.get('payment_intent') ?? undefined;
    const redirect_status = sp.get('redirect_status') ?? undefined;

    useEffect(() => {
        let on = true;
        (async () => {
            try {
                // 1) якщо є дані від Stripe у query — оновимо статус на бекенді
                if (number && (payment_intent || redirect_status)) {
                    try {
                        await refreshOrderStatus(number, payment_intent);
                    } catch {}
                }
                // 2) забираємо свіже замовлення
                if (number) {
                    const o = await OrdersApi.show(number);
                    if (!on) return;
                    setOrder(o);
                    GA.purchase(o);
                }
            } finally {
                if (on) setLoading(false);
            }
        })();
        return () => { on = false; };
    }, [number, payment_intent, redirect_status]);

    if (loading) return <div className="max-w-6xl mx-auto p-4">Завантаження…</div>;
    if (!order) return <div className="max-w-6xl mx-auto p-4">Замовлення не знайдено.</div>;

    const items = order.items ?? [];
    const itemsTotal = items.reduce((s, i) => s + Number(i.price || 0) * Number(i.qty || 0), 0);

    const isPaid =
        (order.payment_status ?? '').toLowerCase() === 'succeeded' ||
        (order.status ?? '').toLowerCase() === 'paid';

    const trackingNumber = order.shipment?.tracking_number ?? null;
    const rawShipmentStatus = order.shipment?.status ? String(order.shipment.status) : null;
    const shipmentStatus = rawShipmentStatus
        ? rawShipmentStatus
              .replace(/_/g, ' ')
              .replace(/^[a-zа-яіїєґ]/i, (c) => c.toUpperCase())
        : null;
    const currency = order.currency ?? 'EUR';
    const billingAddress = order.billing_address ?? null;
    const hasBillingDetails = billingAddress
        ? Object.values(billingAddress).some((value) => {
              if (value == null) return false;
              if (typeof value === 'string') return value.trim().length > 0;
              return Boolean(value);
          })
        : false;

    return (
        <div className="max-w-6xl mx-auto p-4 space-y-6">
            <SeoHead title={`Замовлення ${order.number} — Shop`} robots="noindex,nofollow" canonical />
            <h1 className="text-2xl font-semibold" data-testid="order-confirmed">
                Дякуємо! Замовлення {order.number} оформлено
            </h1>
            <div className="flex flex-wrap items-center gap-3">
                <p className="text-gray-600">
                    Підтвердження надіслано на {order.email}.
                    {!isPaid && ' — Оплата очікується.'}
                </p>
                {order.id && (
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => setChatOpen((open) => !open)}
                    >
                        {chatOpen ? 'Сховати чат' : 'Написати продавцю'}
                    </Button>
                )}
            </div>
            {chatOpen && order.id && (
                <OrderChat orderId={order.id} orderNumber={order.number} />
            )}
            <div className="rounded-xl border border-gray-200 p-4 space-y-2">
                <h2 className="text-lg font-semibold">Доставка та відстеження</h2>
                <p className="text-sm text-gray-600">
                    Номер відстеження:{' '}
                    <span className="font-medium text-gray-900">
                        {trackingNumber || 'Очікується'}
                    </span>
                    {shipmentStatus && (
                        <span className="ml-2 text-xs uppercase tracking-wide text-gray-500">
                            {shipmentStatus}
                        </span>
                    )}
                </p>
                {order.shipping_address && (
                    <div className="text-sm text-gray-600">
                        {order.shipping_address.name && <div>{order.shipping_address.name}</div>}
                        {order.shipping_address.city && <div>{order.shipping_address.city}</div>}
                        {order.shipping_address.addr && <div>{order.shipping_address.addr}</div>}
                        {order.shipping_address.postal_code && <div>{order.shipping_address.postal_code}</div>}
                        {order.shipping_address.phone && <div>{order.shipping_address.phone}</div>}
                    </div>
                )}
            </div>
            {hasBillingDetails && billingAddress && (
                <div className="rounded-xl border border-gray-200 p-4 space-y-2">
                    <h2 className="text-lg font-semibold">Платіжні дані</h2>
                    <div className="text-sm text-gray-600">
                        {billingAddress.company && (
                            <div className="font-medium text-gray-800">{billingAddress.company}</div>
                        )}
                        {billingAddress.name && <div>{billingAddress.name}</div>}
                        {billingAddress.tax_id && (
                            <div className="text-xs text-gray-500">Податковий номер: {billingAddress.tax_id}</div>
                        )}
                        {billingAddress.city && <div>{billingAddress.city}</div>}
                        {billingAddress.addr && <div>{billingAddress.addr}</div>}
                        {billingAddress.postal_code && <div>{billingAddress.postal_code}</div>}
                    </div>
                </div>
            )}
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
                                        {it.product?.vendor && (
                                            <div className="mt-1 text-xs text-gray-500">
                                                Продавець: {it.product.vendor.name ?? `#${it.product.vendor.id}`}{' '}
                                                {it.product.vendor.id && (
                                                    <Link
                                                        to={`/seller/${it.product.vendor.id}`}
                                                        className="text-blue-600 hover:underline"
                                                    >
                                                        Написати продавцю
                                                    </Link>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </td>
                            <td className="p-3">{it.qty}</td>
                            <td className="p-3">{formatPrice(it.price, currency)}</td>
                            <td className="p-3">{formatPrice(Number(it.price) * Number(it.qty), currency)}</td>
                        </tr>
                    ))}
                    </tbody>
                    <tfoot className="border-t bg-gray-50">
                    <tr>
                        <td className="p-3 text-right font-medium" colSpan={3}>Разом за товари</td>
                        <td className="p-3 font-semibold">{formatPrice(itemsTotal, currency)}</td>
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
