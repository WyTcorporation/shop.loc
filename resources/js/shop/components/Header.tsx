import { Link, NavLink } from 'react-router-dom';
import useCart from '../useCart';
import MiniCart from './MiniCart';
import WishlistBadge from '../components/WishlistBadge';
import { openCookiePreferences } from '../ui/analytics';

export default function Header() {
    const { cart, total } = useCart();
    const itemsCount = (cart?.items ?? []).reduce((s, i) => s + Number(i.qty || 0), 0);

    return (
        <header className="sticky top-0 z-30 bg-white/80 backdrop-blur border-b">
            <div className="mx-auto max-w-6xl px-4 h-14 flex items-center justify-between">
                <Link to="/" className="font-semibold tracking-tight">3D-Print Shop</Link>
                <nav className="flex items-center gap-4 text-sm">
                    <NavLink to="/" className={({isActive}) => isActive ? 'font-medium' : 'text-gray-600 hover:text-black'}>
                        Каталог
                    </NavLink>
                    <MiniCart />
                    <WishlistBadge />
                    <button onClick={openCookiePreferences} className="text-xs underline">
                        Налаштувати cookies
                    </button>
                </nav>
            </div>
        </header>
    );
}
