import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import useWishlist from '../hooks/useWishlist';
import { Heart } from 'lucide-react';

export default function WishlistBadge() {
    const { items } = useWishlist();
    const { pathname } = useLocation();
    const active = pathname.startsWith('/wishlist');

    return (
        <Link to="/wishlist" className="relative inline-flex items-center gap-2 px-3 py-1 rounded-lg hover:bg-gray-50">
            <Heart className={`h-5 w-5 ${active ? 'text-pink-600' : 'text-gray-700'}`} />
            <span className="text-sm">Обране</span>
            {items.length > 0 && (
                <span className="absolute -top-1 -right-1 rounded-full bg-pink-600 text-white text-[11px] px-1.5 py-0.5">
          {items.length}
        </span>
            )}
        </Link>
    );
}
