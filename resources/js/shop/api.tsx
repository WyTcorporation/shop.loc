import axios from 'axios'

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
    images?: Image[]
    [k: string]: any
}

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

/* ==================== PRODUCTS / CATEGORIES ==================== */
export const ProductsApi = {
    list(params: {
        page?: number
        per_page?: number
        category_id?: number
        sort?: 'price_asc' | 'price_desc' | 'new' | string
        search?: string
    }) {
        // бек спокійно проігнорує undefined-параметри
        return api.get<Paginated<Product>>('/products', { params }).then(r => r.data)
    },
    show(slug: string) {
        return api.get<Product>(`/products/${encodeURIComponent(slug)}`).then(r => r.data)
    },
}

export const CategoriesApi = {
    list() {
        return api.get<Category[]>('/categories').then(r => r.data)
    },
}

/* Сумісні з існуючим кодом Catalog.tsx обгортки: */
export async function fetchProducts(params: {
    page?: number
    per_page?: number
    category_id?: number
    sort?: 'price_asc' | 'price_desc' | 'new' | string
    search?: string
}): Promise<Paginated<Product>> {
    return ProductsApi.list(params)
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
