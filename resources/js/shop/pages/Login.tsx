import React from 'react';
import {Link, Navigate, useLocation} from 'react-router-dom';
import useAuth from '../hooks/useAuth';
import {resolveErrorMessage} from '../lib/errors';

export default function LoginPage() {
    const {login, isAuthenticated, isReady, isLoading} = useAuth();
    const location = useLocation();
    const [email, setEmail] = React.useState('');
    const [password, setPassword] = React.useState('');
    const [error, setError] = React.useState<string | null>(null);
    const [submitting, setSubmitting] = React.useState(false);

    const from = React.useMemo(() => {
        const state = location.state as { from?: string } | null;
        return state?.from;
    }, [location.state]);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (submitting) return;
        setError(null);
        setSubmitting(true);
        try {
            await login({email, password});
        } catch (err) {
            setError(resolveErrorMessage(err, 'Не вдалося увійти. Спробуйте ще раз.'));
        } finally {
            setSubmitting(false);
        }
    };

    if (!isReady && isLoading) {
        return (
            <div className="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-4 py-16">
                <p className="text-sm text-gray-500">Завантаження...</p>
            </div>
        );
    }

    if (isReady && isAuthenticated) {
        return <Navigate to={from ?? '/profile'} replace/>;
    }

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col items-center justify-center px-4 py-16">
            <div className="w-full max-w-md rounded-lg border bg-white p-8 shadow-sm">
                <h1 className="mb-6 text-2xl font-semibold">Вхід</h1>
                {error && (
                    <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {error}
                    </div>
                )}
                <form className="space-y-4" onSubmit={handleSubmit}>
                    <div className="space-y-1">
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                            Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            autoComplete="email"
                            required
                            value={email}
                            onChange={event => setEmail(event.target.value)}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                        />
                    </div>
                    <div className="space-y-1">
                        <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                            Пароль
                        </label>
                        <input
                            id="password"
                            type="password"
                            autoComplete="current-password"
                            required
                            value={password}
                            onChange={event => setPassword(event.target.value)}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                        />
                    </div>
                    <button
                        type="submit"
                        disabled={submitting || isLoading}
                        className="w-full rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {submitting || isLoading ? 'Зачекайте…' : 'Увійти'}
                    </button>
                </form>
                <p className="mt-6 text-sm text-gray-600">
                    Немає акаунта?{' '}
                    <Link to="/register" className="font-medium text-blue-600 hover:text-blue-500">
                        Зареєструватися
                    </Link>
                </p>
            </div>
        </div>
    );
}
