import React from 'react';
import {Link, Navigate, useLocation} from 'react-router-dom';
import useAuth from '../hooks/useAuth';
import {resolveErrorMessage} from '../lib/errors';
import { useLocale } from '../i18n/LocaleProvider';

export default function RegisterPage() {
    const {register: registerUser, isAuthenticated, isReady, isLoading} = useAuth();
    const location = useLocation();
    const [name, setName] = React.useState('');
    const [email, setEmail] = React.useState('');
    const [password, setPassword] = React.useState('');
    const [passwordConfirmation, setPasswordConfirmation] = React.useState('');
    const [error, setError] = React.useState<string | null>(null);
    const [submitting, setSubmitting] = React.useState(false);
    const { t } = useLocale();

    const from = React.useMemo(() => {
        const state = location.state as { from?: string } | null;
        return state?.from;
    }, [location.state]);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (submitting) return;
        const trimmedPassword = password.trim();
        if (trimmedPassword.length < 8) {
            setError(t('auth.register.passwordTooShort'));
            return;
        }
        if (password !== passwordConfirmation) {
            setError(t('auth.register.passwordMismatch'));
            return;
        }
        setError(null);
        setSubmitting(true);
        try {
            await registerUser({
                name,
                email,
                password,
                password_confirmation: passwordConfirmation,
            });
        } catch (err) {
            setError(resolveErrorMessage(err, t('auth.register.errorFallback')));
        } finally {
            setSubmitting(false);
        }
    };

    if (!isReady && isLoading) {
        return (
            <div className="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-4 py-16">
                <p className="text-sm text-gray-500">{t('auth.shared.loading')}</p>
            </div>
        );
    }

    if (isReady && isAuthenticated) {
        return <Navigate to={from ?? '/profile'} replace/>;
    }

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col items-center justify-center px-4 py-16">
            <div className="w-full max-w-md rounded-lg border bg-white p-8 shadow-sm">
                <h1 className="mb-6 text-2xl font-semibold">{t('auth.register.title')}</h1>
                {error && (
                    <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {error}
                    </div>
                )}
                <form className="space-y-4" onSubmit={handleSubmit}>
                    <div className="space-y-1">
                        <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                            {t('auth.register.nameLabel')}
                        </label>
                        <input
                            id="name"
                            type="text"
                            autoComplete="name"
                            required
                            value={name}
                            onChange={event => setName(event.target.value)}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                        />
                    </div>
                    <div className="space-y-1">
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                            {t('auth.register.emailLabel')}
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
                            {t('auth.register.passwordLabel')}
                        </label>
                        <input
                            id="password"
                            type="password"
                            autoComplete="new-password"
                            required
                            value={password}
                            onChange={event => setPassword(event.target.value)}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                        />
                    </div>
                    <div className="space-y-1">
                        <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                            {t('auth.register.passwordConfirmationLabel')}
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            autoComplete="new-password"
                            required
                            value={passwordConfirmation}
                            onChange={event => setPasswordConfirmation(event.target.value)}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                        />
                    </div>
                    <button
                        type="submit"
                        disabled={submitting || isLoading}
                        className="w-full rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {submitting || isLoading ? t('auth.shared.processing') : t('auth.register.submit')}
                    </button>
                </form>
                <p className="mt-6 text-sm text-gray-600">
                    {t('auth.register.haveAccount')}{' '}
                    <Link to="/login" className="font-medium text-blue-600 hover:text-blue-500">
                        {t('auth.register.signInLink')}
                    </Link>
                </p>
            </div>
        </div>
    );
}
