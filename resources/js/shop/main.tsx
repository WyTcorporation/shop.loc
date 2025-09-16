import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Route, Routes, useLocation } from 'react-router-dom';
import '../../css/app.css';
import CookieConsent from './components/CookieConsent';
import Header from './components/Header';
import JsonLd from './components/JsonLd';
import { AuthProvider } from './hooks/useAuth';
import { WishlistProvider } from './hooks/useWishlist';
import LocaleProvider from './i18n/LocaleProvider';
import { normalizeLang } from './i18n/config';
import { initMonitoring } from './monitoring';
import CartPage from './pages/Cart';
import CatalogPage from './pages/Catalog';
import CheckoutPage from './pages/Checkout';
import LoginPage from './pages/Login';
import NotFoundPage from './pages/NotFound';
import OrderConfirmationPage from './pages/OrderConfirmation';
import ProductPage from './pages/Product';
import ProfilePage from './pages/Profile';
import ProfileAddressesPage from './pages/ProfileAddresses';
import ProfileOrdersPage from './pages/ProfileOrders';
import ProfilePointsPage from './pages/ProfilePoints';
import RegisterPage from './pages/Register';
import WishlistPage from './pages/Wishlist';
import './sentry';
import { AppErrorBoundary } from './ui/ErrorBoundary';
import { initAnalyticsOnLoad } from './ui/analytics';
import { NotifyProvider, useNotify } from './ui/notify';
import { CartProvider } from './useCart';

initAnalyticsOnLoad();

initMonitoring();

const el = document.getElementById('shop-root');

const origin = typeof window !== 'undefined' ? window.location.origin : '';

function LangGate({ children }: { children: any }) {
    const seg1 = typeof window !== 'undefined' ? window.location.pathname.split('/').filter(Boolean)[0] : '';
    const lang = normalizeLang(seg1);
    return <LocaleProvider initial={lang}>{children}</LocaleProvider>;
}

const websiteLd = {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'Shop',
    url: origin || undefined,
    potentialAction: {
        '@type': 'SearchAction',
        target: `${origin}/?q={search_term_string}`,
        'query-input': 'required name=search_term_string',
    },
};

const orgLd = {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'Shop',
    url: origin || undefined,
    logo: origin ? `${origin}/logo.png` : undefined,
    sameAs: [
        // профілі соцмереж за потреби
        'https://www.facebook.com/yourpage',
        'https://www.instagram.com/yourpage',
    ],
    contactPoint: [
        {
            '@type': 'ContactPoint',
            telephone: '+380-00-000-0000',
            contactType: 'customer service',
            areaServed: 'UA',
            availableLanguage: ['uk'],
        },
    ],
};

function RouteToastAutoClear() {
    const location = useLocation();
    const { clearAll } = useNotify();
    React.useEffect(() => {
        clearAll();
    }, [location.pathname, location.search, clearAll]);
    return null;
}

if (el) {
    createRoot(el).render(
        <React.StrictMode>
            <NotifyProvider autoCloseMs={0}>
                <AuthProvider>
                    <CartProvider>
                        <WishlistProvider>
                            <LangGate>
                                <BrowserRouter>
                                    <AppErrorBoundary>
                                        <RouteToastAutoClear />
                                        <CookieConsent />
                                        <Header />
                                        <JsonLd data={websiteLd} />
                                        <JsonLd data={orgLd} />
                                        <Routes>
                                            <Route path="/" element={<CatalogPage />} />
                                            <Route path="/product/:slug" element={<ProductPage />} />
                                            <Route path="/cart" element={<CartPage />} />
                                            <Route path="/checkout" element={<CheckoutPage />} />
                                            <Route path="/order/:number" element={<OrderConfirmationPage />} />
                                            <Route path="/wishlist" element={<WishlistPage />} />
                                            <Route path="/login" element={<LoginPage />} />
                                            <Route path="/register" element={<RegisterPage />} />
                                            <Route path="/profile" element={<ProfilePage />} />
                                            <Route path="/profile/orders" element={<ProfileOrdersPage />} />
                                            <Route path="/profile/addresses" element={<ProfileAddressesPage />} />
                                            <Route path="/profile/points" element={<ProfilePointsPage />} />
                                            <Route path="*" element={<NotFoundPage />} />
                                        </Routes>
                                    </AppErrorBoundary>
                                </BrowserRouter>
                            </LangGate>
                        </WishlistProvider>
                    </CartProvider>
                </AuthProvider>
            </NotifyProvider>
        </React.StrictMode>,
    );
}
