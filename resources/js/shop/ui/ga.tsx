import type { Product } from '../api';

type GAItem = {
    item_id: string | number;
    item_name: string;
    price?: number;
    quantity?: number;
    item_category?: string;
};

function detectCurrency(): string {
    if (typeof document !== 'undefined') {
        const currency = document.documentElement.dataset.baseCurrency;

        if (currency) {
            return currency.trim().toUpperCase();
        }
    }

    if (typeof globalThis !== 'undefined' && typeof (globalThis as Record<string, any>).APP === 'object') {
        const appCurrency = (globalThis as Record<string, any>).APP?.baseCurrency;

        if (typeof appCurrency === 'string' && appCurrency.trim()) {
            return appCurrency.trim().toUpperCase();
        }
    }

    return 'EUR';
}

const CURRENCY = detectCurrency();

// Безпечний шорткат: якщо gtag не ініціалізовано — нічого не робимо.
function gtagSafe(...args: any[]) {
    // @ts-ignore
    if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
        // @ts-ignore
        window.gtag(...args);
    }
}

function mapProduct(p: Product): GAItem {
    return {
        item_id: p.id,
        item_name: p.name,
        price: Number(p.price) || 0,
        item_category: p.category_id ? String(p.category_id) : undefined,
    };
}

function mapCartItem(ci: any): GAItem {
    return {
        item_id: ci.product_id ?? ci.id,
        item_name: ci.name ?? ci.product?.name ?? `#${ci.product_id}`,
        price: Number(ci.price) || 0,
        quantity: Number(ci.qty) || 1,
    };
}

export const GA = {
    view_item(product: Product) {
        gtagSafe('event', 'view_item', {
            currency: CURRENCY,
            value: Number(product.price) || 0,
            items: [mapProduct(product)],
        });
    },

    add_to_cart(product: Product, quantity: number) {
        gtagSafe('event', 'add_to_cart', {
            currency: CURRENCY,
            value: (Number(product.price) || 0) * (Number(quantity) || 1),
            items: [{ ...mapProduct(product), quantity: Number(quantity) || 1 }],
        });
    },

    view_item_list(products: Product[], list_name = 'Каталог') {
        gtagSafe('event', 'view_item_list', {
            item_list_name: list_name,
            items: products.map(mapProduct),
        });
    },

    view_cart(cart: any) {
        gtagSafe('event', 'view_cart', {
            currency: CURRENCY,
            value: Number(cart?.total) || 0,
            items: (cart?.items ?? []).map(mapCartItem),
        });
    },

    begin_checkout(cart: any) {
        gtagSafe('event', 'begin_checkout', {
            currency: CURRENCY,
            value: Number(cart?.total) || 0,
            items: (cart?.items ?? []).map(mapCartItem),
        });
    },

    purchase(order: any) {
        gtagSafe('event', 'purchase', {
            transaction_id: order?.number ?? order?.id ?? String(Date.now()),
            currency: CURRENCY,
            value: Number(order?.total) || 0,
            items: (order?.items ?? []).map(mapCartItem),
        });
    },
};
