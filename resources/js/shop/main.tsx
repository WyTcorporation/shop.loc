import React from 'react';
import {createRoot} from 'react-dom/client';
import {BrowserRouter, Routes, Route, useLocation} from 'react-router-dom';
import CatalogPage from './pages/Catalog';
import ProductPage from './pages/Product';
import CartPage from './pages/Cart';
import CheckoutPage from './pages/Checkout';
import OrderConfirmationPage from './pages/OrderConfirmation';
import {NotifyProvider, useNotify} from './ui/notify';
import {CartProvider} from './useCart';
import Header from './components/Header';
import {WishlistProvider} from './hooks/useWishlist';
import WishlistPage from './pages/Wishlist';
import '../../css/app.css';
import {AppErrorBoundary} from './ui/ErrorBoundary';
import JsonLd from './components/JsonLd';
import CookieConsent from './components/CookieConsent';
import { initAnalyticsOnLoad } from './ui/analytics';
import { initMonitoring } from './monitoring';
import NotFoundPage from './pages/NotFound';

initAnalyticsOnLoad();

initMonitoring();

const el = document.getElementById('shop-root');

const origin = typeof window !== 'undefined' ? window.location.origin : '';

const websiteLd = {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'Shop',
    url: origin || undefined,
    potentialAction: {
        '@type': 'SearchAction',
        target: `${origin}/?q={search_term_string}`,
        'query-input': 'required name=search_term_string'
    }
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
    contactPoint: [{
        '@type': 'ContactPoint',
        telephone: '+380-00-000-0000',
        contactType: 'customer service',
        areaServed: 'UA',
        availableLanguage: ['uk']
    }]
};

function RouteToastAutoClear() {
    const location = useLocation();
    const {clearAll} = useNotify();
    React.useEffect(() => {
        clearAll();
    }, [location.pathname, location.search, clearAll]);
    return null;
}

if (el) {
    createRoot(el).render(
        <React.StrictMode>
            <NotifyProvider autoCloseMs={0}>
                <CartProvider>
                    <WishlistProvider>
                        <BrowserRouter>
                            <AppErrorBoundary>
                                <RouteToastAutoClear/>
                                <CookieConsent />
                                <Header/>
                                <JsonLd data={websiteLd} />
                                <JsonLd data={orgLd} />
                                <Routes>
                                    <Route path="/" element={<CatalogPage/>}/>
                                    <Route path="/product/:slug" element={<ProductPage/>}/>
                                    <Route path="/cart" element={<CartPage/>}/>
                                    <Route path="/checkout" element={<CheckoutPage/>}/>
                                    <Route path="/order/:number" element={<OrderConfirmationPage/>}/>
                                    <Route path="/wishlist" element={<WishlistPage/>}/>
                                    <Route path="*" element={<NotFoundPage/>}/>
                                </Routes>
                            </AppErrorBoundary>
                        </BrowserRouter>
                    </WishlistProvider>
                </CartProvider>
            </NotifyProvider>
        </React.StrictMode>
    );
}
