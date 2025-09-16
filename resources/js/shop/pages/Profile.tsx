import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import ProfileNavigation from '../components/ProfileNavigation';
import useAuth from '../hooks/useAuth';
import { resolveErrorMessage } from '../lib/errors';

export default function ProfilePage() {
    const { user, isAuthenticated, isReady, isLoading, logout } = useAuth();
    const location = useLocation();
    const [error, setError] = React.useState<string | null>(null);
    const [pending, setPending] = React.useState(false);

    const redirectTo = React.useMemo(() => {
        const path = `${location.pathname ?? ''}${location.search ?? ''}${location.hash ?? ''}`;
        return path || '/profile';
    }, [location.hash, location.pathname, location.search]);

    if (!isReady) {
        return (
            <div className="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-4 py-16">
                <p className="text-sm text-gray-500">Завантаження профілю…</p>
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: redirectTo }} replace />;
    }

    const handleLogout = async () => {
        setError(null);
        setPending(true);
        try {
            await logout();
        } catch (err) {
            setError(resolveErrorMessage(err, 'Не вдалося вийти. Спробуйте ще раз.'));
        } finally {
            setPending(false);
        }
    };

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col px-4 py-16">
            <div className="w-full lg:w-3/4 xl:w-2/3">
                <h1 className="mb-4 text-2xl font-semibold">Профіль</h1>
                <p className="mb-6 text-sm text-gray-600">
                    Ласкаво просимо, <span className="font-medium text-gray-900">{user?.name ?? 'користувачу'}</span>. Керуйте своїми даними та
                    перейдіть до інших розділів профілю.
                </p>
                <ProfileNavigation />
            </div>
            <div className="mt-4 w-full max-w-2xl rounded-lg border bg-white p-8 shadow-sm">
                <h2 className="mb-6 text-xl font-semibold">Особисті дані</h2>
                {error && <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
                <dl className="space-y-4 text-sm text-gray-700">
                    <div>
                        <dt className="font-medium text-gray-900">ID</dt>
                        <dd className="mt-1 text-gray-700">{user?.id ?? '—'}</dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">Ім'я</dt>
                        <dd className="mt-1 text-gray-700">{user?.name ?? '—'}</dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">Email</dt>
                        <dd className="mt-1 break-words text-gray-700">{user?.email ?? '—'}</dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">Email підтверджено</dt>
                        <dd className="mt-1 text-gray-700">{user?.email_verified_at ? 'Так' : 'Ні'}</dd>
                    </div>
                </dl>
                <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-xs text-gray-500">Токен Sanctum збережено локально для авторизованих запитів до API.</p>
                    <button
                        type="button"
                        onClick={handleLogout}
                        disabled={pending || isLoading}
                        className="w-full rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                    >
                        {pending || isLoading ? 'Вихід…' : 'Вийти'}
                    </button>
                </div>
            </div>
        </div>
    );
}
