import React from 'react';
import type { Review } from '../api';

function formatDate(value?: string | null) {
    if (!value) return null;
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return null;
    return date.toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function renderStars(rating: number) {
    const safeRating = Math.max(0, Math.min(5, Math.round(rating)));
    const full = '★★★★★'.slice(0, safeRating);
    const empty = '☆☆☆☆☆'.slice(safeRating);
    return `${full}${empty}`;
}

type ReviewListProps = {
    reviews: Review[];
    averageRating?: number | null;
    loading?: boolean;
    className?: string;
};

export default function ReviewList({ reviews, averageRating = null, loading = false, className }: ReviewListProps) {
    const containerClassName = className ? `space-y-4 ${className}` : 'space-y-4';
    const hasReviews = reviews.length > 0;

    return (
        <section className={containerClassName} aria-label="Відгуки">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h2 className="text-lg font-semibold">Відгуки</h2>
                <div className="text-sm text-muted-foreground">
                    {averageRating != null && Number.isFinite(averageRating)
                        ? (
                            <span>
                                Середній рейтинг: <span className="font-medium">{averageRating.toFixed(1)}</span> із 5
                            </span>
                        )
                        : 'Ще немає оцінок'}
                </div>
            </div>

            {loading ? (
                <div className="text-sm text-muted-foreground">Завантаження відгуків…</div>
            ) : !hasReviews ? (
                <div className="text-sm text-muted-foreground">Відгуків поки немає. Станьте першим!</div>
            ) : (
                <ul className="space-y-4">
                    {reviews.map((review) => {
                        const dateLabel = formatDate(review.created_at);
                        const stars = renderStars(review.rating ?? 0);
                        return (
                            <li key={review.id} className="rounded-lg border border-gray-200 p-4 shadow-sm">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div className="font-medium">{review.user?.name ?? 'Користувач'}</div>
                                        {dateLabel ? (
                                            <div className="text-xs text-muted-foreground">{dateLabel}</div>
                                        ) : null}
                                    </div>
                                    <div className="text-right">
                                        <span aria-hidden="true" className="block text-lg leading-none text-amber-500">
                                            {stars}
                                        </span>
                                        <span className="text-xs text-muted-foreground">{review.rating} із 5</span>
                                    </div>
                                </div>
                                {review.text ? (
                                    <p className="mt-3 whitespace-pre-line text-sm text-gray-700">{review.text}</p>
                                ) : null}
                                {review.status === 'pending' ? (
                                    <div className="mt-3 text-xs text-amber-600">
                                        Відгук очікує модерації. Ми сповістимо після публікації.
                                    </div>
                                ) : null}
                            </li>
                        );
                    })}
                </ul>
            )}
        </section>
    );
}
