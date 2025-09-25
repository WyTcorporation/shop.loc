import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { isAxiosError } from 'axios';
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
import { useLocale } from '../i18n/LocaleProvider';
import type { TranslationKey } from '../i18n/messages';

const deliveryOptionDefinitions = [
    {
        id: 'nova',
        titleKey: 'checkout.delivery.options.nova.title',
        descriptionKey: 'checkout.delivery.options.nova.description',
    },
    {
        id: 'ukr',
        titleKey: 'checkout.delivery.options.ukr.title',
        descriptionKey: 'checkout.delivery.options.ukr.description',
    },
    {
        id: 'pickup',
        titleKey: 'checkout.delivery.options.pickup.title',
        descriptionKey: 'checkout.delivery.options.pickup.description',
    },
] as const satisfies readonly {
    id: 'nova' | 'ukr' | 'pickup';
    titleKey: TranslationKey;
    descriptionKey: TranslationKey;
}[];

type DeliveryOptionId = typeof deliveryOptionDefinitions[number]['id'];

type CheckoutStep = 'address' | 'delivery' | 'payment';

type AddressFormState = {
    name: string;
    city: string;
    addr: string;
    postal_code: string;
    phone: string;
};

type AddressErrors = Partial<Record<'email' | keyof AddressFormState, string>>;

type BillingFormState = {
    name: string;
    company: string;
    tax_id: string;
    city: string;
    addr: string;
    postal_code: string;
    phone: string;
};

type BillingErrors = Partial<Record<keyof BillingFormState, string>>;

const stepOrder: CheckoutStep[] = ['address', 'delivery', 'payment'];

function StepIndicator({ current, labels }: { current: CheckoutStep; labels: Record<CheckoutStep, string> }) {
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
                            {labels[key]}
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

const emptyBilling: BillingFormState = {
    name: '',
    company: '',
    tax_id: '',
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
    const { t } = useLocale();

    const brand = t('header.brand');
    const stepLabels = useMemo(
        () => ({
            address: t('checkout.steps.address'),
            delivery: t('checkout.steps.delivery'),
            payment: t('checkout.steps.payment'),
        }),
        [t],
    );
    const deliveryOptions = useMemo(
        () =>
            deliveryOptionDefinitions.map((option) => ({
                id: option.id as DeliveryOptionId,
                title: t(option.titleKey),
                description: t(option.descriptionKey),
            })),
        [t],
    );

    const [step, setStep] = useState<CheckoutStep>('address');
    const [email, setEmail] = useState('');
    const [addressForm, setAddressForm] = useState<AddressFormState>({ ...emptyAddress });
    const [addressErrors, setAddressErrors] = useState<AddressErrors>({});
    const [billingEnabled, setBillingEnabled] = useState(false);
    const [billingForm, setBillingForm] = useState<BillingFormState>({ ...emptyBilling });
    const [billingErrors, setBillingErrors] = useState<BillingErrors>({});
    const [addresses, setAddresses] = useState<Address[]>([]);
    const [addressesLoading, setAddressesLoading] = useState(false);
    const [addressesError, setAddressesError] = useState<string | null>(null);
    const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
    const [deliveryMethod, setDeliveryMethod] = useState<DeliveryOptionId>(deliveryOptionDefinitions[0].id);
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
                    notifyError({ title: t('checkout.notifications.cartUnavailable') });
                    nav('/cart', { replace: true });
                }
            } catch {
                notifyError({ title: t('checkout.notifications.cartCheckFailed') });
                nav('/cart', { replace: true });
            }
        })();
    }, [nav, notifyError, t]);

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
                setAddressesError(resolveErrorMessage(error, t('checkout.notifications.addressesLoadFailed')));
            })
            .finally(() => {
                if (!ignore) setAddressesLoading(false);
            });

        return () => {
            ignore = true;
        };
    }, [isAuthenticated, t]);

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

    const handleBillingInputChange = (field: keyof BillingFormState) => (
        event: React.ChangeEvent<HTMLInputElement>,
    ) => {
        const value = event.target.value;
        setBillingForm((prev) => ({ ...prev, [field]: value }));
        setBillingErrors((prev) => {
            if (!prev[field]) return prev;
            const next = { ...prev };
            delete next[field];
            return next;
        });
    };

    const handleToggleBilling = (event: React.ChangeEvent<HTMLInputElement>) => {
        const checked = event.target.checked;
        setBillingEnabled(checked);
        if (!checked) {
            setBillingForm({ ...emptyBilling });
            setBillingErrors({});
        }
    };

    const handleCopyShippingToBilling = () => {
        setBillingForm((prev) => ({
            ...prev,
            name: addressForm.name,
            city: addressForm.city,
            addr: addressForm.addr,
            postal_code: addressForm.postal_code,
            phone: addressForm.phone,
        }));
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
        const billingIssues: BillingErrors = {};
        const trimmedEmail = email.trim();
        if (!trimmedEmail) {
            errors.email = t('checkout.errors.emailRequired');
        } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(trimmedEmail)) {
            errors.email = t('checkout.errors.emailInvalid');
        }
        if (!addressForm.name.trim()) errors.name = t('checkout.errors.shippingNameRequired');
        if (!addressForm.city.trim()) errors.city = t('checkout.errors.shippingCityRequired');
        if (!addressForm.addr.trim()) errors.addr = t('checkout.errors.shippingAddrRequired');

        if (billingEnabled) {
            if (!billingForm.name.trim()) billingIssues.name = t('checkout.errors.billingNameRequired');
            if (!billingForm.city.trim()) billingIssues.city = t('checkout.errors.billingCityRequired');
            if (!billingForm.addr.trim()) billingIssues.addr = t('checkout.errors.billingAddrRequired');
            if (billingForm.company.trim() && !billingForm.tax_id.trim()) {
                billingIssues.tax_id = t('checkout.errors.billingTaxRequired');
            }
        }

        setAddressErrors(errors);
        setBillingErrors(billingIssues);
        if (Object.keys(errors).length === 0 && Object.keys(billingIssues).length === 0) {
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
            notifySuccess({
                title: normalized
                    ? t('checkout.notifications.couponApplied')
                    : t('checkout.notifications.couponRemoved'),
            });
        } catch (error) {
            setCouponError(resolveErrorMessage(error, t('checkout.notifications.couponApplyFailed')));
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
            const billing = billingEnabled
                ? {
                      name: billingForm.name.trim(),
                      city: billingForm.city.trim(),
                      addr: billingForm.addr.trim(),
                      ...(billingForm.company.trim() ? { company: billingForm.company.trim() } : {}),
                      ...(billingForm.tax_id.trim() ? { tax_id: billingForm.tax_id.trim() } : {}),
                      ...(billingForm.postal_code.trim()
                          ? { postal_code: billingForm.postal_code.trim() }
                          : {}),
                      ...(billingForm.phone.trim() ? { phone: billingForm.phone.trim() } : {}),
                  }
                : null;
            const delivery = deliveryOptions.find((opt) => opt.id === deliveryMethod);
            const deliveryLabel = delivery?.title ?? deliveryMethod;
            const comment = deliveryComment.trim();
            const payload = {
                email: trimmedEmail,
                shipping_address: shipping,
                ...(billing ? { billing_address: billing } : {}),
                delivery_method: deliveryLabel,
                ...(comment ? { note: comment } : {}),
            };
            const created = await OrdersApi.create(payload);
            setOrder(created);
            notifySuccess({ title: t('checkout.notifications.orderCreateSuccess') });
            try {
                await clear();
            } catch {
                /* no-op */
            }
            setStep('payment');
        } catch (error) {
            if (isAxiosError(error)) {
                const payload = error.response?.data as
                    | { code?: string; product_id?: number; message?: string }
                    | undefined;

                if (payload?.code === 'sold_out') {
                    const productId = payload.product_id;
                    const matchingItem = cart?.items?.find((item) => item.product_id === productId);
                    const productName = matchingItem?.name ?? matchingItem?.product?.name ?? null;
                    const apologyMessage = typeof payload.message === 'string' ? payload.message : undefined;

                    try {
                        await reload();
                    } catch {
                        /* no-op */
                    }

                    nav('sold-out', {
                        replace: true,
                        state: {
                            productName: productName ?? undefined,
                            message: apologyMessage,
                        },
                    });

                    return;
                }
            }

            const message = resolveErrorMessage(error, t('checkout.notifications.orderCreateFailed'));
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
            } catch {
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
        [deliveryMethod, deliveryOptions],
    );

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-6">
            <SeoHead title={t('checkout.seoTitle', { brand })} robots="noindex,nofollow" canonical />
            <h1 className="text-2xl font-semibold">{t('checkout.title')}</h1>

            <StepIndicator current={step} labels={stepLabels} />

            {step === 'address' && (
                <form onSubmit={handleAddressSubmit} className="space-y-6">
                    <div className="grid gap-3">
                        <label className="text-sm font-medium text-gray-700" htmlFor="checkout-email">
                            {t('checkout.address.emailLabel')}
                        </label>
                        <input
                            id="checkout-email"
                            type="email"
                            className={`w-full rounded-lg border px-3 py-2 ${
                                addressErrors.email ? 'border-red-500' : 'border-gray-300'
                            }`}
                            placeholder={t('checkout.address.emailPlaceholder')}
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
                            <h2 className="text-lg font-semibold">{t('checkout.address.saved.title')}</h2>
                            {addressesLoading && <span className="text-sm text-gray-500">{t('common.loading')}</span>}
                        </div>
                        {addressesError && (
                            <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {addressesError}
                            </div>
                        )}
                        {addresses.length === 0 && !addressesLoading && (
                            <p className="text-sm text-gray-600">
                                {isAuthenticated
                                    ? t('checkout.address.saved.emptyAuthenticated')
                                    : t('checkout.address.saved.emptyGuest')}
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
                                {t('checkout.address.fields.name.label')}
                            </label>
                            <input
                                id="shipping-name"
                                className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                    addressErrors.name ? 'border-red-500' : 'border-gray-300'
                                }`}
                                value={addressForm.name}
                                onChange={handleAddressInputChange('name')}
                                placeholder={t('checkout.address.fields.name.placeholder')}
                                data-testid="shipping-name"
                                aria-invalid={addressErrors.name ? 'true' : 'false'}
                            />
                            {addressErrors.name && (
                                <p className="text-sm text-red-600">{addressErrors.name}</p>
                            )}
                        </div>
                        <div>
                            <label className="text-sm font-medium text-gray-700" htmlFor="shipping-city">
                                {t('checkout.address.fields.city.label')}
                            </label>
                            <input
                                id="shipping-city"
                                className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                    addressErrors.city ? 'border-red-500' : 'border-gray-300'
                                }`}
                                value={addressForm.city}
                                onChange={handleAddressInputChange('city')}
                                placeholder={t('checkout.address.fields.city.placeholder')}
                                data-testid="shipping-city"
                                aria-invalid={addressErrors.city ? 'true' : 'false'}
                            />
                            {addressErrors.city && (
                                <p className="text-sm text-red-600">{addressErrors.city}</p>
                            )}
                        </div>
                        <div>
                            <label className="text-sm font-medium text-gray-700" htmlFor="shipping-addr">
                                {t('checkout.address.fields.addr.label')}
                            </label>
                            <input
                                id="shipping-addr"
                                className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                    addressErrors.addr ? 'border-red-500' : 'border-gray-300'
                                }`}
                                value={addressForm.addr}
                                onChange={handleAddressInputChange('addr')}
                                placeholder={t('checkout.address.fields.addr.placeholder')}
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
                                    {t('checkout.address.fields.postal.optionalLabel')}
                                </label>
                                <input
                                    id="shipping-postal"
                                    className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                    value={addressForm.postal_code}
                                    onChange={handleAddressInputChange('postal_code')}
                                    placeholder={t('checkout.address.fields.postal.placeholder')}
                                />
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-700" htmlFor="shipping-phone">
                                    {t('checkout.address.fields.phone.optionalLabel')}
                                </label>
                                <input
                                    id="shipping-phone"
                                    className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                    value={addressForm.phone}
                                    onChange={handleAddressInputChange('phone')}
                                    placeholder={t('checkout.address.fields.phone.placeholder')}
                                />
                            </div>
                        </div>
                    </div>

                    <div className="space-y-4 rounded-xl border border-gray-200 p-4">
                        <label className="flex items-center gap-3 text-sm font-medium text-gray-700">
                            <input
                                type="checkbox"
                                className="h-4 w-4 rounded border-gray-300"
                                checked={billingEnabled}
                                onChange={handleToggleBilling}
                            />
                            {t('checkout.billing.toggle')}
                        </label>

                        {billingEnabled && (
                            <div className="space-y-4">
                                <div className="flex flex-wrap items-center gap-3">
                                    <p className="text-sm text-gray-600 flex-1">
                                        {t('checkout.billing.description')}
                                    </p>
                                    <button
                                        type="button"
                                        onClick={handleCopyShippingToBilling}
                                        className="text-sm font-medium text-blue-600 hover:underline"
                                    >
                                        {t('checkout.billing.copyFromShipping')}
                                    </button>
                                </div>
                                <div className="grid gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-gray-700" htmlFor="billing-name">
                                            {t('checkout.billing.fields.name.label')}
                                        </label>
                                        <input
                                            id="billing-name"
                                            className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                                billingErrors.name ? 'border-red-500' : 'border-gray-300'
                                            }`}
                                            value={billingForm.name}
                                            onChange={handleBillingInputChange('name')}
                                            placeholder={t('checkout.billing.fields.name.placeholder')}
                                            aria-invalid={billingErrors.name ? 'true' : 'false'}
                                        />
                                        {billingErrors.name && (
                                            <p className="text-sm text-red-600">{billingErrors.name}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-700" htmlFor="billing-company">
                                            {t('checkout.billing.fields.company.label')}
                                        </label>
                                        <input
                                            id="billing-company"
                                            className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                            value={billingForm.company}
                                            onChange={handleBillingInputChange('company')}
                                            placeholder={t('checkout.billing.fields.company.placeholder')}
                                        />
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-700" htmlFor="billing-tax">
                                            {t('checkout.billing.fields.taxId.label')}
                                        </label>
                                        <input
                                            id="billing-tax"
                                            className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                                billingErrors.tax_id ? 'border-red-500' : 'border-gray-300'
                                            }`}
                                            value={billingForm.tax_id}
                                            onChange={handleBillingInputChange('tax_id')}
                                            placeholder={t('checkout.billing.fields.taxId.placeholder')}
                                            aria-invalid={billingErrors.tax_id ? 'true' : 'false'}
                                        />
                                        {billingErrors.tax_id && (
                                            <p className="text-sm text-red-600">{billingErrors.tax_id}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-700" htmlFor="billing-city">
                                            {t('checkout.billing.fields.city.label')}
                                        </label>
                                        <input
                                            id="billing-city"
                                            className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                                billingErrors.city ? 'border-red-500' : 'border-gray-300'
                                            }`}
                                            value={billingForm.city}
                                            onChange={handleBillingInputChange('city')}
                                            placeholder={t('checkout.billing.fields.city.placeholder')}
                                            aria-invalid={billingErrors.city ? 'true' : 'false'}
                                        />
                                        {billingErrors.city && (
                                            <p className="text-sm text-red-600">{billingErrors.city}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-700" htmlFor="billing-addr">
                                            {t('checkout.billing.fields.addr.label')}
                                        </label>
                                        <input
                                            id="billing-addr"
                                            className={`mt-1 w-full rounded-lg border px-3 py-2 ${
                                                billingErrors.addr ? 'border-red-500' : 'border-gray-300'
                                            }`}
                                            value={billingForm.addr}
                                            onChange={handleBillingInputChange('addr')}
                                            placeholder={t('checkout.billing.fields.addr.placeholder')}
                                            aria-invalid={billingErrors.addr ? 'true' : 'false'}
                                        />
                                        {billingErrors.addr && (
                                            <p className="text-sm text-red-600">{billingErrors.addr}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-700" htmlFor="billing-postal">
                                            {t('checkout.billing.fields.postal.optionalLabel')}
                                        </label>
                                        <input
                                            id="billing-postal"
                                            className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                            value={billingForm.postal_code}
                                            onChange={handleBillingInputChange('postal_code')}
                                            placeholder={t('checkout.billing.fields.postal.placeholder')}
                                        />
                                        {billingErrors.postal_code && (
                                            <p className="text-sm text-red-600">{billingErrors.postal_code}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-700" htmlFor="billing-phone">
                                            {t('checkout.billing.fields.phone.optionalLabel')}
                                        </label>
                                        <input
                                            id="billing-phone"
                                            className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                            value={billingForm.phone}
                                            onChange={handleBillingInputChange('phone')}
                                            placeholder={t('checkout.billing.fields.phone.placeholder')}
                                        />
                                        {billingErrors.phone && (
                                            <p className="text-sm text-red-600">{billingErrors.phone}</p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="flex justify-end gap-3">
                        <button
                            type="submit"
                            className="rounded-lg bg-black px-5 py-2 text-white transition hover:bg-black/90"
                        >
                            {t('checkout.address.next')}
                        </button>
                    </div>
                </form>
            )}

            {step === 'delivery' && (
                <div className="space-y-6">
                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold">{t('checkout.delivery.title')}</h2>
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
                                {t('checkout.delivery.commentLabel')}
                            </label>
                            <textarea
                                id="delivery-comment"
                                className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                rows={3}
                                value={deliveryComment}
                                onChange={(event) => setDeliveryComment(event.target.value)}
                                placeholder={t('checkout.delivery.commentPlaceholder')}
                            />
                        </div>
                    </div>

                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold">{t('checkout.coupon.title')}</h2>
                        <form onSubmit={handleApplyCoupon} className="flex flex-col gap-3 sm:flex-row">
                            <input
                                className={`w-full rounded-lg border px-3 py-2 ${
                                    couponError ? 'border-red-500' : 'border-gray-300'
                                }`}
                                value={couponCode}
                                onChange={(event) => setCouponCode(event.target.value)}
                                placeholder={t('checkout.coupon.placeholder')}
                                aria-invalid={couponError ? 'true' : 'false'}
                            />
                            <button
                                type="submit"
                                disabled={couponLoading}
                                className="rounded-lg bg-black px-4 py-2 text-white transition hover:bg-black/90 disabled:opacity-60"
                            >
                                {couponLoading ? t('checkout.coupon.applying') : t('checkout.coupon.apply')}
                            </button>
                        </form>
                        {couponError && (
                            <p className="text-sm text-red-600">{couponError}</p>
                        )}
                        {cart?.discounts?.coupon?.code && (
                            <p className="text-sm text-green-600">
                                {t('checkout.coupon.applied', { code: cart.discounts.coupon.code })}
                            </p>
                        )}
                    </div>

                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold">{t('checkout.summary.title')}</h2>
                        <div className="divide-y rounded-xl border border-gray-200">
                            {(cart?.items ?? []).map((item) => {
                                const lineTotal = item.line_total ?? Number(item.price ?? 0) * Number(item.qty ?? 0);
                                return (
                                    <div key={item.id} className="flex items-start justify-between gap-3 p-3">
                                        <div className="flex-1">
                                            <div className="font-medium">
                                                {item.name ?? item.product?.name ?? `#${item.product_id}`}
                                            </div>
                                            <div className="text-xs text-gray-500">{t('checkout.summary.quantity', { count: Number(item.qty ?? 0) })}</div>
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
                                <span>{t('checkout.summary.subtotal')}</span>
                                <span className="font-medium">{formatPrice(subtotal)}</span>
                            </div>
                            {discountValue > 0 && (
                                <div className="flex justify-between text-green-600">
                                    <span>{t('checkout.summary.discount')}</span>
                                    <span>-{formatPrice(discountValue)}</span>
                                </div>
                            )}
                            <div className="flex justify-between text-base font-semibold">
                                <span>{t('checkout.summary.total')}</span>
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
                        {t('checkout.summary.notice')}
                    </div>

                    <div className="flex items-center justify-between gap-3">
                        <button
                            type="button"
                            onClick={() => setStep('address')}
                            className="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-50"
                        >
                            {t('common.actions.back')}
                        </button>
                        <button
                            type="button"
                            onClick={handleGoToPayment}
                            disabled={creatingOrder}
                            className="rounded-lg bg-black px-5 py-2 text-white transition hover:bg-black/90 disabled:opacity-60"
                            data-testid="place-order"
                        >
                            {creatingOrder ? t('checkout.summary.creating') : t('checkout.summary.goToPayment')}
                        </button>
                    </div>
                </div>
            )}

            {step === 'payment' && (
                <div className="space-y-6">
                    {!order && (
                        <div className="rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-sm text-yellow-700">
                            {t('checkout.payment.preparing')}
                        </div>
                    )}
                    {order && (
                        <>
                            <div className="space-y-2 rounded-xl border border-gray-200 p-4">
                                <div className="text-sm text-gray-500">{t('checkout.payment.orderNumberLabel')}</div>
                                <div className="text-xl font-semibold">{order.number}</div>
                                <div className="text-sm text-gray-600">{t('checkout.payment.confirmationNotice', { email: order.email })}</div>
                                <div className="text-sm text-gray-600">
                                    {t('checkout.payment.totalNotice', {
                                        amount: formatPrice(order.total, order.currency ?? 'EUR'),
                                    })}
                                </div>
                            </div>

                            <div className="space-y-3 rounded-xl border border-gray-200 p-4">
                                <h2 className="text-lg font-semibold">{t('checkout.payment.title')}</h2>
                                <p className="text-sm text-gray-600">{t('checkout.payment.description')}</p>
                                <PayOrder number={order.number} onPaid={handlePaid} />
                            </div>

                            <div className="space-y-3">
                                <h2 className="text-lg font-semibold">{t('checkout.payment.billingTitle')}</h2>
                                <div className="rounded-xl border border-gray-200 p-4 text-sm text-gray-700">
                                    {order.billing_address ? (
                                        <>
                                            {order.billing_address.company && (
                                                <div className="font-medium">{order.billing_address.company}</div>
                                            )}
                                            {order.billing_address.name && (
                                                <div>{order.billing_address.name}</div>
                                            )}
                                            {order.billing_address.tax_id && (
                                                <div className="text-xs text-gray-500">
                                                    {t('checkout.payment.billingTax', { taxId: order.billing_address.tax_id })}
                                                </div>
                                            )}
                                            {order.billing_address.city && (
                                                <div>{order.billing_address.city}</div>
                                            )}
                                            {order.billing_address.addr && (
                                                <div>{order.billing_address.addr}</div>
                                            )}
                                            {order.billing_address.postal_code && (
                                                <div>{order.billing_address.postal_code}</div>
                                            )}
                                            {order.billing_address.phone && (
                                                <div>{order.billing_address.phone}</div>
                                            )}
                                        </>
                                    ) : (
                                        <div className="text-gray-600">
                                            {t('checkout.payment.billingMatchesShipping')}
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-3">
                                <h2 className="text-lg font-semibold">{t('checkout.payment.shippingTitle')}</h2>
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
                                        {t('checkout.payment.shippingMethod', { method: selectedDelivery.title })}
                                    </div>
                                    {deliveryComment.trim() && (
                                        <div className="text-gray-600">
                                            {t('checkout.payment.shippingComment', { comment: deliveryComment.trim() })}
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-3">
                                <h2 className="text-lg font-semibold">{t('checkout.payment.itemsTitle')}</h2>
                                <div className="divide-y rounded-xl border border-gray-200">
                                    {(order.items ?? []).map((item) => {
                                        const line = item.subtotal ?? Number(item.price ?? 0) * Number(item.qty ?? 0);
                                        return (
                                            <div key={item.id} className="flex items-start justify-between gap-3 p-3">
                                                <div className="flex-1">
                                                    <div className="font-medium">
                                                        {item.name ?? item.product?.name ?? `#${item.product_id}`}
                                                    </div>
                                                    <div className="text-xs text-gray-500">{t('checkout.summary.quantity', { count: Number(item.qty ?? 0) })}</div>
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
