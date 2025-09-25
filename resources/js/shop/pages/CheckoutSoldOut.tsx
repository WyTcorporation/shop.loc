import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import SeoHead from '../components/SeoHead';
import { useLocale } from '../i18n/LocaleProvider';

type LocationState = {
    productName?: string;
    message?: string;
};

export default function CheckoutSoldOutPage() {
    const { t } = useLocale();
    const nav = useNavigate();
    const { state } = useLocation() as { state?: LocationState };

    const brand = t('header.brand');
    const productName = state?.productName;
    const serverMessage = state?.message;

    const description = React.useMemo(() => {
        if (typeof serverMessage === 'string' && serverMessage.trim()) {
            return serverMessage;
        }

        if (productName) {
            return t('checkout.soldOutPage.descriptionWithProduct', { product: productName });
        }

        return t('checkout.soldOutPage.description');
    }, [productName, serverMessage, t]);

    return (
        <div className="mx-auto max-w-3xl p-6 text-center space-y-6">
            <SeoHead
                title={t('checkout.soldOutPage.seoTitle', { brand })}
                robots="noindex,nofollow"
                canonical
            />
            <h1 className="text-3xl font-semibold">{t('checkout.soldOutPage.title')}</h1>
            <p className="text-gray-600">{description}</p>
            <div className="flex flex-wrap justify-center gap-3">
                <button
                    type="button"
                    className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:text-gray-900"
                    onClick={() => nav('/cart', { replace: true })}
                >
                    {t('checkout.soldOutPage.backToCart')}
                </button>
                <button
                    type="button"
                    className="rounded-lg bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-900"
                    onClick={() => nav('/', { replace: true })}
                >
                    {t('checkout.soldOutPage.backToCatalog')}
                </button>
            </div>
        </div>
    );
}
