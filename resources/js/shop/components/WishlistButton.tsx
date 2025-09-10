import React from 'react';
import useWishlist from '../hooks/useWishlist';
import { Heart } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function WishlistButton({
                                           product,
                                           className,
                                           'data-testid': testId,
                                       }: {
    product: { id: number; slug?: string | null; name: string; price: number | string; preview_url?: string | null; images?: { url: string; is_primary?: boolean; alt?: string|null }[] };
    className?: string;
    'data-testid'?: string;
}) {
    const { has, toggle } = useWishlist();
    const active = has(product.id);

    const preview =
        product.preview_url
        ?? product.images?.find(i => i.is_primary)?.url
        ?? product.images?.[0]?.url
        ?? null;

    return (
        <button
            type="button"
            aria-label={active ? 'remove from wishlist' : 'add to wishlist'}
            data-testid={testId ?? 'wishlist-toggle'}
            onClick={(e) => { e.preventDefault(); toggle({
                id: product.id, slug: product.slug ?? null, name: product.name, price: product.price, preview_url: preview
            }); }}
            className={cn(
                'inline-flex items-center justify-center rounded-full border px-2 py-1 text-xs transition',
                active ? 'bg-pink-600 text-white border-pink-600' : 'bg-white text-gray-700 hover:bg-gray-50',
                className
            )}
            title={active ? 'Усунути з обраного' : 'Додати в обране'}
        >
            <Heart className={cn('h-4 w-4 mr-1', active ? 'fill-current' : '')} />
            {active ? 'В обраному' : 'В обране'}
        </button>
    );
}
