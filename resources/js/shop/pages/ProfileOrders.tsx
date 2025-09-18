import React from 'react';
import { Link, Navigate, useLocation } from 'react-router-dom';
import { OrdersApi, type OrderResponse } from '../api';
import ProfileNavigation from '../components/ProfileNavigation';
import useAuth from '../hooks/useAuth';
import { useLocale } from '../i18n/LocaleProvider';
import { resolveErrorMessage } from '../lib/errors';
import { formatCurrency, formatDateTime } from '../ui/format';

export default function ProfileOrdersPage() {
    const { t, locale } = useLocale();
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
                    setError(resolveErrorMessage(err, t('profile.orders.error')));
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
    }, [isAuthenticated, isReady, t]);

    if (!isReady) {
        return (
            <div className="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-4 py-16">
                <p className="text-sm text-gray-500">{t('profile.orders.loading')}</p>
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: redirectTo }} replace />;
    }

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col px-4 py-16">
            <div className="w-full">
                <h1 className="mb-4 text-2xl font-semibold">{t('profile.orders.title')}</h1>
                <p className="mb-8 text-sm text-gray-600">{t('profile.orders.description')}</p>
                <ProfileNavigation />
                {error && <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
                <div className="overflow-hidden rounded-lg border bg-white shadow-sm">
                    {loading ? (
                        <div className="flex items-center justify-center px-6 py-16 text-sm text-gray-500">
                            {t('profile.orders.table.loading')}
                        </div>
                    ) : orders.length === 0 ? (
                        <div className="px-6 py-16 text-center text-sm text-gray-600">
                            {t('profile.orders.table.empty.description')}{' '}
                            <Link className="underline" to="/">
                                {t('profile.orders.table.empty.cta')}
                            </Link>
                            .
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200 text-sm">
                                <thead className="bg-gray-50">
                                    <tr className="text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                        <th className="px-4 py-3">{t('profile.orders.table.headers.number')}</th>
                                        <th className="px-4 py-3">{t('profile.orders.table.headers.date')}</th>
                                        <th className="px-4 py-3">{t('profile.orders.table.headers.status')}</th>
                                        <th className="px-4 py-3">{t('profile.orders.table.headers.total')}</th>
                                        <th className="px-4 py-3">{t('profile.orders.table.headers.actions')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {orders.map((order) => {
                                        const total = formatCurrency(order.total, {
                                            currency: order.currency ?? 'EUR',
                                            locale,
                                        });
                                        return (
                                            <tr key={order.number} className="hover:bg-gray-50">
                                                <td className="px-4 py-4 font-medium text-gray-900">{order.number}</td>
                                                <td className="px-4 py-4 text-gray-700">
                                                    {formatDateTime(order.created_at, {
                                                        locale,
                                                        invalidFallback: order.created_at ?? '—',
                                                    })}
                                                </td>
                                                <td className="px-4 py-4 text-gray-700">{order.status ?? '—'}</td>
                                                <td className="px-4 py-4 text-gray-900">{total}</td>
                                                <td className="px-4 py-4">
                                                    <Link
                                                        className="text-xs font-medium text-blue-600 hover:text-blue-800"
                                                        to={`/order/${order.number}`}
                                                    >
                                                        {t('profile.orders.table.view')}
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
