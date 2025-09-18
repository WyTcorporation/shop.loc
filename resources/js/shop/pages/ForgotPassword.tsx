import React from 'react';
import { Link } from 'react-router-dom';
import { AuthApi } from '../api';
import { resolveErrorMessage } from '../lib/errors';

const EMAIL_REGEX = /[^@\s]+@[^@\s]+\.[^@\s]+/;

export default function ForgotPasswordPage() {
    const [email, setEmail] = React.useState('');
    const [submitting, setSubmitting] = React.useState(false);
    const [error, setError] = React.useState<string | null>(null);
    const [status, setStatus] = React.useState<string | null>(null);
    const [emailError, setEmailError] = React.useState<string | null>(null);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (submitting) return;

        const trimmedEmail = email.trim();

        if (!trimmedEmail) {
            setEmailError('Вкажіть email.');
            return;
        }

        if (!EMAIL_REGEX.test(trimmedEmail)) {
            setEmailError('Вкажіть коректну електронну адресу.');
            return;
        }

        setEmailError(null);
        setError(null);
        setStatus(null);
        setSubmitting(true);

        try {
            const response = await AuthApi.requestPasswordReset({ email: trimmedEmail });
            setStatus(response?.message ?? 'Посилання для відновлення пароля надіслано.');
        } catch (err) {
            setError(resolveErrorMessage(err, 'Не вдалося надіслати лист. Спробуйте ще раз.'));
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col items-center justify-center px-4 py-16">
            <div className="w-full max-w-md rounded-lg border bg-white p-8 shadow-sm">
                <h1 className="mb-6 text-2xl font-semibold">Відновлення пароля</h1>
                <p className="mb-4 text-sm text-gray-600">
                    Введіть email, і ми надішлемо посилання для відновлення пароля.
                </p>
                {status && (
                    <div className="mb-4 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">{status}</div>
                )}
                {error && (
                    <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>
                )}
                <form className="space-y-4" onSubmit={handleSubmit} noValidate>
                    <div className="space-y-1">
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                            Email
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
                    <button
                        type="submit"
                        disabled={submitting}
                        className="w-full rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {submitting ? 'Надсилаємо…' : 'Надіслати посилання'}
                    </button>
                </form>
                <p className="mt-6 text-sm text-gray-600">
                    Пам’ятаєте пароль?{' '}
                    <Link to="/login" className="font-medium text-blue-600 hover:text-blue-500">
                        Повернутися до входу
                    </Link>
                </p>
            </div>
        </div>
    );
}
