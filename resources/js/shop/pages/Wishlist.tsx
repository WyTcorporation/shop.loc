import React from 'react';
import {Link} from 'react-router-dom';
import useWishlist from '../hooks/useWishlist';
import {Card} from '@/components/ui/card';
import {Button} from '@/components/ui/button';
import {Alert, AlertDescription, AlertTitle} from '@/components/ui/alert';
import {Skeleton} from '@/components/ui/skeleton';
import {formatPrice} from '../ui/format';

export default function WishlistPage() {
    const {items, clear, isLoading, error} = useWishlist();
    const hasItems = items.length > 0;

    return (
        <div className="mx-auto w-full max-w-7xl px-4 py-6" data-testid="wishlist-page">
            <div className="mb-4 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Обране</h1>
                {hasItems && (
                    <Button variant="outline" onClick={() => clear()} disabled={isLoading}>
                        Очистити
                    </Button>
                )}
            </div>

            {isLoading && (
                <div className="mb-4 text-sm text-muted-foreground" data-testid="wishlist-loading">
                    Оновлюємо список бажаного...
                </div>
            )}

            {error && (
                <Alert variant="destructive" className="mb-4" data-testid="wishlist-error">
                    <AlertTitle>Не вдалося оновити список</AlertTitle>
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
                    {items.map(p => (
                        <Card key={`${p.id}-${p.slug ?? ''}`} className="overflow-hidden" data-testid="wishlist-card">
                            <Link to={`/product/${p.slug ?? p.id}`} className="block">
                                <div className="aspect-square bg-muted/40">
                                    {p.preview_url ? (
                                        <img src={p.preview_url} alt={p.name} className="h-full w-full object-cover" />
                                    ) : (
                                        <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                                            без фото
                                        </div>
                                    )}
                                </div>
                                <div className="p-3">
                                    <div className="line-clamp-2 text-sm font-medium">{p.name}</div>
                                    <div className="mt-1 text-sm text-muted-foreground">{formatPrice(p.price)}</div>
                                </div>
                            </Link>
                        </Card>
                    ))}
                </div>
            ) : (
                <div className="text-muted-foreground" data-testid="wishlist-empty">
                    Поки що порожньо.
                </div>
            )}
        </div>
    );
}
