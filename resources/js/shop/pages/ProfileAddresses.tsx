import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { AddressesApi, type Address } from '../api';
import ProfileNavigation from '../components/ProfileNavigation';
import useAuth from '../hooks/useAuth';
import { useLocale } from '../i18n/LocaleProvider';
import { resolveErrorMessage } from '../lib/errors';

export default function ProfileAddressesPage() {
    const { t } = useLocale();
    const { isAuthenticated, isReady } = useAuth();
    const location = useLocation();
    const redirectTo = React.useMemo(() => {
        const path = `${location.pathname ?? ''}${location.search ?? ''}${location.hash ?? ''}`;
        return path || '/profile/addresses';
    }, [location.hash, location.pathname, location.search]);

    const [addresses, setAddresses] = React.useState<Address[]>([]);
    const [loading, setLoading] = React.useState(false);
    const [error, setError] = React.useState<string | null>(null);

    React.useEffect(() => {
        if (!isReady || !isAuthenticated) {
            return;
        }

        let ignore = false;
        setLoading(true);
        setError(null);

        AddressesApi.list()
            .then((list) => {
                if (!ignore) {
                    setAddresses(list);
                }
            })
            .catch((err) => {
                if (!ignore) {
                    setError(resolveErrorMessage(err, t('profile.addresses.error')));
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
                <p className="text-sm text-gray-500">{t('profile.addresses.loading')}</p>
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: redirectTo }} replace />;
    }

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col px-4 py-16">
            <div className="w-full">
                <h1 className="mb-4 text-2xl font-semibold">{t('profile.addresses.title')}</h1>
                <p className="mb-8 text-sm text-gray-600">{t('profile.addresses.description')}</p>
                <ProfileNavigation />
                {error && <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
                {loading ? (
                    <div className="flex items-center justify-center rounded-lg border bg-white px-6 py-16 text-sm text-gray-500 shadow-sm">
                        {t('profile.addresses.list.loading')}
                    </div>
                ) : addresses.length === 0 ? (
                    <div className="rounded-lg border bg-white px-6 py-16 text-center text-sm text-gray-600 shadow-sm">
                        {t('profile.addresses.list.empty')}
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2">
                        {addresses.map((address) => (
                            <article key={address.id} className="rounded-lg border bg-white p-6 shadow-sm">
                                <h2 className="text-base font-semibold text-gray-900">{address.name || t('profile.addresses.list.defaultName')}</h2>
                                <dl className="mt-4 space-y-2 text-sm text-gray-700">
                                    <div>
                                        <dt className="font-medium text-gray-900">{t('profile.addresses.list.fields.city')}</dt>
                                        <dd>{address.city || '—'}</dd>
                                    </div>
                                    <div>
                                        <dt className="font-medium text-gray-900">{t('profile.addresses.list.fields.address')}</dt>
                                        <dd>{address.addr || '—'}</dd>
                                    </div>
                                    {address.postal_code && (
                                        <div>
                                            <dt className="font-medium text-gray-900">{t('profile.addresses.list.fields.postalCode')}</dt>
                                            <dd>{address.postal_code}</dd>
                                        </div>
                                    )}
                                    {address.phone && (
                                        <div>
                                            <dt className="font-medium text-gray-900">{t('profile.addresses.list.fields.phone')}</dt>
                                            <dd>{address.phone}</dd>
                                        </div>
                                    )}
                                </dl>
                            </article>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
