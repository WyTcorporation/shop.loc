import React from 'react';
import { Link, Navigate, useParams } from 'react-router-dom';
import { AuthApi } from '../api';
import { resolveErrorMessage } from '../lib/errors';
import { useLocale } from '../i18n/LocaleProvider';

const EMAIL_REGEX = /[^@\s]+@[^@\s]+\.[^@\s]+/;

export default function ResetPasswordPage() {
    const params = useParams<{ token: string }>();
    const token = params.token ?? '';
    const [email, setEmail] = React.useState('');
    const [password, setPassword] = React.useState('');
    const [passwordConfirmation, setPasswordConfirmation] = React.useState('');
    const [submitting, setSubmitting] = React.useState(false);
    const [error, setError] = React.useState<string | null>(null);
    const [status, setStatus] = React.useState<string | null>(null);
    const [emailError, setEmailError] = React.useState<string | null>(null);
    const [passwordError, setPasswordError] = React.useState<string | null>(null);
    const [passwordConfirmationError, setPasswordConfirmationError] = React.useState<string | null>(null);
    const { t } = useLocale();

    if (!token) {
        return <Navigate to="/forgot-password" replace />;
    }

    const validate = () => {
        let valid = true;
        const trimmedEmail = email.trim();
        const trimmedPassword = password.trim();
        const trimmedConfirmation = passwordConfirmation.trim();

        if (!trimmedEmail) {
            setEmailError(t('auth.reset.errors.emailRequired'));
            valid = false;
        } else if (!EMAIL_REGEX.test(trimmedEmail)) {
            setEmailError(t('auth.reset.errors.emailInvalid'));
            valid = false;
        } else {
            setEmailError(null);
        }

        if (!trimmedPassword) {
            setPasswordError(t('auth.reset.errors.passwordRequired'));
            valid = false;
        } else if (trimmedPassword.length < 8) {
            setPasswordError(t('auth.reset.errors.passwordTooShort'));
            valid = false;
        } else {
            setPasswordError(null);
        }

        if (!trimmedConfirmation) {
            setPasswordConfirmationError(t('auth.reset.errors.confirmationRequired'));
            valid = false;
        } else if (trimmedPassword !== trimmedConfirmation) {
            setPasswordConfirmationError(t('auth.reset.errors.passwordMismatch'));
            valid = false;
        } else {
            setPasswordConfirmationError(null);
        }

        return valid;
    };

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (submitting) return;

        if (!validate()) {
            return;
        }

        setSubmitting(true);
        setError(null);
        setStatus(null);

        try {
            const trimmedEmail = email.trim();
            const trimmedPassword = password.trim();
            const response = await AuthApi.resetPassword({
                token,
                email: trimmedEmail,
                password: trimmedPassword,
                password_confirmation: passwordConfirmation.trim(),
            });
            setStatus(response?.message ?? t('auth.reset.update.successFallback'));
        } catch (err) {
            setError(resolveErrorMessage(err, t('auth.reset.update.errorFallback')));
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col items-center justify-center px-4 py-16">
            <div className="w-full max-w-md rounded-lg border bg-white p-8 shadow-sm">
                <h1 className="mb-6 text-2xl font-semibold">{t('auth.reset.update.title')}</h1>
                <p className="mb-4 text-sm text-gray-600">{t('auth.reset.update.description')}</p>
                {status && (
                    <div className="mb-4 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">{status}</div>
                )}
                {error && (
                    <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>
                )}
                <form className="space-y-4" onSubmit={handleSubmit} noValidate>
                    <div className="space-y-1">
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                            {t('auth.reset.fields.emailLabel')}
                        </label>
                        <input
                            id="email"
                            type="email"
                            autoComplete="email"
                            value={email}
                            onChange={(event) => setEmail(event.target.value)}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                            required
                        />
                        {emailError && <p className="text-xs text-red-600">{emailError}</p>}
                    </div>
                    <div className="space-y-1">
                        <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                            {t('auth.reset.fields.passwordLabel')}
                        </label>
                        <input
                            id="password"
                            type="password"
                            autoComplete="new-password"
                            value={password}
                            onChange={(event) => setPassword(event.target.value)}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                            required
                        />
                        {passwordError && <p className="text-xs text-red-600">{passwordError}</p>}
                    </div>
                    <div className="space-y-1">
                        <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                            {t('auth.reset.fields.passwordConfirmationLabel')}
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            autoComplete="new-password"
                            value={passwordConfirmation}
                            onChange={(event) => setPasswordConfirmation(event.target.value)}
                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                            required
                        />
                        {passwordConfirmationError && <p className="text-xs text-red-600">{passwordConfirmationError}</p>}
                    </div>
                    <button
                        type="submit"
                        disabled={submitting}
                        className="w-full rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {submitting ? t('auth.reset.update.submitting') : t('auth.reset.update.submit')}
                    </button>
                </form>
                <p className="mt-6 text-sm text-gray-600">
                    {t('auth.reset.update.backToLoginPrefix')}{' '}
                    <Link to="/login" className="font-medium text-blue-600 hover:text-blue-500">
                        {t('auth.reset.update.backToLoginLink')}
                    </Link>
                    {t('auth.reset.update.backToLoginSuffix')}
                </p>
            </div>
        </div>
    );
}
