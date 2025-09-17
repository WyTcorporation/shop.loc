import { Link, NavLink } from 'react-router-dom';
import { ChevronDown } from 'lucide-react';

import useAuth from '../hooks/useAuth';
import MiniCart from './MiniCart';
import WishlistBadge from '../components/WishlistBadge';
import { openCookiePreferences } from '../ui/analytics';
import LanguageSwitcher from '@/shop/components/LanguageSwitcher';
import MainSearch from './MainSearch';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export default function Header() {
    const { isAuthenticated, user, logout, isReady } = useAuth();

    const displayName = user?.name?.trim() || user?.email?.trim() || 'Мій профіль';

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
                        {!isReady ? (
                            <span className="text-xs text-gray-500">Завантаження…</span>
                        ) : isAuthenticated ? (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <button className="flex items-center gap-1 text-sm font-medium text-gray-700 transition-colors hover:text-black">
                                        <span>{displayName}</span>
                                        <ChevronDown className="h-4 w-4" />
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="min-w-[12rem]">
                                    <DropdownMenuLabel className="flex flex-col gap-0.5">
                                        <span className="text-sm font-medium text-gray-900">{displayName}</span>
                                        {user?.email ? (
                                            <span className="text-xs text-gray-500">{user.email}</span>
                                        ) : null}
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem asChild>
                                        <Link to="/profile" className="block w-full">
                                            Мій профіль
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem onSelect={() => void logout()}>
                                        Вийти
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : (
                            <div className="flex items-center gap-3">
                                <Link to="/login" className="text-sm font-medium text-gray-700 transition-colors hover:text-black">
                                    Увійти
                                </Link>
                                <Link
                                    to="/register"
                                    className="rounded border border-black px-3 py-1 text-xs font-semibold uppercase tracking-wide text-black transition-colors hover:bg-black hover:text-white"
                                >
                                    Зареєструватися
                                </Link>
                            </div>
                        )}
                        <LanguageSwitcher />
                    </nav>
                </div>
            </div>
        </header>
    );
}
