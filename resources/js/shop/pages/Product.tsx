import React, { useEffect, useState } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { ProductsApi, Product } from '../api'
import useCart from '../useCart'
import { useNotify } from '../ui/notify'

export default function ProductPage() {
    const { slug } = useParams()
    const [p, setP] = useState<Product | null>(null)
    const [qty, setQty] = useState(1)
    const { add } = useCart()
    const { success, error } = useNotify()
    const navigate = useNavigate()

    const money = (v: unknown) =>
        new Intl.NumberFormat('uk-UA', { style: 'currency', currency: 'EUR' })
            .format(Number(v ?? 0));

    useEffect(() => { if (slug) ProductsApi.show(slug).then(setP) }, [slug])

    if (!p) return <div className="max-w-4xl mx-auto p-6">Loading…</div>

    async function handleAdd() {
        try {
            await add(p.id, qty)
            success('Додано до кошика', {
                action: { label: 'Відкрити кошик', onClick: () => navigate('/cart') },
                ttl: 0,
            });
        } catch (e: any) {
            const msg = e?.response?.data?.message ?? 'Не вдалося додати товар'
            error('Помилка', { description: msg })
        }
    }

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-6">
            <Link to="/" className="text-sm text-gray-500">&larr; Back</Link>
            <div className="grid md:grid-cols-2 gap-6">
                {p.preview_url
                    ? <img src={p.preview_url} alt={p.name} className="w-full h-80 object-cover rounded"/>
                    : <div className="w-full h-80 bg-gray-100 rounded"/>}
                <div className="space-y-4">
                    <h1 className="text-2xl font-semibold">{p.name}</h1>
                    <div className="text-lg">{money(p.price)}</div>
                    <div className="flex items-center gap-2">
                        <input type="number" min={1} value={qty}
                               onChange={e=>setQty(parseInt(e.target.value || '1',10))}
                               className="border rounded px-2 py-1 w-20"/>
                        <button onClick={handleAdd} className="px-4 py-2 rounded bg-black text-white">
                            Add to cart
                        </button>
                    </div>
                    <Link to="/cart" className="inline-block text-sm underline">Go to cart</Link>
                </div>
            </div>
        </div>
    )
}
