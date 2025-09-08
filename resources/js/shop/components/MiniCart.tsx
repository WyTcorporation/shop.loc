import * as Dialog from '@radix-ui/react-dialog';
import { Link } from 'react-router-dom';
import useCart from '../useCart';
import { formatPrice } from '../ui/format';

export default function MiniCart() {
    const { cart, total } = useCart();
    const items = cart?.items ?? [];

    return (
        <Dialog.Root>
            <Dialog.Trigger asChild>
                <button className="relative inline-flex items-center gap-2 border rounded-full px-3 py-1">
                    <span>Кошик</span>
                    <span className="text-gray-500">{formatPrice(total)}</span>
                    <span className="absolute -right-2 -top-2 min-w-6 h-6 px-1 rounded-full bg-black text-white text-xs grid place-items-center">
            {items.reduce((s, i) => s + Number(i.qty || 0), 0)}
          </span>
                </button>
            </Dialog.Trigger>

            <Dialog.Portal>
                <Dialog.Overlay className="fixed inset-0 bg-black/30" />
                <Dialog.Content className="fixed right-4 top-16 w-[420px] max-h-[75vh] overflow-auto bg-white rounded-xl shadow-xl p-4">
                    <div className="flex items-center justify-between mb-3">
                        <Dialog.Title className="text-lg font-semibold">Ваш кошик</Dialog.Title>
                        <Dialog.Close className="text-sm text-gray-500 hover:text-black">Закрити</Dialog.Close>
                    </div>

                    {items.length === 0 ? (
                        <div className="text-gray-500">Кошик порожній</div>
                    ) : (
                        <ul className="space-y-3">
                            {items.map(it => (
                                <li key={it.id} className="flex gap-3">
                                    {it.product?.preview_url && (
                                        <img src={it.product.preview_url} className="w-12 h-12 rounded border object-cover" />
                                    )}
                                    <div className="flex-1">
                                        <div className="text-sm font-medium line-clamp-2">{it.product?.name ?? `#${it.product_id}`}</div>
                                        <div className="text-xs text-gray-500">×{it.qty}</div>
                                    </div>
                                    <div className="text-sm font-medium">{formatPrice(Number(it.price) * Number(it.qty))}</div>
                                </li>
                            ))}
                        </ul>
                    )}

                    <div className="mt-4 border-t pt-3 flex items-center justify-between">
                        <div className="text-sm text-gray-600">Разом</div>
                        <div className="text-base font-semibold">{formatPrice(total)}</div>
                    </div>

                    <div className="mt-4 grid grid-cols-2 gap-2">
                        <Dialog.Close asChild>
                            <Link to="/cart" className="text-center border rounded-lg py-2 hover:bg-gray-50">Відкрити кошик</Link>
                        </Dialog.Close>
                        <Dialog.Close asChild>
                            <Link to="/checkout" className="text-center rounded-lg py-2 bg-black text-white hover:bg-black/90">Оформити</Link>
                        </Dialog.Close>
                    </div>
                </Dialog.Content>
            </Dialog.Portal>
        </Dialog.Root>
    );
}
