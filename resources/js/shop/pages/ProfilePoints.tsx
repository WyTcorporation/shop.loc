import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { ProfileApi, type LoyaltyPointTransaction, type LoyaltyPointsResponse } from '../api';
import ProfileNavigation from '../components/ProfileNavigation';
import useAuth from '../hooks/useAuth';
import { resolveErrorMessage } from '../lib/errors';

function formatDate(dateString?: string | null) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) {
        return dateString;
    }
    return date.toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function getTransactionTypeLabel(type?: string | null) {
    if (!type) {
        return 'Операція';
    }

    switch (type) {
        case 'earn':
        case 'earned':
            return 'Нарахування';
        case 'redeem':
        case 'spent':
            return 'Списання';
        default:
            return type;
    }
}

function normalizePoints(value: number | string) {
    const numeric = typeof value === 'string' ? Number(value) : value;
    if (Number.isNaN(numeric)) {
        return value;
    }
    return numeric;
}

export default function ProfilePointsPage() {
    const { isAuthenticated, isReady } = useAuth();
    const location = useLocation();
    const redirectTo = React.useMemo(() => {
        const path = `${location.pathname ?? ''}${location.search ?? ''}${location.hash ?? ''}`;
        return path || '/profile/points';
    }, [location.hash, location.pathname, location.search]);

    const [data, setData] = React.useState<LoyaltyPointsResponse | null>(null);
    const [loading, setLoading] = React.useState(false);
    const [error, setError] = React.useState<string | null>(null);

    React.useEffect(() => {
        if (!isReady || !isAuthenticated) {
            return;
        }

        let ignore = false;
        setLoading(true);
        setError(null);

        ProfileApi.fetchPoints()
            .then((payload) => {
                if (!ignore) {
                    setData(payload);
                }
            })
            .catch((err) => {
                if (!ignore) {
                    setError(resolveErrorMessage(err, 'Не вдалося завантажити інформацію про бали.'));
                }
            })
            .finally(() => {
                if (!ignore) {
                    setLoading(false);
                }
            });

        return () => {
            ignore = true;
        };
    }, [isAuthenticated, isReady]);

    if (!isReady) {
        return (
            <div className="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-4 py-16">
                <p className="text-sm text-gray-500">Завантаження балів…</p>
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: redirectTo }} replace />;
    }

    const transactions: LoyaltyPointTransaction[] = data?.transactions ?? [];
    const balance = data?.balance ?? 0;
    const totalEarned = data?.total_earned;
    const totalSpent = data?.total_spent;

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col px-4 py-16">
            <div className="w-full">
                <h1 className="mb-4 text-2xl font-semibold">Бонусні бали</h1>
                <p className="mb-8 text-sm text-gray-600">Відстежуйте доступний баланс та історію використання бонусних балів.</p>
                <ProfileNavigation />
                {error && <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
                <div className="mb-6 grid gap-4 rounded-lg border bg-white p-6 shadow-sm sm:grid-cols-3">
                    <div>
                        <p className="text-xs text-gray-500 uppercase">Доступно</p>
                        <p className="mt-2 text-3xl font-semibold text-gray-900">{balance}</p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500 uppercase">Нараховано</p>
                        <p className="mt-2 text-lg font-medium text-gray-900">
                            {typeof totalEarned === 'number' || typeof totalEarned === 'string' ? normalizePoints(totalEarned) : '—'}
                        </p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500 uppercase">Використано</p>
                        <p className="mt-2 text-lg font-medium text-gray-900">
                            {typeof totalSpent === 'number' || typeof totalSpent === 'string' ? normalizePoints(totalSpent) : '—'}
                        </p>
                    </div>
                </div>
                <div className="overflow-hidden rounded-lg border bg-white shadow-sm">
                    {loading ? (
                        <div className="flex items-center justify-center px-6 py-16 text-sm text-gray-500">Завантаження…</div>
                    ) : transactions.length === 0 ? (
                        <div className="px-6 py-16 text-center text-sm text-gray-600">
                            Історія балів порожня. Використовуйте бали під час оформлення замовлень, щоб побачити рух коштів.
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200 text-sm">
                                <thead className="bg-gray-50">
                                    <tr className="text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                        <th className="px-4 py-3">Дата</th>
                                        <th className="px-4 py-3">Опис</th>
                                        <th className="px-4 py-3">Тип</th>
                                        <th className="px-4 py-3">Кількість</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {transactions.map((transaction) => {
                                        const pointsValue = normalizePoints(transaction.points);
                                        return (
                                            <tr key={transaction.id} className="hover:bg-gray-50">
                                                <td className="px-4 py-4 text-gray-700">{formatDate(transaction.created_at)}</td>
                                                <td className="px-4 py-4 text-gray-700">{transaction.description ?? '—'}</td>
                                                <td className="px-4 py-4 text-gray-700">{getTransactionTypeLabel(transaction.type)}</td>
                                                <td className="px-4 py-4 font-medium text-gray-900">{pointsValue}</td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
