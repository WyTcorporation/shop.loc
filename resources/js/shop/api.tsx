import axios from 'axios'
import { normalizeLang } from './i18n/config';

const API_BASE =
    (import.meta as any).env?.VITE_API_URL ||
    'http://localhost:8080/api'

export const api = axios.create({
    baseURL: API_BASE,
    withCredentials: true, // потрібні cookie (cart_id)
})

/* ==================== TYPES ==================== */
export type Image = {
    id: number
    url: string
    alt?: string
    is_primary?: boolean
}

export type Product = {
    id: number
    name: string
    slug: string
    category_id: number
    price: number | string
    price_old?: number | string | null
    preview_url?: string | null
    images?: { url: string; alt?: string; is_primary?: boolean }[];
    [k: string]: any
}

export type ProductsQuery = {
    page?: number;
    per_page?: number;
    category_id?: number;
    search?: string;
    sort?: 'new'|'price_asc'|'price_desc';
    color?: string[];
    size?: string[];
    min_price?: number;
    max_price?: number;
    with_facets?: 0|1;
};

export type Category = {
    id: number
    name: string
    slug?: string
}

export type Paginated<T> = {
    data: T[]
    current_page?: number
    last_page?: number
    per_page?: number | string
    total?: number
    next_page_url?: string | null
    prev_page_url?: string | null
    [k: string]: any
}

export type CartItem = {
    id: number
    product_id: number
    name?: string
    slug?: string
    image?: string
    price: number | string
    qty: number
    line_total?: number
    product?: Product
}

export type Cart = {
    id: string
    status: 'active' | 'ordered' | string
    items: CartItem[]
    total: number
}
export type Facets = Record<string, Record<string, number>>;

export type PaginatedWithFacets<T> = Paginated<T> & {
    facets?: Facets;
};

/* ==================== PRODUCTS / CATEGORIES ==================== */
export const ProductsApi = {
    // list(params: {
    //     page?: number
    //     per_page?: number
    //     category_id?: number
    //     sort?: 'price_asc' | 'price_desc' | 'new' | string
    //     search?: string
    // }) {
    //     // бек спокійно проігнорує undefined-параметри
    //     return api.get<Paginated<Product>>('/products', { params }).then(r => r.data)
    // },
    list(params: {
        page?: number;
        per_page?: number;
        search?: string;
        category_id?: number;
        sort?: 'new'|'price_asc'|'price_desc';
        with_facets?: 0|1;
    }) {
        return api.get('/products', { params }).then(r => r.data);
    },
    show(slug: string) {
        return api.get<Product>(`/products/${encodeURIComponent(slug)}`).then(r => r.data)
    },
    related: fetchRelatedProducts,
}

export const CategoriesApi = {
    list() {
        return api.get<Category[]>('/categories').then(r => r.data)
    },
}

/* Сумісні з існуючим кодом Catalog.tsx обгортки: */
export async function fetchProducts(params: ProductsQuery) {
    const r = await api.get('/products', {
        params: {
            ...params,
            color: params.color && params.color.length ? params.color : undefined,
            size:  params.size  && params.size.length  ? params.size  : undefined,
            min_price: params.min_price ?? undefined,
            max_price: params.max_price ?? undefined,
        }
    });
    return r.data as PaginatedWithFacets<Product>;
}

export async function fetchProductFacets(params: {
    search?: string;
    category_id?: number;
    color?: string[];
    size?: string[];
}) {
    const sp = new URLSearchParams();
    if (params.search) sp.set('search', params.search);
    if (params.category_id) sp.set('category_id', String(params.category_id));
    (params.color ?? []).forEach(c => sp.append('filter[color][]', c));
    (params.size  ?? []).forEach(s => sp.append('filter[size][]', s));

    const { data } = await api.get(`/products/facets?${sp.toString()}`);
    return data as {
        facets: {
            ['category_id']?: Record<string, number>;
            ['attrs.color']?: Record<string, number>;
            ['attrs.size']?:  Record<string, number>;
        };
        nbHits: number;
        driver: string;
        error?: string;
    };
}

export async function fetchRelatedProducts(
    category_id: number,
    exclude_id?: number,
    limit = 4
): Promise<Product[]> {
    const res = await fetchProducts({ page: 1, per_page: limit + 1, category_id, sort: 'new' });
    const items = res.data ?? [];
    const filtered = exclude_id ? items.filter(p => p.id !== exclude_id) : items;
    return filtered.slice(0, limit);
}


export async function fetchCategories(): Promise<Category[]> {
    return CategoriesApi.list()
}

/* ==================== CART ==================== */
let activeCartId: string | null = null
const setActiveCartId = (id: string) => { activeCartId = id }

export function resetCartCache() {
    activeCartId = null;
}


export const CART_KEY = 'cart_id'
export async function ensureCartId() { return requireCartId() }
export async function fetchCartById(id: string) {
    const { data } = await api.get<Cart>(`/cart/${id}`)
    setActiveCartId(data.id)
    return data
}

async function getCart(): Promise<Cart> {
    const { data } = await api.get<Cart>('/cart')
    setActiveCartId(data.id)
    return data
}
async function showCart(id: string): Promise<Cart> {
    const { data } = await api.get<Cart>(`/cart/${id}`)
    setActiveCartId(data.id)
    return data
}
async function requireCartId(): Promise<string> {
    if (activeCartId) return activeCartId
    const { data } = await api.get<Cart>('/cart') // створить активний кошик і поверне id
    setActiveCartId(data.id)
    return data.id
}

async function addToCart(product_id: number, qty = 1): Promise<Cart> {
    const id = await requireCartId()
    const { data } = await api.post<Cart>(`/cart/${id}/items`, { product_id, qty })
    setActiveCartId(data.id)
    return data
}
async function updateCartItem(item_id: number, qty: number): Promise<Cart> {
    const id = await requireCartId()
    const { data } = await api.patch<Cart>(`/cart/${id}/items/${item_id}`, { qty })
    return data
}
async function removeCartItem(item_id: number): Promise<Cart> {
    const id = await requireCartId()
    const { data } = await api.delete<Cart>(`/cart/${id}/items/${item_id}`)
    return data
}
async function refreshCart(): Promise<Cart> {
    try {
        if (!activeCartId) return getCart();
        const c = await showCart(activeCartId);
        if ((c as any)?.status && String(c.status) !== 'active') {
            activeCartId = null;
            return getCart();
        }
        return c;
    } catch {
        activeCartId = null;
        return getCart();
    }
}

export const CartApi = {
    get: getCart,
    show: showCart,
    add: addToCart,
    update: updateCartItem,
    remove: removeCartItem,
    refresh: refreshCart,
}

/* ==================== ORDERS ==================== */

export const OrdersApi = {
    async create(payload: {
        email: string
        shipping_address: { name: string; city: string; addr: string }
        note?: string
    }) {
        const cart_id = await requireCartId()
        const { data } = await api.post('/orders', { cart_id, ...payload })
        return data
    },
    show(number: string) {
        return api.get(`/orders/${encodeURIComponent(number)}`).then(r => r.data)
    },
}

export async function refreshOrderStatus(number: string, payment_intent?: string) {
    const res = await fetch(`/api/payment/refresh/${encodeURIComponent(number)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ payment_intent }),
    });
    if (!res.ok) throw new Error('refresh failed');
    return res.json();
}
