import { Link, NavLink } from 'react-router-dom';
import useCart from '../useCart';
import { formatPrice } from '../ui/format';
import MiniCart from './MiniCart';
import WishlistBadge from '../components/WishlistBadge';

export default function Header() {
    const { cart, total } = useCart();
    const itemsCount = (cart?.items ?? []).reduce((s, i) => s + Number(i.qty || 0), 0);

    return (
        <header className="sticky top-0 z-30 bg-white/80 backdrop-blur border-b">
            <div className="mx-auto max-w-6xl px-4 h-14 flex items-center justify-between">
                <Link to="/" className="font-semibold tracking-tight">3D-Print Shop</Link>
                <nav className="flex items-center gap-4 text-sm">
                    <NavLink to="/shop" className={({isActive}) => isActive ? 'font-medium' : 'text-gray-600 hover:text-black'}>
                        Каталог
                    </NavLink>
                    <NavLink to="/cart" className={({isActive}) => isActive ? 'font-medium' : 'text-gray-600 hover:text-black'}>
                        Кошик
                    </NavLink>
                    <Link to="/cart" className="relative inline-flex items-center gap-2 border rounded-full px-3 py-1">
                        <span>Кошик</span>
                        <span className="text-gray-500">{formatPrice(total)}</span>
                        <span className="absolute -right-2 -top-2 min-w-6 h-6 px-1 rounded-full bg-black text-white text-xs grid place-items-center">
              {itemsCount}
            </span>
                    </Link>
                    <MiniCart />
                    <WishlistBadge />
                </nav>
            </div>
        </header>
    );
}
