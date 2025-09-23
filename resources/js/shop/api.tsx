import axios from 'axios';
import { normalizeLang, resolveLocale, type Lang } from './i18n/config';
import type { WishItem } from './ui/wishlist';

const API_BASE = (import.meta as any).env?.VITE_API_URL || 'http://localhost:8080/api';

export const api = axios.create({
    baseURL: API_BASE,
    withCredentials: true, // потрібні cookie (cart_id)
});

export function setApiLocale(lang: Lang) {
    const locale = resolveLocale(lang);

    api.defaults.headers.common['Accept-Language'] = locale;

    const headers = api.defaults.headers as typeof api.defaults.headers & { set?: (name: string, value: string) => void };
    headers.set?.('Accept-Language', locale);
}

const initialDocumentLang = typeof document !== 'undefined' ? document.documentElement.getAttribute('lang') : '';
const initialNavigatorLang = typeof navigator !== 'undefined' ? navigator.language : '';
const initialLangCandidate = initialDocumentLang || initialNavigatorLang;

if (initialLangCandidate) {
    setApiLocale(normalizeLang(initialLangCandidate));
}

/* ==================== TYPES ==================== */
export type AuthUser = {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
    two_factor_enabled?: boolean;
    two_factor_confirmed_at?: string | null;
    [key: string]: unknown;
};

type AuthTokenResponse = {
    token: string;
    user: AuthUser;
};

export type TwoFactorStatus = {
    enabled: boolean;
    pending: boolean;
    confirmed_at?: string | null;
};

export type TwoFactorSetup = {
    secret: string;
    otpauth_url: string;
};

export type Image = {
    id: number;
    url: string;
    alt?: string;
    is_primary?: boolean;
};

export type Vendor = {
    id: number;
    name: string;
    slug?: string | null;
    contact_email?: string | null;
    contact_phone?: string | null;
    description?: string | null;
};

export type ProductAttribute = {
    key?: string | null;
    name?: string | null;
    value?: string | number | null;
    label?: string | null;
    translations?: Record<string, string | { label?: string | null; value?: string | null } | null> | null;
};

export type Product = {
    id: number;
    name: string;
    slug: string;
    category_id: number;
    price: number | string;
    price_old?: number | string | null;
    preview_url?: string | null;
    images?: { url: string; alt?: string; is_primary?: boolean }[];
    currency?: string;
    base_currency?: string;
    price_cents?: number;
    base_price_cents?: number;
    vendor?: Vendor | null;
    attributes?: ProductAttribute[] | Record<string, unknown> | null;
    attrs?: ProductAttribute[] | Record<string, unknown> | null;
    [k: string]: any;
};

export type SearchSuggestion = {
    id: number;
    name: string;
    slug: string;
    preview_url?: string | null;
    price?: number | null;
    currency?: string | null;
};

export type ProductsQuery = {
    page?: number;
    per_page?: number;
    category_id?: number;
    search?: string;
    sort?: 'new' | 'price_asc' | 'price_desc';
    color?: string[];
    size?: string[];
    min_price?: number;
    max_price?: number;
    with_facets?: 0 | 1;
};

export type Category = {
    id: number;
    name: string;
    slug?: string;
};

export type Paginated<T> = {
    data: T[];
    current_page?: number;
    last_page?: number;
    per_page?: number | string;
    total?: number;
    next_page_url?: string | null;
    prev_page_url?: string | null;
    [k: string]: any;
};

export type CartItem = {
    id: number;
    product_id: number;
    name?: string;
    slug?: string;
    image?: string;
    price: number | string;
    qty: number;
    line_total?: number;
    product?: Product;
    vendor?: Vendor | null;
};

export type Cart = {
    id: string;
    status: 'active' | 'ordered' | string;
    items: CartItem[];
    total: number | string;
    currency?: string;
    subtotal?: number | string;
    discounts?: {
        coupon?: { code?: string | null; amount?: number | string | null };
        loyalty_points?: { used?: number | string | null; value?: number | string | null };
        total?: number | string | null;
    };
    available_points?: number;
    max_redeemable_points?: number;
};
export type FacetEntry = {
    value: string;
    label?: string;
    count: number;
    translations?: Record<string, string | null> | null;
};

export type CategoryFacetPayload = Record<string, FacetEntry | number> | FacetEntry[];

export type Facets = Partial<
    Record<string, Record<string, FacetEntry> | CategoryFacetPayload>
>;

export type PaginatedWithFacets<T> = Paginated<T> & {
    facets?: Facets;
};

export type SellerProductsResponse = Paginated<Product> & {
    vendor: Vendor;
};

export type ReviewUser = {
    id: number;
    name: string;
};

export type Review = {
    id: number;
    product_id: number;
    user_id: number;
    rating: number;
    text?: string | null;
    status?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    user?: ReviewUser | null;
};

type ReviewListResponse = {
    data: Review[];
    average_rating: number | string | null;
    reviews_count: number;
};

type ReviewCreatePayload = {
    rating: number;
    text?: string | null;
};

type ReviewCreateResponse = {
    data: Review;
    message?: string;
};

export type Address = {
    id: number;
    name: string;
    city: string;
    addr: string;
    postal_code?: string | null;
    phone?: string | null;
};

export type Shipment = {
    status?: string | null;
    tracking_number?: string | null;
    shipped_at?: string | null;
    delivered_at?: string | null;
};

export type OrderItemResponse = {
    id: number;
    product_id: number;
    qty: number;
    price: number | string;
    subtotal?: number | string;
    preview_url?: string | null;
    name?: string | null;
    product?: Product | null;
};

export type OrderResponse = {
    id: number;
    number: string;
    email: string;
    status?: string;
    payment_status?: string | null;
    subtotal?: number | string;
    total: number | string;
    discount_total?: number | string | null;
    coupon_code?: string | null;
    coupon_discount?: number | string | null;
    loyalty_points_used?: number | string | null;
    loyalty_points_value?: number | string | null;
    currency?: string;
    base_currency?: string;
    items: OrderItemResponse[];
    shipment?: Shipment | null;
    shipping_address?: {
        name?: string;
        city?: string;
        addr?: string;
        postal_code?: string | null;
        phone?: string | null;
    };
    billing_address?: {
        name?: string;
        company?: string | null;
        tax_id?: string | null;
        city?: string | null;
        addr?: string | null;
        postal_code?: string | null;
        phone?: string | null;
    } | null;
    note?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
};

export type OrderMessage = {
    id: number;
    order_id: number;
    user_id: number;
    body: string;
    meta?: Record<string, unknown> | null;
    created_at?: string | null;
    updated_at?: string | null;
    user?: { id: number; name: string } | null;
    is_author?: boolean;
};

export type LoyaltyPointTransaction = {
    id: number;
    order_id?: number | null;
    type?: string | null;
    points: number | string;
    amount?: number | string | null;
    description?: string | null;
    meta?: Record<string, unknown> | null;
    created_at?: string | null;
    updated_at?: string | null;
};

export type LoyaltyPointsResponse = {
    balance: number;
    transactions: LoyaltyPointTransaction[];
    total_earned?: number | string | null;
    total_spent?: number | string | null;
};

/* ==================== AUTH ==================== */
export const AuthApi = {
    async login(payload: { email: string; password: string; remember?: boolean; otp?: string }) {
        const { data } = await api.post<AuthTokenResponse>('/auth/login', payload);
        return data;
    },
    async register(payload: { name: string; email: string; password: string; password_confirmation?: string; [key: string]: unknown }) {
        const { data } = await api.post<AuthTokenResponse>('/auth/register', payload);
        return data;
    },
    async me() {
        const { data } = await api.get<AuthUser>('/auth/me');
        return data;
    },
    async update(payload: { name?: string; email?: string; password?: string | null; password_confirmation?: string | null }) {
        const { data } = await api.put<AuthUser>('/auth/me', payload);
        return data;
    },
    async logout() {
        await api.post('/auth/logout');
    },
    async resendVerification() {
        const { data } = await api.post<{ message?: string }>('/email/resend');
        return data;
    },
    async requestPasswordReset(payload: { email: string }) {
        const { data } = await api.post<{ message?: string }>('/password/email', payload);
        return data;
    },
    async resetPassword(payload: { token: string; email: string; password: string; password_confirmation: string }) {
        const { data } = await api.post<{ message?: string }>('/password/reset', payload);
        return data;
    },
};

export const TwoFactorApi = {
    async status() {
        const { data } = await api.get<TwoFactorStatus>('/profile/two-factor');
        return data;
    },
    async enable() {
        const { data } = await api.post<TwoFactorSetup>('/profile/two-factor');
        return data;
    },
    async confirm(payload: { code: string }) {
        const { data } = await api.post<{ message?: string; confirmed_at?: string | null }>('/profile/two-factor/confirm', payload);
        return data;
    },
    async disable() {
        await api.delete('/profile/two-factor');
    },
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
        sort?: 'new' | 'price_asc' | 'price_desc';
        with_facets?: 0 | 1;
    }) {
        return api.get('/products', { params }).then((r) => r.data);
    },
    show(slug: string) {
        return api.get<Product>(`/products/${encodeURIComponent(slug)}`).then((r) => r.data);
    },
    sellerProducts(vendorId: number | string, params: { page?: number; per_page?: number } = {}) {
        return api
            .get<SellerProductsResponse>(`/seller/${encodeURIComponent(String(vendorId))}/products`, { params })
            .then((r) => r.data);
    },
    related: fetchRelatedProducts,
};

export const CategoriesApi = {
    list() {
        return api.get<Category[]>('/categories').then((r) => r.data);
    },
};

export const AddressesApi = {
    list() {
        return api.get<Address[]>('/profile/addresses').then((r) => r.data);
    },
};

/* Сумісні з існуючим кодом Catalog.tsx обгортки: */
export async function fetchProducts(params: ProductsQuery) {
    const r = await api.get('/products', {
        params: {
            ...params,
            color: params.color && params.color.length ? params.color : undefined,
            size: params.size && params.size.length ? params.size : undefined,
            min_price: params.min_price ?? undefined,
            max_price: params.max_price ?? undefined,
        },
    });
    return r.data as PaginatedWithFacets<Product>;
}

export async function fetchSearchSuggestions(
    query: string,
    options: { signal?: AbortSignal } = {},
): Promise<SearchSuggestion[]> {
    if (!query.trim()) {
        return [];
    }

    const { data } = await api.get<{ data?: SearchSuggestion[] }>('/search/suggestions', {
        params: { q: query },
        signal: options.signal,
    });

    return data?.data ?? [];
}

export async function fetchSellerProducts(
    vendorId: number | string,
    params: { page?: number; per_page?: number } = {},
): Promise<SellerProductsResponse> {
    const { data } = await api.get<SellerProductsResponse>(
        `/seller/${encodeURIComponent(String(vendorId))}/products`,
        { params },
    );
    return data;
}

export async function fetchProductFacets(params: { search?: string; category_id?: number; color?: string[]; size?: string[] }) {
    const sp = new URLSearchParams();
    if (params.search) sp.set('search', params.search);
    if (params.category_id) sp.set('category_id', String(params.category_id));
    (params.color ?? []).forEach((c) => sp.append('filter[color][]', c));
    (params.size ?? []).forEach((s) => sp.append('filter[size][]', s));

    const { data } = await api.get(`/products/facets?${sp.toString()}`);
    return data as {
        facets: {
            ['category_id']?: CategoryFacetPayload;
            ['attrs.color']?: Record<string, FacetEntry>;
            ['attrs.size']?: Record<string, FacetEntry>;
        };
        nbHits: number;
        driver: string;
        error?: string;
    };
}

export async function fetchRelatedProducts(category_id: number, exclude_id?: number, limit = 4): Promise<Product[]> {
    const res = await fetchProducts({ page: 1, per_page: limit + 1, category_id, sort: 'new' });
    const items = res.data ?? [];
    const filtered = exclude_id ? items.filter((p) => p.id !== exclude_id) : items;
    return filtered.slice(0, limit);
}

export async function fetchCategories(): Promise<Category[]> {
    return CategoriesApi.list();
}

/* ==================== REVIEWS ==================== */

export const ReviewsApi = {
    async list(productId: number): Promise<ReviewListResponse> {
        const { data } = await api.get<ReviewListResponse>(`/products/${encodeURIComponent(productId)}/reviews`);
        return data;
    },
    async create(productId: number, payload: ReviewCreatePayload): Promise<ReviewCreateResponse> {
        const { data } = await api.post<ReviewCreateResponse>(`/products/${encodeURIComponent(productId)}/reviews`, payload);
        return data;
    },
};

/* ==================== WISHLIST ==================== */

export const WishlistApi = {
    async list(): Promise<WishItem[]> {
        const { data } = await api.get<WishItem[]>('/profile/wishlist');
        return data;
    },
    async add(productId: number): Promise<WishItem> {
        const { data } = await api.post<WishItem>(`/profile/wishlist/${encodeURIComponent(productId)}`);
        return data;
    },
    async remove(productId: number): Promise<void> {
        await api.delete(`/profile/wishlist/${encodeURIComponent(productId)}`);
    },
};

/* ==================== CART ==================== */
let activeCartId: string | null = null;
const setActiveCartId = (id: string) => {
    activeCartId = id;
};

export function resetCartCache() {
    activeCartId = null;
}

export const CART_KEY = 'cart_id';
export async function ensureCartId() {
    return requireCartId();
}
export async function fetchCartById(id: string) {
    const { data } = await api.get<Cart>(`/cart/${id}`);
    setActiveCartId(data.id);
    return data;
}

async function getCart(): Promise<Cart> {
    const { data } = await api.get<Cart>('/cart');
    setActiveCartId(data.id);
    return data;
}
async function showCart(id: string): Promise<Cart> {
    const { data } = await api.get<Cart>(`/cart/${id}`);
    setActiveCartId(data.id);
    return data;
}
async function requireCartId(): Promise<string> {
    if (activeCartId) {
        try {
            const existingCart = await showCart(activeCartId);
            if (String(existingCart?.status ?? '') === 'active') {
                return existingCart.id;
            }
        } catch (error) {
            // Якщо відбулася помилка — створюємо новий кошик
        }
        resetCartCache();
    }

    const { data } = await api.get<Cart>('/cart'); // створить активний кошик і поверне id
    setActiveCartId(data.id);
    return data.id;
}

async function addToCart(product_id: number, qty = 1): Promise<Cart> {
    const id = await requireCartId();
    const { data } = await api.post<Cart>(`/cart/${id}/items`, { product_id, qty });
    setActiveCartId(data.id);
    return data;
}
async function updateCartItem(item_id: number, qty: number): Promise<Cart> {
    const id = await requireCartId();
    const { data } = await api.patch<Cart>(`/cart/${id}/items/${item_id}`, { qty });
    return data;
}
async function removeCartItem(item_id: number): Promise<Cart> {
    const id = await requireCartId();
    const { data } = await api.delete<Cart>(`/cart/${id}/items/${item_id}`);
    return data;
}
async function applyCouponToCart(code?: string | null): Promise<Cart> {
    const cart_id = await requireCartId();
    const { data } = await api.post<Cart>('/cart/apply-coupon', {
        cart_id,
        code: code ?? null,
    });
    setActiveCartId(data.id);
    return data;
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
    applyCoupon: applyCouponToCart,
    refresh: refreshCart,
};

/* ==================== ORDERS ==================== */

export const OrdersApi = {
    async create(payload: {
        email: string;
        shipping_address: {
            name: string;
            city: string;
            addr: string;
            postal_code?: string | null;
            phone?: string | null;
        };
        billing_address?: {
            name: string;
            company?: string | null;
            tax_id?: string | null;
            city: string;
            addr: string;
            postal_code?: string | null;
            phone?: string | null;
        } | null;
        note?: string;
    }): Promise<OrderResponse> {
        const cart_id = await requireCartId();
        const { data } = await api.post<OrderResponse>('/orders', { cart_id, ...payload });
        return data;
    },
    show(number: string) {
        return api.get<OrderResponse>(`/orders/${encodeURIComponent(number)}`).then((r) => r.data);
    },
    listMine() {
        return api.get<OrderResponse[]>('/profile/orders').then((r) => r.data);
    },
    listMessages(orderId: number | string) {
        return api
            .get<{ data: OrderMessage[] }>(`/orders/${encodeURIComponent(String(orderId))}/messages`)
            .then((r) => r.data.data ?? []);
    },
    sendMessage(orderId: number | string, body: string) {
        return api
            .post<OrderMessage>(`/orders/${encodeURIComponent(String(orderId))}/messages`, { body })
            .then((r) => r.data);
    },
};

export async function refreshOrderStatus(number: string, payment_intent?: string) {
    const res = await fetch(`/api/payment/refresh/${encodeURIComponent(number)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ payment_intent }),
    });
    if (!res.ok) throw new Error('refresh failed');
    return res.json();
}

export const ProfileApi = {
    fetchPoints() {
        return api.get<LoyaltyPointsResponse>('/profile/points').then((r) => r.data);
    },
};
