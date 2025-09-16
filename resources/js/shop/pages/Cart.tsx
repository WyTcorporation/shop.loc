import React, {useEffect} from 'react'
import { Link } from 'react-router-dom'
import useCart from '../useCart'
import SeoHead from '../components/SeoHead';
import { GA } from '../ui/ga';

function money(v: unknown) {
    return new Intl.NumberFormat('uk-UA', { style: 'currency', currency: 'EUR', maximumFractionDigits: 2 })
        .format(Number(v ?? 0));
}

export default function CartPage() {
    const { cart, total, update, remove } = useCart()

    if (!cart) return <div className="max-w-4xl mx-auto p-6">Loading…</div>

    useEffect(() => {
        GA.view_cart(cart);
    }, [cart]);

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-6">
            <SeoHead title="Кошик — Shop" robots="noindex,nofollow" canonical />
            <h1 className="text-2xl font-semibold">Cart</h1>

            {cart.items.length === 0 && (
                <div className="text-gray-600">Cart is empty. <Link to="/" className="underline">Go shopping</Link></div>
            )}

            <div className="space-y-3">
                {cart.items.map(it => {
                    const p = (it as any).product ?? it; // 🔁 підтримка обох форм
                    const preview = p.preview_url ?? p.images?.[0]?.url;
                    const line = Number(p.price || it.price || 0) * Number(it.qty || 0);
                    const vendor = (p as any).vendor ?? (it as any).vendor ?? null;
                    return (
                        <div key={it.id} className="flex items-center gap-3 border rounded p-3">
                            {preview ? <img src={preview} className="w-16 h-16 object-cover rounded" /> : <div className="w-16 h-16 bg-gray-100 rounded" />}
                            <div className="flex-1">
                                <div className="font-medium">{p.name ?? it.name}</div>
                                <div className="text-sm text-gray-600">{money(p.price ?? it.price)}</div>
                                {vendor && (
                                    <div className="mt-1 text-xs text-gray-500">
                                        Продавець: {vendor.name ?? '—'}{' '}
                                        {vendor.id && (
                                            <Link className="text-blue-600 hover:underline" to={`/seller/${vendor.id}`}>
                                                Написати продавцю
                                            </Link>
                                        )}
                                    </div>
                                )}
                            </div>
                            <input
                                type="number"
                                min={0}
                                value={it.qty}
                                onChange={e => update(it.id, Math.max(0, parseInt(e.target.value || '0', 10)))}
                                className="border rounded px-2 py-1 w-20"
                            />
                            <div className="w-24 text-right font-medium">{money(line)}</div>
                            <button onClick={()=>remove(it.id)} className="px-2 py-1 text-sm rounded border">Remove</button>
                        </div>
                    );
                })}
            </div>

            <div className="flex justify-between items-center border-t pt-4">
                <div className="text-lg">Total:</div>
                <div className="text-xl font-semibold">{money(total)}</div>
            </div>

            <div className="flex justify-end">
                <Link
                    to="/checkout"
                    data-testid="go-checkout"
                    className="px-5 py-2 rounded bg-black text-white disabled:opacity-50"
                    aria-disabled={cart.items.length===0}
                    onClick={e=>{ if(cart.items.length===0){e.preventDefault()} }}
                >
                    Checkout
                </Link>
            </div>
        </div>
    )
}
