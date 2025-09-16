import React from 'react';
import { Link, Navigate, useLocation } from 'react-router-dom';
import { OrdersApi, type OrderResponse } from '../api';
import ProfileNavigation from '../components/ProfileNavigation';
import useAuth from '../hooks/useAuth';
import { resolveErrorMessage } from '../lib/errors';
import { formatPrice } from '../ui/format';

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
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function ProfileOrdersPage() {
    const { isAuthenticated, isReady } = useAuth();
    const location = useLocation();
    const redirectTo = React.useMemo(() => {
        const path = `${location.pathname ?? ''}${location.search ?? ''}${location.hash ?? ''}`;
        return path || '/profile/orders';
    }, [location.hash, location.pathname, location.search]);

    const [orders, setOrders] = React.useState<OrderResponse[]>([]);
    const [loading, setLoading] = React.useState(false);
    const [error, setError] = React.useState<string | null>(null);

    React.useEffect(() => {
        if (!isReady || !isAuthenticated) {
            return;
        }

        let ignore = false;
        setLoading(true);
        setError(null);

        OrdersApi.listMine()
            .then((list) => {
                if (!ignore) {
                    setOrders(list);
                }
            })
            .catch((err) => {
                if (!ignore) {
                    setError(resolveErrorMessage(err, 'Не вдалося завантажити замовлення.'));
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
                <p className="text-sm text-gray-500">Завантаження замовлень…</p>
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: redirectTo }} replace />;
    }

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col px-4 py-16">
            <div className="w-full">
                <h1 className="mb-4 text-2xl font-semibold">Мої замовлення</h1>
                <p className="mb-8 text-sm text-gray-600">Переглядайте історію покупок, статус замовлень та переходьте до їх деталей.</p>
                <ProfileNavigation />
                {error && <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
                <div className="overflow-hidden rounded-lg border bg-white shadow-sm">
                    {loading ? (
                        <div className="flex items-center justify-center px-6 py-16 text-sm text-gray-500">Завантаження…</div>
                    ) : orders.length === 0 ? (
                        <div className="px-6 py-16 text-center text-sm text-gray-600">
                            Ви ще не зробили жодного замовлення.{' '}
                            <Link className="underline" to="/">
                                Перейти до каталогу
                            </Link>
                            .
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200 text-sm">
                                <thead className="bg-gray-50">
                                    <tr className="text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                        <th className="px-4 py-3">Номер</th>
                                        <th className="px-4 py-3">Дата</th>
                                        <th className="px-4 py-3">Статус</th>
                                        <th className="px-4 py-3">Сума</th>
                                        <th className="px-4 py-3">Дії</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {orders.map((order) => {
                                        const total = formatPrice(order.total, order.currency ?? 'EUR');
                                        return (
                                            <tr key={order.number} className="hover:bg-gray-50">
                                                <td className="px-4 py-4 font-medium text-gray-900">{order.number}</td>
                                                <td className="px-4 py-4 text-gray-700">{formatDate(order.created_at)}</td>
                                                <td className="px-4 py-4 text-gray-700">{order.status ?? '—'}</td>
                                                <td className="px-4 py-4 text-gray-900">{total}</td>
                                                <td className="px-4 py-4">
                                                    <Link
                                                        className="text-xs font-medium text-blue-600 hover:text-blue-800"
                                                        to={`/order/${order.number}`}
                                                    >
                                                        Деталі замовлення
                                                    </Link>
                                                </td>
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
