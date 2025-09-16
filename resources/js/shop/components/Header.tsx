import { Link, NavLink } from 'react-router-dom';
import useCart from '../useCart';
import MiniCart from './MiniCart';
import WishlistBadge from '../components/WishlistBadge';
import { openCookiePreferences } from '../ui/analytics';
import LanguageSwitcher from '@/shop/components/LanguageSwitcher';
import MainSearch from './MainSearch';

export default function Header() {
    const { cart, total } = useCart();
    const itemsCount = (cart?.items ?? []).reduce((s, i) => s + Number(i.qty || 0), 0);

    return (
        <header className="sticky top-0 z-30 border-b bg-white/80 backdrop-blur">
            <div className="mx-auto flex h-14 w-full max-w-6xl items-center gap-4 px-4">
                <Link to="/" className="shrink-0 font-semibold tracking-tight">3D-Print Shop</Link>
                <div className="flex flex-1 items-center gap-4">
                    <div className="hidden flex-1 md:block">
                        <MainSearch />
                    </div>
                    <nav className="flex items-center gap-4 text-sm">
                        <NavLink
                            to="/"
                            className={({ isActive }) =>
                                isActive ? 'font-medium' : 'text-gray-600 hover:text-black'
                            }
                        >
                            Каталог
                        </NavLink>
                        <MiniCart />
                        <WishlistBadge />
                        <button onClick={openCookiePreferences} className="text-xs underline">
                            Налаштувати cookies
                        </button>
                        <LanguageSwitcher />
                    </nav>
                </div>
            </div>
        </header>
    );
}
