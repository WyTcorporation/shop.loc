import axios from 'axios';

export const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
});


export interface Category {
    id: number;
    name: string;
    slug?: string;
    parent_id?: number | null;
}

export interface Product {
    id: number;
    name: string;
    price: number;
    slug?: string | null;
    preview_url?: string | null;
    description?: string | null;
    images?: Array<{ url: string; is_primary?: boolean; alt?: string | null }>;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface CartItem {
    id: number;
    product_id: number;
    name?: string;
    price: number;
    qty: number;
    subtotal: number;
    preview_url?: string | null;
}

export interface Cart {
    id?: number;
    items: CartItem[];
    total: number;
    updated_at?: string;
}

export interface CreateOrderPayload {
    email: string;
    shipping_address?: Record<string, unknown>;
    items?: Array<{ product_id: number; quantity: number }>;
}

export interface Order {
    id: number;
    number: string;
    email: string;
    total: number;
    status: string;
}


export async function fetchCategories() {
    const { data } = await api.get<Category[]>('/categories');
    return data;
}

export async function fetchProducts(params?: {
    page?: number;
    per_page?: number;
    category_id?: number;
    search?: string;
    sort?: 'price_asc' | 'price_desc' | 'new';
}) {
    const { data } = await api.get<Paginated<Product>>('/products', { params });
    return data;
}

export const ProductsApi = {
    list: (q?: string) =>
        api.get<{ data: Product[] }>('/products', { params: { q } }).then(r => r.data.data),
    show: (slug: string) =>
        api.get<Product>(`/products/${slug}`).then(r => r.data), // без пробілів у шаблоні
};


type AnyCart = { id?: string|number; items?: any[]; total?: any };

function normalizeCart(raw: AnyCart): Cart {
    const byProduct = new Map<number, CartItem>();
    for (const it of raw.items ?? []) {
        const id  = Number(it.id ?? it.item_id ?? 0);
        const pid = Number(it.product_id ?? it.product?.id ?? 0);
        const price = Number(it.price ?? it.product?.price ?? 0);
        const qty = Number(it.qty ?? it.quantity ?? 0) || 0;
        const name = it.name ?? it.product?.name ?? '';
        const preview = it.preview_url ?? it.product?.preview_url ?? null;

        const prev = byProduct.get(pid);
        if (prev) {
            prev.qty += qty;
            prev.subtotal = prev.qty * prev.price;
        } else {
            byProduct.set(pid, { id, product_id: pid, name, price, qty, subtotal: price*qty, preview_url: preview });
        }
    }

    const items = [...byProduct.values()];
    const total = Number(raw.total ?? items.reduce((s, i) => s + (Number(i.subtotal) || 0), 0));
    return { id: raw.id as any, items, total, updated_at: (raw as any).updated_at ?? null };
}

async function ensureCartId(): Promise<string|number> {
    const { data } = await api.get('/cart');
    return (data as any).id;
}

export const CartApi = {
    get: async (): Promise<Cart> => normalizeCart((await api.get('/cart')).data),

    add: async (product_id: number, qty = 1): Promise<Cart> => {
        const id = await ensureCartId();
        const { data } = await api.post(`/cart/${id}/items`, { product_id, qty });
        return normalizeCart(data);
    },

    update: async (item_id: number, qty: number): Promise<Cart> => {
        const id = await ensureCartId();
        const { data } = await api.patch(`/cart/${id}/items/${item_id}`, { qty });
        if (data?.removed) {
            return CartApi.get();
        }
        return normalizeCart(data);
    },

    remove: async (item_id: number): Promise<Cart> => {
        const id = await ensureCartId();
        const { data } = await api.delete(`/cart/${id}/items/${item_id}`);
        return normalizeCart(data);
    },
};

export const OrdersApi = {
    create: async (payload: { email: string; shipping_address: any }) => {
        const { data: cart } = await api.get('/cart');
        const cart_id = cart.id;
        return (await api.post('/orders', { cart_id, ...payload })).data;
    },
    show: async (number: string) => (await api.get(`/orders/${number}`)).data,
};
