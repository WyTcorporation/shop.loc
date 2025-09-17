import React from 'react';
import { Link } from 'react-router-dom';
import { ReviewsApi, type Review } from '../api';
import RatingStars from './RatingStars';
import useAuth from '../hooks/useAuth';
import { useNotify } from '../ui/notify';
import { resolveErrorMessage } from '../lib/errors';

type ReviewFormProps = {
    productId: number;
    onSubmitted?: (review: Review) => void;
    className?: string;
};

export default function ReviewForm({ productId, onSubmitted, className }: ReviewFormProps) {
    const { isAuthenticated } = useAuth();
    const { success, error } = useNotify();

    const [rating, setRating] = React.useState<number>(5);
    const [text, setText] = React.useState('');
    const [submitting, setSubmitting] = React.useState(false);
    const [formError, setFormError] = React.useState<string | null>(null);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (submitting) return;
        if (!isAuthenticated) {
            setFormError('Щоб залишити відгук, будь ласка, увійдіть у свій акаунт.');
            return;
        }

        setFormError(null);
        setSubmitting(true);
        try {
            const payload = await ReviewsApi.create(productId, {
                rating,
                text: text.trim() ? text.trim() : undefined,
            });
            const review = payload.data;
            setText('');
            setRating(5);
            onSubmitted?.(review);
            success({
                title: 'Дякуємо за відгук!',
                description: 'Ваш відгук буде опубліковано після модерації.',
            });
        } catch (err) {
            const message = resolveErrorMessage(err, 'Не вдалося надіслати відгук. Спробуйте пізніше.');
            setFormError(message);
            error({ title: 'Не вдалося надіслати відгук', description: message });
        } finally {
            setSubmitting(false);
        }
    };

    const fieldsetDisabled = submitting || !isAuthenticated;
    const containerClassName = className ? `space-y-4 ${className}` : 'space-y-4';

    const ratingLabelId = React.useId();

    return (
        <section className={containerClassName} aria-label="Форма відгуку">
            <h3 className="text-lg font-semibold">Залишити відгук</h3>
            {!isAuthenticated ? (
                <p className="text-sm text-muted-foreground">
                    Щоб поділитися враженнями,{' '}
                    <Link to="/login" className="font-medium text-blue-600 hover:text-blue-500">
                        увійдіть
                    </Link>{' '}
                    або{' '}
                    <Link to="/register" className="font-medium text-blue-600 hover:text-blue-500">
                        зареєструйтеся
                    </Link>
                    .
                </p>
            ) : null}
            {formError ? (
                <div className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{formError}</div>
            ) : null}
            <form className="space-y-4" onSubmit={handleSubmit}>
                <fieldset className="space-y-4" disabled={fieldsetDisabled}>
                    <div className="space-y-1">
                        <span id={ratingLabelId} className="block text-sm font-medium text-gray-700">
                            Оцінка
                        </span>
                        <RatingStars
                            value={rating}
                            onChange={setRating}
                            aria-labelledby={ratingLabelId}
                        />
                    </div>
                    <div className="space-y-1">
                        <label htmlFor="review-text" className="block text-sm font-medium text-gray-700">
                            Коментар (необов'язково)
                        </label>
                        <textarea
                            id="review-text"
                            value={text}
                            onChange={(event) => setText(event.target.value)}
                            rows={4}
                            maxLength={2000}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                            placeholder="Поділіться своїми враженнями про товар"
                        />
                        <div className="text-right text-xs text-muted-foreground">{text.length}/2000</div>
                    </div>
                    <button
                        type="submit"
                        className="inline-flex items-center justify-center rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {submitting ? 'Надсилання…' : 'Надіслати відгук'}
                    </button>
                </fieldset>
            </form>
        </section>
    );
}
