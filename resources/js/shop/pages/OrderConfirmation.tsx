import { useCallback, useEffect, useState } from 'react';
import {useParams, Link, useSearchParams} from 'react-router-dom';
import { OrdersApi, refreshOrderStatus, type Product } from '../api';
import { formatPhoneForDisplay } from '../lib/phone';
import { formatPrice } from '../ui/format';
import SeoHead from '../components/SeoHead';
import { GA } from '../ui/ga';
import PayOrder from "@/shop/components/PayOrder";
import OrderChat from '../components/OrderChat';
import { Button } from '@/components/ui/button';
import { useLocale } from '../i18n/LocaleProvider';

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
    subtotal?: number | string | null;
    discount_total?: number | string | null;
    coupon_code?: string | null;
    coupon_discount?: number | string | null;
    loyalty_points_used?: number | string | null;
    loyalty_points_value?: number | string | null;
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
        phone?: string | null;
    } | null;
    currency?: string | null;
};

export default function OrderConfirmation() {
    const { number } = useParams<{ number: string }>();
    const [order, setOrder] = useState<Order | null>(null);
    const [loading, setLoading] = useState(true);
    const [chatOpen, setChatOpen] = useState(false);
    const { t } = useLocale();

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

    const handlePaid = useCallback(
        async (_status?: string, paymentIntentId?: string) => {
            if (!number) return;
            try {
                await refreshOrderStatus(number, paymentIntentId);
            } catch (error) {
                console.error('Failed to refresh order status after payment', error);
                return;
            }

            try {
                const updatedOrder = await OrdersApi.show(number);
                setOrder(updatedOrder);
            } catch (error) {
                console.error('Failed to fetch updated order after payment', error);
            }
        },
        [number]
    );

    if (loading) return <div className="max-w-6xl mx-auto p-4">{t('order.confirmation.loading')}</div>;
    if (!order) return <div className="max-w-6xl mx-auto p-4">{t('order.confirmation.notFound')}</div>;

    const items = order.items ?? [];
    const toNumber = (value: number | string | null | undefined) => {
        const parsed = Number(value ?? 0);
        return Number.isFinite(parsed) ? parsed : 0;
    };
    const itemsTotal = items.reduce((s, i) => s + Number(i.price || 0) * Number(i.qty || 0), 0);
    const subtotal = order.subtotal != null ? toNumber(order.subtotal) : itemsTotal;
    const couponCode = (order.coupon_code ?? '').trim() || null;
    const couponDiscount = toNumber(order.coupon_discount);
    const loyaltyPointsUsed = toNumber(order.loyalty_points_used);
    const loyaltyPointsValue = toNumber(order.loyalty_points_value);
    const discountTotal = Math.max(
        0,
        order.discount_total != null ? toNumber(order.discount_total) : couponDiscount + loyaltyPointsValue,
    );
    const hasCouponCode = Boolean(couponCode);
    const hasDiscount = discountTotal > 0;
    const hasLoyaltyPoints = loyaltyPointsUsed > 0;
    const loyaltyPointsDisplay = hasLoyaltyPoints
        ? new Intl.NumberFormat('uk-UA').format(loyaltyPointsUsed)
        : '';

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
            <SeoHead
                title={t('order.confirmation.seoTitle', { number: order.number, brand: t('common.brand') })}
                robots="noindex,nofollow"
                canonical
            />
            <h1 className="text-2xl font-semibold" data-testid="order-confirmed">
                {t('order.confirmation.title', { number: order.number })}
            </h1>
            <div className="flex flex-wrap items-center gap-3">
                <p className="text-gray-600">
                    {t('order.confirmation.confirmationNotice', { email: order.email })}
                    {!isPaid && <span> {t('order.confirmation.paymentPending')}</span>}
                </p>
                {order.id && (
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => setChatOpen((open) => !open)}
                    >
                        {chatOpen ? t('order.confirmation.chat.close') : t('order.confirmation.chat.open')}
                    </Button>
                )}
            </div>
            {chatOpen && order.id && (
                <OrderChat orderId={order.id} orderNumber={order.number} />
            )}
            <div className="rounded-xl border border-gray-200 p-4 space-y-2">
                <h2 className="text-lg font-semibold">{t('order.confirmation.shipping.title')}</h2>
                <p className="text-sm text-gray-600">
                    {t('order.confirmation.shipping.trackingNumber')}{' '}
                    <span className="font-medium text-gray-900">
                        {trackingNumber || t('order.confirmation.shipping.pending')}
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
                        {order.shipping_address.phone && (
                            <div>{formatPhoneForDisplay(order.shipping_address.phone)}</div>
                        )}
                    </div>
                )}
            </div>
            {hasBillingDetails && billingAddress && (
                <div className="rounded-xl border border-gray-200 p-4 space-y-2">
                    <h2 className="text-lg font-semibold">{t('order.confirmation.billing.title')}</h2>
                    <div className="text-sm text-gray-600">
                        {billingAddress.company && (
                            <div className="font-medium text-gray-800">{billingAddress.company}</div>
                        )}
                        {billingAddress.name && <div>{billingAddress.name}</div>}
                        {billingAddress.tax_id && (
                            <div className="text-xs text-gray-500">{t('order.confirmation.billing.taxIdLabel')} {billingAddress.tax_id}</div>
                        )}
                        {billingAddress.city && <div>{billingAddress.city}</div>}
                        {billingAddress.addr && <div>{billingAddress.addr}</div>}
                        {billingAddress.postal_code && <div>{billingAddress.postal_code}</div>}
                        {billingAddress.phone && (
                            <div>{formatPhoneForDisplay(billingAddress.phone)}</div>
                        )}
                    </div>
                </div>
            )}
            <div className="border rounded-xl overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50 text-left text-sm">
                    <tr>
                        <th className="p-3">{t('order.confirmation.table.product')}</th>
                        <th className="p-3 w-24">{t('order.confirmation.table.quantity')}</th>
                        <th className="p-3 w-36">{t('order.confirmation.table.price')}</th>
                        <th className="p-3 w-36">{t('order.confirmation.table.total')}</th>
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
                                                {t('order.confirmation.table.viewProduct')}
                                            </Link>
                                        )}
                                        {it.product?.vendor && (
                                            <div className="mt-1 text-xs text-gray-500">
                                                {t('order.confirmation.table.vendor')} {it.product.vendor.name ?? `#${it.product.vendor.id}`}{' '}
                                                {it.product.vendor.id && (
                                                    <Link
                                                        to={`/seller/${it.product.vendor.id}`}
                                                        className="text-blue-600 hover:underline"
                                                    >
                                                        {t('order.confirmation.table.contactSeller')}
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
                    <tfoot className="border-t bg-gray-50 text-sm">
                    <tr>
                        <td className="p-3 text-right font-medium" colSpan={3}>{t('order.confirmation.table.subtotal')}</td>
                        <td className="p-3 font-semibold">{formatPrice(subtotal, currency)}</td>
                    </tr>
                    {hasCouponCode && (
                        <tr>
                            <td className="p-3 text-right font-medium" colSpan={3}>{t('order.confirmation.table.coupon')}</td>
                            <td className="p-3 font-semibold">{couponCode}</td>
                        </tr>
                    )}
                    {hasDiscount && (
                        <tr>
                            <td className="p-3 text-right font-medium" colSpan={3}>{t('order.confirmation.table.discount')}</td>
                            <td className="p-3 font-semibold text-red-600">−{formatPrice(discountTotal, currency)}</td>
                        </tr>
                    )}
                    {hasLoyaltyPoints && (
                        <tr>
                            <td className="p-3 text-right font-medium" colSpan={3}>{t('order.confirmation.table.loyalty')}</td>
                            <td className="p-3 font-semibold">
                                {loyaltyPointsDisplay}
                                {loyaltyPointsValue > 0 && (
                                    <span className="ml-1 text-gray-500">
                                        {t('order.confirmation.table.loyaltyValue', {
                                            amount: formatPrice(loyaltyPointsValue, currency),
                                        })}
                                    </span>
                                )}
                            </td>
                        </tr>
                    )}
                    <tr>
                        <td className="p-3 text-right font-semibold" colSpan={3}>{t('order.confirmation.table.amountDue')}</td>
                        <td className="p-3 text-lg font-semibold">{formatPrice(order.total, currency)}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div className="flex gap-3">
                <Link to="/" className="px-4 py-2 rounded-lg border hover:bg-gray-50">{t('order.confirmation.cta.continue')}</Link>
            </div>

            {!isPaid && (
                <div className="border rounded-xl p-4">
                    <h2 className="font-semibold mb-2">{t('order.confirmation.payment.title')}</h2>
                    <p className="text-sm text-gray-600 mb-3">{t('order.confirmation.payment.description')}</p>
                    <PayOrder number={order.number} onPaid={handlePaid} />
                </div>
            )}
        </div>
    );
}
