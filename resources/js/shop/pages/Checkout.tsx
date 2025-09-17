import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    AddressesApi,
    CartApi,
    OrdersApi,
    refreshOrderStatus,
    type Address,
    type Cart,
    type OrderResponse,
} from '../api';
import useCart from '../useCart';
import { useNotify } from '../ui/notify';
import SeoHead from '../components/SeoHead';
import { GA } from '../ui/ga';
import { formatPrice } from '../ui/format';
import { resolveErrorMessage } from '../lib/errors';
import PayOrder from '../components/PayOrder';
import useAuth from '../hooks/useAuth';

const deliveryOptions = [
    {
        id: 'nova' as const,
        title: 'Нова пошта',
        description: 'Доставка протягом 2–3 днів по Україні.',
    },
    {
        id: 'ukr' as const,
        title: 'Укрпошта',
        description: 'Економна доставка 3–5 днів до відділення.',
    },
    {
        id: 'pickup' as const,
        title: 'Самовивіз',
        description: 'Заберіть замовлення сьогодні у нашій майстерні (Київ).',
    },
];

type DeliveryOptionId = typeof deliveryOptions[number]['id'];

type CheckoutStep = 'address' | 'delivery' | 'payment';

type AddressFormState = {
    name: string;
    city: string;
    addr: string;
    postal_code: string;
    phone: string;
};

type AddressErrors = Partial<Record<'email' | keyof AddressFormState, string>>;

const stepOrder: CheckoutStep[] = ['address', 'delivery', 'payment'];
const stepLabels: Record<CheckoutStep, string> = {
    address: 'Адреса',
    delivery: 'Доставка',
    payment: 'Оплата',
};

function StepIndicator({ current }: { current: CheckoutStep }) {
    return (
        <ol className="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
            {stepOrder.map((key, index) => {
                const active = key === current;
                const completed = stepOrder.indexOf(current) > index;
                return (
                    <li key={key} className="flex items-center gap-3">
                        <span
                            className={`flex h-8 w-8 items-center justify-center rounded-full border text-sm font-semibold ${
                                active
                                    ? 'border-black bg-black text-white'
                                    : completed
                                        ? 'border-black bg-white text-black'
                                        : 'border-gray-300 bg-white text-gray-500'
                            }`}
                        >
                            {index + 1}
                        </span>
                        <span
                            className={`text-sm font-medium ${
                                active ? 'text-black' : completed ? 'text-gray-700' : 'text-gray-500'
                            }`}
                        >
                            {stepLabels[key]}
                        </span>
                    </li>
                );
            })}
        </ol>
    );
}

function formatCartSubtotal(cart: Cart | null): number {
    if (!cart) return 0;
    if (cart.subtotal !== undefined && cart.subtotal !== null) {
        return Number(cart.subtotal) || 0;
    }
    return (cart.items ?? []).reduce(
        (sum, item) => sum + Number(item.price ?? 0) * Number(item.qty ?? 0),
        0,
    );
}

const emptyAddress: AddressFormState = {
    name: '',
    city: '',
    addr: '',
    postal_code: '',
    phone: '',
};

export default function CheckoutPage() {
    const nav = useNavigate();
    const { error: notifyError, success: notifySuccess } = useNotify();
    const { user, isAuthenticated } = useAuth();
    const { cart, reload, clear } = useCart();

    const [step, setStep] = useState<CheckoutStep>('address');
    const [email, setEmail] = useState('');
    const [addressForm, setAddressForm] = useState<AddressFormState>({ ...emptyAddress });
    const [addressErrors, setAddressErrors] = useState<AddressErrors>({});
    const [addresses, setAddresses] = useState<Address[]>([]);
    const [addressesLoading, setAddressesLoading] = useState(false);
    const [addressesError, setAddressesError] = useState<string | null>(null);
    const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
    const [deliveryMethod, setDeliveryMethod] = useState<DeliveryOptionId>(deliveryOptions[0].id);
    const [deliveryComment, setDeliveryComment] = useState('');
    const [couponCode, setCouponCode] = useState('');
    const [couponError, setCouponError] = useState<string | null>(null);
    const [couponLoading, setCouponLoading] = useState(false);
    const [creatingOrder, setCreatingOrder] = useState(false);
    const [createError, setCreateError] = useState<string | null>(null);
    const [order, setOrder] = useState<OrderResponse | null>(null);

    const emailInitialized = useRef(false);
    const addressInitialized = useRef(false);
    const lastAppliedCoupon = useRef<string | null>(null);

    useEffect(() => {
        (async () => {
            try {
                const current = await CartApi.get();
                const itemsCount = current?.items?.length ?? 0;
                if (!current || current.status !== 'active' || itemsCount === 0) {
                    notifyError({ title: 'Кошик порожній або вже оформлено.' });
                    nav('/cart', { replace: true });
                }
            } catch {
                notifyError({ title: 'Не вдалося перевірити кошик.' });
                nav('/cart', { replace: true });
            }
        })();
    }, [nav, notifyError]);

    useEffect(() => {
        GA.begin_checkout(cart);
    }, [cart]);

    useEffect(() => {
        if (emailInitialized.current) return;
        if (user?.email) {
            setEmail(user.email);
            emailInitialized.current = true;
        }
    }, [user?.email]);

    useEffect(() => {
        if (!isAuthenticated) {
            setAddresses([]);
            setSelectedAddressId(null);
            addressInitialized.current = false;
            return;
        }
        let ignore = false;
        setAddressesLoading(true);
        setAddressesError(null);
        AddressesApi.list()
            .then((list) => {
                if (ignore) return;
                setAddresses(list);
                if (!addressInitialized.current && list.length) {
                    const [first] = list;
                    if (first) {
                        setSelectedAddressId(first.id);
                        setAddressForm({
                            name: first.name ?? '',
                            city: first.city ?? '',
                            addr: first.addr ?? '',
                            postal_code: first.postal_code ?? '',
                            phone: first.phone ?? '',
                        });
                        addressInitialized.current = true;
                    }
                }
            })
            .catch((error) => {
                if (ignore) return;
                setAddressesError(resolveErrorMessage(error, 'Не вдалося завантажити адреси.'));
            })
            .finally(() => {
                if (!ignore) setAddressesLoading(false);
            });

        return () => {
            ignore = true;
        };
    }, [isAuthenticated]);

    useEffect(() => {
        const applied = cart?.discounts?.coupon?.code;
        if (typeof applied === 'string' && applied) {
            lastAppliedCoupon.current = applied;
            setCouponCode(applied);
        } else if (!applied && lastAppliedCoupon.current) {
            lastAppliedCoupon.current = '';
            setCouponCode('');
        }
    }, [cart?.discounts?.coupon?.code]);

    const subtotal = useMemo(() => formatCartSubtotal(cart), [cart]);
    const discountValue = useMemo(() => Number(cart?.discounts?.coupon?.amount ?? 0), [cart]);
    const cartTotal = useMemo(() => Number(cart?.total ?? subtotal), [cart, subtotal]);

    const handleEmailChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setEmail(event.target.value);
        setAddressErrors((prev) => {
            if (!prev.email) return prev;
            const next = { ...prev };
            delete next.email;
            return next;
        });
    };

    const handleAddressInputChange = (field: keyof AddressFormState) => (
        event: React.ChangeEvent<HTMLInputElement>,
    ) => {
        const value = event.target.value;
        setAddressForm((prev) => ({ ...prev, [field]: value }));
        setAddressErrors((prev) => {
            if (!prev[field]) return prev;
            const next = { ...prev };
            delete next[field];
            return next;
        });
    };

    const handleSelectAddress = (addr: Address) => {
        setSelectedAddressId(addr.id);
        setAddressForm({
            name: addr.name ?? '',
            city: addr.city ?? '',
            addr: addr.addr ?? '',
            postal_code: addr.postal_code ?? '',
            phone: addr.phone ?? '',
        });
        setAddressErrors({});
    };

    const handleAddressSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        const errors: AddressErrors = {};
        const trimmedEmail = email.trim();
        if (!trimmedEmail) {
            errors.email = 'Вкажіть email для підтвердження.';
        } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(trimmedEmail)) {
            errors.email = 'Некоректний email.';
        }
        if (!addressForm.name.trim()) errors.name = 'Вкажіть імʼя одержувача.';
        if (!addressForm.city.trim()) errors.city = 'Вкажіть місто доставки.';
        if (!addressForm.addr.trim()) errors.addr = 'Вкажіть адресу доставки.';

        setAddressErrors(errors);
        if (Object.keys(errors).length === 0) {
            setStep('delivery');
        }
    };

    const handleApplyCoupon = async (event: React.FormEvent) => {
        event.preventDefault();
        if (couponLoading) return;
        setCouponError(null);
        setCouponLoading(true);
        try {
            const normalized = couponCode.trim();
            await CartApi.applyCoupon(normalized ? normalized : null);
            await reload();
            notifySuccess({ title: normalized ? 'Купон застосовано.' : 'Купон скасовано.' });
        } catch (error) {
            setCouponError(resolveErrorMessage(error, 'Не вдалося застосувати купон.'));
        } finally {
            setCouponLoading(false);
        }
    };

    const handleGoToPayment = async () => {
        if (creatingOrder) return;
        if (order) {
            setStep('payment');
            return;
        }
        setCreatingOrder(true);
        setCreateError(null);
        try {
            const trimmedEmail = email.trim();
            const shipping = {
                name: addressForm.name.trim(),
                city: addressForm.city.trim(),
                addr: addressForm.addr.trim(),
                ...(addressForm.postal_code.trim() ? { postal_code: addressForm.postal_code.trim() } : {}),
                ...(addressForm.phone.trim() ? { phone: addressForm.phone.trim() } : {}),
            };
            const delivery = deliveryOptions.find((opt) => opt.id === deliveryMethod);
            const noteParts = [delivery ? `Доставка: ${delivery.title}` : null];
            if (deliveryComment.trim()) {
                noteParts.push(`Коментар: ${deliveryComment.trim()}`);
            }
            const payload = {
                email: trimmedEmail,
                shipping_address: shipping,
                note: noteParts.filter(Boolean).join('\n') || undefined,
            };
            const created = await OrdersApi.create(payload);
            setOrder(created);
            notifySuccess({ title: 'Замовлення створено. Завершіть оплату.' });
            try {
                await clear();
            } catch {
                /* no-op */
            }
            setStep('payment');
        } catch (error) {
            const message = resolveErrorMessage(error, 'Не вдалося створити замовлення.');
            setCreateError(message);
            notifyError({ title: message });
        } finally {
            setCreatingOrder(false);
        }
    };

    const orderNumber = order?.number;
    const handlePaid = useCallback(
        async (_status?: string, paymentIntentId?: string) => {
            if (!orderNumber) return;
            const attemptRefresh = async (intent?: string) => {
                await refreshOrderStatus(orderNumber, intent);
            };
            try {
                await attemptRefresh(paymentIntentId);
            } catch (error) {
                try {
                    await attemptRefresh();
                } catch (secondaryError) {
                    console.error('Failed to refresh order status', secondaryError);
                }
            }
            nav(`/order/${encodeURIComponent(orderNumber)}`, { replace: true });
        },
        [nav, orderNumber],
    );

    const selectedDelivery = useMemo(
        () => deliveryOptions.find((opt) => opt.id === deliveryMethod) ?? deliveryOptions[0],
        [deliveryMethod],
    );

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-6">
            <SeoHead title="Оформлення замовлення — Shop" robots="noindex,nofollow" canonical />
            <h1 className="text-2xl font-semibold">Оформлення замовлення</h1>

            <StepIndicator current={step} />

            {step === 'address' && (
                <form onSubmit={handleAddressSubmit} className="space-y-6">
                    <div className="grid gap-3">
                        <label className="text-sm font-medium text-gray-700" htmlFor="checkout-email">
                            Контактний email
                        </label>
                        <input
                            id="checkout-email"
                            type="email"
                            className={`w-full rounded-lg border px-3 py-2 ${
                                addressErrors.email ? 'border-red-500' : 'border-gray-300'
                            }`}
                            placeholder="you@example.com"
                            value={email}
                            onChange={handleEmailChange}
                            data-testid="email"
                            aria-invalid={addressErrors.email ? 'true' : 'false'}
                        />
                        {addressErrors.email && (
                            <p className="text-sm text-red-600">{addressErrors.email}</p>
                        )}
                    </div>

                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold">Збережені адреси</h2>
                            {addressesLoading && <span className="text-sm text-gray-500">Завантаження…</span>}
                        </div>
                        {addressesError && (
                            <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {addressesError}
                            </div>
                        )}
                        {addresses.length === 0 && !addressesLoading && (
                            <p className="text-sm text-gray-600">
                                {isAuthenticated
                                    ? 'У вас ще немає збережених адрес.'
                                    : 'Увійдіть, щоб використовувати збережені адреси.'}
                            </p>
                        )}
                        <div className="grid gap-3 sm:grid-cols-2">
                            {addresses.map((addr) => {
                                const active = addr.id === selectedAddressId;
                                return (
                                    <button
                                        type="button"
                                        key={addr.id}
                                        onClick={() => handleSelectAddress(addr)}
                                        className={`rounded-xl border px-4 py-3 text-left transition ${
                                            active
                                                ? 'border-black shadow-sm'
                                                : 'border-gray-200 hover:border-gray-300'
                                        }`}
                                    >
                                        <div className="font-medium">{addr.name}</div>
                                        <div className="text-sm text-gray-600">{addr.city}</div>
                                        <div className="text-sm text-gray-600">{addr.addr}</div>
                                        {addr.postal_code && (
                                            <div className="text-xs text-gray-500">{addr.postal_code}</div>
                                        )}
                                        {addr.phone && (
                                            <div className="text-xs text-gray-500">{addr.phone}</div>
                                        )}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div className="grid gap-4">
                        <div>
                            <label className="text-sm font-medium text-gray-700" htmlFor="shipping-name">
                                Імʼя одержувача
                            </label>
                            <input
                                id="shipping-name"
                                className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                    addressErrors.name ? 'border-red-500' : 'border-gray-300'
                                }`}
                                value={addressForm.name}
                                onChange={handleAddressInputChange('name')}
                                placeholder="Імʼя Прізвище"
                                data-testid="shipping-name"
                                aria-invalid={addressErrors.name ? 'true' : 'false'}
                            />
                            {addressErrors.name && (
                                <p className="text-sm text-red-600">{addressErrors.name}</p>
                            )}
                        </div>
                        <div>
                            <label className="text-sm font-medium text-gray-700" htmlFor="shipping-city">
                                Місто
                            </label>
                            <input
                                id="shipping-city"
                                className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                    addressErrors.city ? 'border-red-500' : 'border-gray-300'
                                }`}
                                value={addressForm.city}
                                onChange={handleAddressInputChange('city')}
                                placeholder="Київ"
                                data-testid="shipping-city"
                                aria-invalid={addressErrors.city ? 'true' : 'false'}
                            />
                            {addressErrors.city && (
                                <p className="text-sm text-red-600">{addressErrors.city}</p>
                            )}
                        </div>
                        <div>
                            <label className="text-sm font-medium text-gray-700" htmlFor="shipping-addr">
                                Адреса доставки
                            </label>
                            <input
                                id="shipping-addr"
                                className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                    addressErrors.addr ? 'border-red-500' : 'border-gray-300'
                                }`}
                                value={addressForm.addr}
                                onChange={handleAddressInputChange('addr')}
                                placeholder="вул. Шевченка, 1"
                                data-testid="shipping-addr"
                                aria-invalid={addressErrors.addr ? 'true' : 'false'}
                            />
                            {addressErrors.addr && (
                                <p className="text-sm text-red-600">{addressErrors.addr}</p>
                            )}
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label className="text-sm font-medium text-gray-700" htmlFor="shipping-postal">
                                    Поштовий індекс (необовʼязково)
                                </label>
                                <input
                                    id="shipping-postal"
                                    className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                    value={addressForm.postal_code}
                                    onChange={handleAddressInputChange('postal_code')}
                                    placeholder="01001"
                                />
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-700" htmlFor="shipping-phone">
                                    Телефон (необовʼязково)
                                </label>
                                <input
                                    id="shipping-phone"
                                    className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                    value={addressForm.phone}
                                    onChange={handleAddressInputChange('phone')}
                                    placeholder="+380 00 000 0000"
                                />
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button
                            type="submit"
                            className="rounded-lg bg-black px-5 py-2 text-white transition hover:bg-black/90"
                        >
                            До доставки
                        </button>
                    </div>
                </form>
            )}

            {step === 'delivery' && (
                <div className="space-y-6">
                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold">Спосіб доставки</h2>
                        <div className="grid gap-3 sm:grid-cols-3">
                            {deliveryOptions.map((option) => {
                                const active = option.id === deliveryMethod;
                                return (
                                    <button
                                        type="button"
                                        key={option.id}
                                        onClick={() => setDeliveryMethod(option.id)}
                                        className={`rounded-xl border px-4 py-3 text-left transition ${
                                            active
                                                ? 'border-black shadow-sm'
                                                : 'border-gray-200 hover:border-gray-300'
                                        }`}
                                    >
                                        <div className="font-medium">{option.title}</div>
                                        <div className="mt-1 text-sm text-gray-600">{option.description}</div>
                                    </button>
                                );
                            })}
                        </div>
                        <div>
                            <label className="text-sm font-medium text-gray-700" htmlFor="delivery-comment">
                                Коментар курʼєру (необовʼязково)
                            </label>
                            <textarea
                                id="delivery-comment"
                                className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                rows={3}
                                value={deliveryComment}
                                onChange={(event) => setDeliveryComment(event.target.value)}
                                placeholder="Наприклад, дзвоніть за 30 хвилин до доставки"
                            />
                        </div>
                    </div>

                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold">Купон</h2>
                        <form onSubmit={handleApplyCoupon} className="flex flex-col gap-3 sm:flex-row">
                            <input
                                className={`w-full rounded-lg border px-3 py-2 ${
                                    couponError ? 'border-red-500' : 'border-gray-300'
                                }`}
                                value={couponCode}
                                onChange={(event) => setCouponCode(event.target.value)}
                                placeholder="Введіть код купона"
                                aria-invalid={couponError ? 'true' : 'false'}
                            />
                            <button
                                type="submit"
                                disabled={couponLoading}
                                className="rounded-lg bg-black px-4 py-2 text-white transition hover:bg-black/90 disabled:opacity-60"
                            >
                                {couponLoading ? 'Застосування…' : 'Застосувати'}
                            </button>
                        </form>
                        {couponError && (
                            <p className="text-sm text-red-600">{couponError}</p>
                        )}
                        {cart?.discounts?.coupon?.code && (
                            <p className="text-sm text-green-600">
                                Застосовано купон: {cart.discounts.coupon.code}
                            </p>
                        )}
                    </div>

                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold">Ваше замовлення</h2>
                        <div className="divide-y rounded-xl border border-gray-200">
                            {(cart?.items ?? []).map((item) => {
                                const lineTotal = item.line_total ?? Number(item.price ?? 0) * Number(item.qty ?? 0);
                                return (
                                    <div key={item.id} className="flex items-start justify-between gap-3 p-3">
                                        <div className="flex-1">
                                            <div className="font-medium">
                                                {item.name ?? item.product?.name ?? `#${item.product_id}`}
                                            </div>
                                            <div className="text-xs text-gray-500">Кількість: {item.qty}</div>
                                        </div>
                                        <div className="text-right text-sm text-gray-500">
                                            {formatPrice(lineTotal)}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                        <div className="space-y-1 rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm">
                            <div className="flex justify-between">
                                <span>Сума товарів</span>
                                <span className="font-medium">{formatPrice(subtotal)}</span>
                            </div>
                            {discountValue > 0 && (
                                <div className="flex justify-between text-green-600">
                                    <span>Знижка</span>
                                    <span>-{formatPrice(discountValue)}</span>
                                </div>
                            )}
                            <div className="flex justify-between text-base font-semibold">
                                <span>До оплати</span>
                                <span>{formatPrice(cartTotal)}</span>
                            </div>
                        </div>
                    </div>

                    {createError && (
                        <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                            {createError}
                        </div>
                    )}

                    <div className="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                        Після переходу до оплати змінити адресу або доставку буде неможливо без
                        створення нового замовлення.
                    </div>

                    <div className="flex items-center justify-between gap-3">
                        <button
                            type="button"
                            onClick={() => setStep('address')}
                            className="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-50"
                        >
                            Назад
                        </button>
                        <button
                            type="button"
                            onClick={handleGoToPayment}
                            disabled={creatingOrder}
                            className="rounded-lg bg-black px-5 py-2 text-white transition hover:bg-black/90 disabled:opacity-60"
                            data-testid="place-order"
                        >
                            {creatingOrder ? 'Створення…' : 'До оплати'}
                        </button>
                    </div>
                </div>
            )}

            {step === 'payment' && (
                <div className="space-y-6">
                    {!order && (
                        <div className="rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-sm text-yellow-700">
                            Підготовка оплати…
                        </div>
                    )}
                    {order && (
                        <>
                            <div className="space-y-2 rounded-xl border border-gray-200 p-4">
                                <div className="text-sm text-gray-500">Номер замовлення</div>
                                <div className="text-xl font-semibold">{order.number}</div>
                                <div className="text-sm text-gray-600">
                                    Підтвердження буде надіслано на {order.email}.
                                </div>
                                <div className="text-sm text-gray-600">
                                    Сума до оплати: {formatPrice(order.total, order.currency ?? 'EUR')}
                                </div>
                            </div>

                            <div className="space-y-3 rounded-xl border border-gray-200 p-4">
                                <h2 className="text-lg font-semibold">Оплата</h2>
                                <p className="text-sm text-gray-600">
                                    Безпечна оплата через Stripe. Після успішної транзакції ви будете перенаправлені до
                                    підтвердження замовлення.
                                </p>
                                <PayOrder number={order.number} onPaid={handlePaid} />
                            </div>

                            <div className="space-y-3">
                                <h2 className="text-lg font-semibold">Доставка</h2>
                                <div className="rounded-xl border border-gray-200 p-4 text-sm text-gray-700">
                                    <div className="font-medium">{order.shipping_address?.name}</div>
                                    <div>{order.shipping_address?.city}</div>
                                    <div>{order.shipping_address?.addr}</div>
                                    {order.shipping_address?.postal_code && (
                                        <div>{order.shipping_address.postal_code}</div>
                                    )}
                                    {order.shipping_address?.phone && (
                                        <div>{order.shipping_address.phone}</div>
                                    )}
                                    <div className="mt-2 text-gray-600">
                                        Спосіб доставки: {selectedDelivery.title}
                                    </div>
                                    {deliveryComment.trim() && (
                                        <div className="text-gray-600">Коментар: {deliveryComment.trim()}</div>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-3">
                                <h2 className="text-lg font-semibold">Товари</h2>
                                <div className="divide-y rounded-xl border border-gray-200">
                                    {(order.items ?? []).map((item) => {
                                        const line = item.subtotal ?? Number(item.price ?? 0) * Number(item.qty ?? 0);
                                        return (
                                            <div key={item.id} className="flex items-start justify-between gap-3 p-3">
                                                <div className="flex-1">
                                                    <div className="font-medium">
                                                        {item.name ?? item.product?.name ?? `#${item.product_id}`}
                                                    </div>
                                                    <div className="text-xs text-gray-500">Кількість: {item.qty}</div>
                                                </div>
                                                <div className="text-right text-sm text-gray-500">
                                                    {formatPrice(line, order.currency ?? 'EUR')}
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </>
                    )}
                </div>
            )}
        </div>
    );
}
