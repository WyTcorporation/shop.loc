import React, {useEffect} from 'react'
import { Link } from 'react-router-dom'
import useCart from '../useCart'
import SeoHead from '../components/SeoHead';
import { GA } from '../ui/ga';
import { useLocale } from '../i18n/LocaleProvider';
import { formatCurrency } from '../ui/format';

export default function CartPage() {
    const { cart, total, update, remove } = useCart()
    const { t, locale } = useLocale();

    const brand = t('header.brand');

    if (!cart) return <div className="max-w-4xl mx-auto p-6">{t('cart.loading')}</div>

    const currency = cart.currency ?? 'EUR';

    useEffect(() => {
        GA.view_cart(cart);
    }, [cart]);

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-6">
            <SeoHead title={t('cart.seoTitle', { brand })} robots="noindex,nofollow" canonical />
            <h1 className="text-2xl font-semibold">{t('cart.title')}</h1>

            {cart.items.length === 0 && (
                <div className="text-gray-600">
                    {t('cart.empty.message')}{' '}
                    <Link to="/" className="underline">{t('cart.empty.cta')}</Link>
                </div>
            )}

            <div className="space-y-3">
                {cart.items.map(it => {
                    const embeddedProduct = (it as any).product ?? null;
                    const p = embeddedProduct ?? it; // 🔁 підтримка обох форм
                    const preview = p.preview_url ?? p.images?.[0]?.url;
                    const line = Number(p.price || it.price || 0) * Number(it.qty || 0);
                    const vendor = (p as any).vendor ?? (it as any).vendor ?? null;
                    const slug = embeddedProduct?.slug ?? p.slug ?? (it as any).slug ?? null;
                    const productUrl = slug ? `/product/${slug}` : null;
                    const namePriceContent = (
                        <>
                            {preview ? (
                                <img src={preview} className="w-16 h-16 object-cover rounded shrink-0" />
                            ) : (
                                <div className="w-16 h-16 bg-gray-100 rounded shrink-0" />
                            )}
                            <div className="flex-1 min-w-0">
                                <div className="font-medium truncate">{p.name ?? it.name}</div>
                                <div className="text-sm text-gray-600">
                                    {formatCurrency(p.price ?? it.price, { currency, locale })}
                                </div>
                            </div>
                        </>
                    );
                    return (
                        <div key={it.id} className="flex items-center gap-3 border rounded p-3">
                            <div className="flex flex-col flex-1 min-w-0 gap-1">
                                {productUrl ? (
                                    <Link to={productUrl} className="flex items-center gap-3 min-w-0">
                                        {namePriceContent}
                                    </Link>
                                ) : (
                                    <div className="flex items-center gap-3 min-w-0">
                                        {namePriceContent}
                                    </div>
                                )}
                                {vendor && (
                                    <div className="text-xs text-gray-500 mt-1">
                                        {t('cart.vendor.label')}: {vendor.name ?? '—'}{' '}
                                        {vendor.id && (
                                            <Link className="text-blue-600 hover:underline" to={`/seller/${vendor.id}`}>
                                                {t('cart.vendor.contact')}
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
                            <div className="w-24 text-right font-medium">{formatCurrency(line, { currency, locale })}</div>
                            <button onClick={()=>remove(it.id)} className="px-2 py-1 text-sm rounded border">{t('cart.line.remove')}</button>
                        </div>
                    );
                })}
            </div>

            <div className="flex justify-between items-center border-t pt-4">
                <div className="text-lg">{t('cart.summary.totalLabel')}:</div>
                <div className="text-xl font-semibold">{formatCurrency(total, { currency, locale })}</div>
            </div>

            <div className="flex justify-end">
                <Link
                    to="/checkout"
                    data-testid="go-checkout"
                    className="px-5 py-2 rounded bg-black text-white disabled:opacity-50"
                    aria-disabled={cart.items.length===0}
                    onClick={e=>{ if(cart.items.length===0){e.preventDefault()} }}
                >
                    {t('cart.summary.checkout')}
                </Link>
            </div>
        </div>
    )
}
