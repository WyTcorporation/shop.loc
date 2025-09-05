import React from 'react';
import {createRoot} from 'react-dom/client';
import {BrowserRouter, Routes, Route, useLocation} from 'react-router-dom';
import CatalogPage from './pages/Catalog';
import ProductPage from './pages/Product';
import CartPage from './pages/Cart';
import CheckoutPage from './pages/Checkout';
import OrderConfirmationPage from './pages/OrderConfirmation';
import { NotifyProvider, useNotify } from './ui/notify';
import { CartProvider } from './useCart';

const el = document.getElementById('shop-root');

function RouteToastAutoClear() {
    const location = useLocation();
    const { clearAll } = useNotify();
    React.useEffect(() => {
        clearAll();                  // прибрати всі тости на кожну зміну шляху
    }, [location.pathname, location.search, clearAll]);
    return null;
}

if (el) {
    createRoot(el).render(
        <React.StrictMode>
            <NotifyProvider  autoCloseMs={0}>
                <CartProvider>
                <BrowserRouter>
                    <RouteToastAutoClear />
                    <Routes>
                        <Route path="/" element={<CatalogPage/>}/>
                        <Route path="/product/:slug" element={<ProductPage/>}/>
                        <Route path="/cart" element={<CartPage/>}/>
                        <Route path="/checkout" element={<CheckoutPage/>}/>
                        <Route path="/order/:number" element={<OrderConfirmationPage/>}/>
                    </Routes>
                </BrowserRouter>
                </CartProvider>
            </NotifyProvider>
        </React.StrictMode>
    );
}
