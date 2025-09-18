import React from 'react';
import {Link} from 'react-router-dom';
import useWishlist from '../hooks/useWishlist';
import {Card} from '@/components/ui/card';
import {Button} from '@/components/ui/button';
import {Alert, AlertDescription, AlertTitle} from '@/components/ui/alert';
import {Skeleton} from '@/components/ui/skeleton';
import {formatPrice} from '../ui/format';
import {WishlistApi} from '../api';
import {Loader2, X} from 'lucide-react';
import { useLocale } from '../i18n/LocaleProvider';

export default function WishlistPage() {
    const {items, clear, isLoading, error, remove} = useWishlist();
    const [removingIds, setRemovingIds] = React.useState<Record<number, boolean>>({});
    const hasItems = items.length > 0;
    const { t } = useLocale();

    const handleRemove = React.useCallback(
        async (productId: number) => {
            setRemovingIds(prev => ({...prev, [productId]: true}));
            try {
                await WishlistApi.remove(productId);
                remove(productId, {sync: false});
            } catch (err) {
                console.error('Wishlist remove error', err);
            } finally {
                setRemovingIds(prev => {
                    const {[productId]: _removed, ...rest} = prev;
                    return rest;
                });
            }
        },
        [remove],
    );

    return (
        <div className="mx-auto w-full max-w-7xl px-4 py-6" data-testid="wishlist-page">
            <div className="mb-4 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">{t('wishlist.title')}</h1>
                {hasItems && (
                    <Button variant="outline" onClick={() => clear()} disabled={isLoading}>
                        {t('wishlist.clear')}
                    </Button>
                )}
            </div>

            {isLoading && (
                <div className="mb-4 text-sm text-muted-foreground" data-testid="wishlist-loading">
                    {t('wishlist.loading')}
                </div>
            )}

            {error && (
                <Alert variant="destructive" className="mb-4" data-testid="wishlist-error">
                    <AlertTitle>{t('wishlist.errorTitle')}</AlertTitle>
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}

            {isLoading && !hasItems ? (
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                    {Array.from({length: 4}).map((_, idx) => (
                        <Card key={`wishlist-skeleton-${idx}`} className="overflow-hidden">
                            <div className="aspect-square bg-muted/40">
                                <Skeleton className="h-full w-full" />
                            </div>
                            <div className="space-y-2 p-3">
                                <Skeleton className="h-4 w-3/4" />
                                <Skeleton className="h-4 w-1/2" />
                            </div>
                        </Card>
                    ))}
                </div>
            ) : hasItems ? (
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                    {items.map(p => {
                        const isRemoving = removingIds[p.id] ?? false;
                        return (
                            <Card
                                key={`${p.id}-${p.slug ?? ''}`}
                                className="relative overflow-hidden"
                                data-testid="wishlist-card"
                                aria-busy={isRemoving}
                            >
                                <Button
                                    type="button"
                                    size="icon"
                                    variant="ghost"
                                    className="absolute right-2 top-2 z-10"
                                    disabled={isRemoving || isLoading}
                                    onClick={() => void handleRemove(p.id)}
                                    aria-label={t('wishlist.removeAria', { name: p.name })}
                                    data-testid="wishlist-remove-button"
                                >
                                    {isRemoving ? (
                                        <Loader2 className="h-4 w-4 animate-spin" aria-hidden="true" />
                                    ) : (
                                        <X className="h-4 w-4" aria-hidden="true" />
                                    )}
                                </Button>
                                <Link to={`/product/${p.slug ?? p.id}`} className="block">
                                    <div className="aspect-square bg-muted/40">
                                        {p.preview_url ? (
                                            <img src={p.preview_url} alt={p.name} className="h-full w-full object-cover" />
                                    ) : (
                                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                                            {t('wishlist.noImage')}
                                        </div>
                                    )}
                                </div>
                                    <div className="p-3">
                                        <div className="line-clamp-2 text-sm font-medium">{p.name}</div>
                                        <div className="mt-1 text-sm text-muted-foreground">{formatPrice(p.price)}</div>
                                    </div>
                                </Link>
                            </Card>
                        );
                    })}
                </div>
            ) : (
                <div className="text-muted-foreground" data-testid="wishlist-empty">
                    {t('wishlist.empty')}
                </div>
            )}
        </div>
    );
}
