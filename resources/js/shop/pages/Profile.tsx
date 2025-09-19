import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import ProfileNavigation from '../components/ProfileNavigation';
import { AuthApi, TwoFactorApi, type TwoFactorSetup, type TwoFactorStatus } from '../api';
import useAuth from '../hooks/useAuth';
import { useLocale } from '../i18n/LocaleProvider';
import { resolveErrorMessage } from '../lib/errors';

export default function ProfilePage() {
    const { t, lang } = useLocale();
    const { user, isAuthenticated, isReady, isLoading, logout, refresh } = useAuth();
    const location = useLocation();
    const [logoutError, setLogoutError] = React.useState<string | null>(null);
    const [logoutPending, setLogoutPending] = React.useState(false);
    const [profileError, setProfileError] = React.useState<string | null>(null);
    const [profileMessage, setProfileMessage] = React.useState<string | null>(null);
    const [profileSaving, setProfileSaving] = React.useState(false);
    const [verificationError, setVerificationError] = React.useState<string | null>(null);
    const [verificationMessage, setVerificationMessage] = React.useState<string | null>(null);
    const [verificationSending, setVerificationSending] = React.useState(false);
    const [form, setForm] = React.useState({
        name: user?.name ?? '',
        email: user?.email ?? '',
        password: '',
        password_confirmation: '',
    });
    const [twoFactorStatus, setTwoFactorStatus] = React.useState<TwoFactorStatus | null>(null);
    const [twoFactorSetup, setTwoFactorSetup] = React.useState<TwoFactorSetup | null>(null);
    const [twoFactorCode, setTwoFactorCode] = React.useState('');
    const [twoFactorFetching, setTwoFactorFetching] = React.useState(false);
    const [twoFactorLoading, setTwoFactorLoading] = React.useState(false);
    const [twoFactorError, setTwoFactorError] = React.useState<string | null>(null);
    const [twoFactorMessage, setTwoFactorMessage] = React.useState<string | null>(null);

    const redirectTo = React.useMemo(() => {
        const path = `${location.pathname ?? ''}${location.search ?? ''}${location.hash ?? ''}`;
        return path || '/profile';
    }, [location.hash, location.pathname, location.search]);

    const dateLocale = React.useMemo(() => {
        switch (lang) {
            case 'uk':
                return 'uk-UA';
            case 'ru':
                return 'ru-RU';
            case 'pt':
                return 'pt-PT';
            default:
                return 'en-US';
        }
    }, [lang]);

    const loadTwoFactorStatus = React.useCallback(async () => {
        if (!isAuthenticated) {
            setTwoFactorStatus(null);
            setTwoFactorSetup(null);
            setTwoFactorCode('');
            return;
        }

        setTwoFactorFetching(true);
        setTwoFactorError(null);
        try {
            const status = await TwoFactorApi.status();
            setTwoFactorStatus(status);
        } catch (err) {
            setTwoFactorError(
                resolveErrorMessage(err, t('profile.overview.errors.loadTwoFactorStatus')),
            );
        } finally {
            setTwoFactorFetching(false);
        }
    }, [isAuthenticated, t]);

    React.useEffect(() => {
        if (!isAuthenticated) return;
        loadTwoFactorStatus();
    }, [isAuthenticated, loadTwoFactorStatus]);

    React.useEffect(() => {
        if (isAuthenticated) return;
        setTwoFactorStatus(null);
        setTwoFactorSetup(null);
        setTwoFactorCode('');
        setTwoFactorError(null);
        setTwoFactorMessage(null);
    }, [isAuthenticated]);

    React.useEffect(() => {
        setForm((prev) => ({
            ...prev,
            name: user?.name ?? '',
            email: user?.email ?? '',
        }));
    }, [user?.email, user?.name]);

    const handleProfileSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (profileSaving) return;

        setProfileError(null);
        setProfileMessage(null);
        setProfileSaving(true);

        const payload: {
            name?: string;
            email?: string;
            password?: string;
            password_confirmation?: string;
        } = {
            name: form.name?.trim() ?? '',
            email: form.email?.trim() ?? '',
        };

        if (!form.password) {
            delete payload.password;
            delete payload.password_confirmation;
        } else {
            payload.password = form.password;
            payload.password_confirmation = form.password_confirmation;
        }

        try {
            const updated = await AuthApi.update(payload);
            setProfileMessage(t('profile.overview.notifications.updateSuccess'));
            setForm((prev) => ({
                ...prev,
                name: updated?.name ?? prev.name,
                email: updated?.email ?? prev.email,
                password: '',
                password_confirmation: '',
            }));
            await refresh().catch(() => undefined);
        } catch (err) {
            setProfileError(resolveErrorMessage(err, t('profile.overview.errors.update')));
        } finally {
            setProfileSaving(false);
        }
    };

    const handleStartTwoFactor = async () => {
        setTwoFactorError(null);
        setTwoFactorMessage(null);
        setTwoFactorLoading(true);
        try {
            const setup = await TwoFactorApi.enable();
            setTwoFactorSetup(setup);
            setTwoFactorCode('');
            setTwoFactorStatus({ enabled: false, pending: true, confirmed_at: null });
        } catch (err) {
            setTwoFactorError(resolveErrorMessage(err, t('profile.overview.errors.startTwoFactor')));
        } finally {
            setTwoFactorLoading(false);
        }
    };

    const handleConfirmTwoFactor = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (twoFactorLoading) return;

        const code = twoFactorCode.trim();
        if (!code) {
            setTwoFactorError(t('profile.overview.twoFactor.messages.emptyCode'));
            return;
        }

        setTwoFactorError(null);
        setTwoFactorMessage(null);
        setTwoFactorLoading(true);
        try {
            const response = await TwoFactorApi.confirm({ code });
            setTwoFactorMessage(
                response?.message ?? t('profile.overview.twoFactor.messages.enabled'),
            );
            setTwoFactorSetup(null);
            setTwoFactorCode('');
            await loadTwoFactorStatus();
            await refresh().catch(() => undefined);
        } catch (err) {
            setTwoFactorError(resolveErrorMessage(err, t('profile.overview.errors.confirmTwoFactor')));
        } finally {
            setTwoFactorLoading(false);
        }
    };

    const handleDisableTwoFactor = async () => {
        if (!window.confirm(t('profile.overview.twoFactor.disable.confirm'))) {
            return;
        }

        setTwoFactorError(null);
        setTwoFactorMessage(null);
        setTwoFactorLoading(true);
        try {
            await TwoFactorApi.disable();
            setTwoFactorSetup(null);
            setTwoFactorCode('');
            setTwoFactorMessage(t('profile.overview.twoFactor.messages.disabled'));
            await loadTwoFactorStatus();
            await refresh().catch(() => undefined);
        } catch (err) {
            setTwoFactorError(resolveErrorMessage(err, t('profile.overview.errors.disableTwoFactor')));
        } finally {
            setTwoFactorLoading(false);
        }
    };

    const confirmedAtText = React.useMemo(() => {
        if (!twoFactorStatus?.confirmed_at) return null;
        try {
            return new Date(twoFactorStatus.confirmed_at).toLocaleString(dateLocale);
        } catch {
            return twoFactorStatus.confirmed_at;
        }
    }, [dateLocale, twoFactorStatus?.confirmed_at]);

    const welcomeName = user?.name ?? t('profile.overview.guestName');
    const welcomeMessage = React.useMemo(
        () => t('profile.overview.welcome', { name: welcomeName }),
        [t, welcomeName],
    );
    const [welcomePrefix, welcomeSuffix, welcomeHasName] = React.useMemo(() => {
        const index = welcomeMessage.indexOf(welcomeName);
        if (index < 0) {
            return [welcomeMessage, '', false] as const;
        }
        return [
            welcomeMessage.slice(0, index),
            welcomeMessage.slice(index + welcomeName.length),
            true,
        ] as const;
    }, [welcomeMessage, welcomeName]);

    if (!isReady) {
        return (
            <div className="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-4 py-16">
                <p className="text-sm text-gray-500">{t('profile.overview.loading')}</p>
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: redirectTo }} replace />;
    }

    const handleLogout = async () => {
        setLogoutError(null);
        setLogoutPending(true);
        try {
            await logout();
        } catch (err) {
            setLogoutError(resolveErrorMessage(err, t('profile.overview.session.logout.error')));
        } finally {
            setLogoutPending(false);
        }
    };

    const handleResendVerification = async () => {
        if (verificationSending || isLoading) return;

        setVerificationError(null);
        setVerificationMessage(null);
        setVerificationSending(true);
        try {
            const response = await AuthApi.resendVerification();
            setVerificationMessage(
                response?.message ?? t('profile.overview.verification.successFallback'),
            );
        } catch (err) {
            setVerificationError(
                resolveErrorMessage(err, t('profile.overview.errors.resendVerification')),
            );
        } finally {
            setVerificationSending(false);
        }
    };

    const twoFactorStatusText = twoFactorStatus?.enabled
        ? t('profile.overview.twoFactor.status.enabled')
        : twoFactorStatus?.pending
            ? t('profile.overview.twoFactor.status.pending')
            : t('profile.overview.twoFactor.status.disabled');

    return (
        <div className="mx-auto flex min-h-[calc(100vh-3.5rem)] w-full max-w-6xl flex-col px-4 py-16">
            <div className="w-full lg:w-3/4 xl:w-2/3">
                <h1 className="mb-4 text-2xl font-semibold">{t('profile.overview.title')}</h1>
                <p className="mb-6 text-sm text-gray-600">
                    {welcomeHasName ? (
                        <>
                            {welcomePrefix}
                            <span className="font-medium text-gray-900">{welcomeName}</span>
                            {welcomeSuffix}
                        </>
                    ) : (
                        welcomeMessage
                    )}
                </p>
                <ProfileNavigation />
            </div>
            <div className="mt-4 w-full max-w-2xl rounded-lg border bg-white p-8 shadow-sm">
                <h2 className="mb-6 text-xl font-semibold">{t('profile.overview.personalDataTitle')}</h2>
                {profileError && (
                    <div
                        data-testid="profile-error"
                        className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
                    >
                        {profileError}
                    </div>
                )}
                {profileMessage && (
                    <div
                        data-testid="profile-success"
                        className="mb-4 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700"
                    >
                        {profileMessage}
                    </div>
                )}
                {!user?.email_verified_at && (
                    <div
                        data-testid="email-verification-alert"
                        className="mb-4 rounded border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800"
                    >
                        <p className="font-medium">{t('profile.overview.verification.title')}</p>
                        <p className="mt-1 text-yellow-700">{t('profile.overview.verification.description')}</p>
                        {verificationError && (
                            <p data-testid="email-verification-error" className="mt-2 text-red-600">
                                {verificationError}
                            </p>
                        )}
                        {verificationMessage && (
                            <p data-testid="email-verification-success" className="mt-2 text-green-700">
                                {verificationMessage}
                            </p>
                        )}
                        <button
                            type="button"
                            onClick={handleResendVerification}
                            disabled={verificationSending || isLoading}
                            className="mt-3 inline-flex items-center rounded border border-yellow-300 bg-white px-4 py-2 text-sm font-medium text-yellow-800 transition hover:bg-yellow-100 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {verificationSending ? (
                                <>
                                    <svg
                                        aria-hidden="true"
                                        className="mr-2 h-4 w-4 animate-spin text-yellow-700"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        />
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                        />
                                    </svg>
                                    {t('profile.overview.verification.resend.sending')}
                                </>
                            ) : (
                                t('profile.overview.verification.resend.action')
                            )}
                        </button>
                    </div>
                )}
                <form onSubmit={handleProfileSubmit} className="space-y-4" data-testid="profile-form">
                    <div>
                        <label htmlFor="profileName" className="block text-sm font-medium text-gray-700">
                            {t('profile.overview.form.labels.name')}
                        </label>
                        <input
                            id="profileName"
                            type="text"
                            value={form.name}
                            onChange={(event) =>
                                setForm((prev) => ({
                                    ...prev,
                                    name: event.target.value,
                                }))
                            }
                            autoComplete="name"
                            data-testid="profile-name"
                            className="mt-1 w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                            placeholder={t('profile.overview.form.placeholders.name')}
                        />
                    </div>
                    <div>
                        <label htmlFor="profileEmail" className="block text-sm font-medium text-gray-700">
                            {t('profile.overview.form.labels.email')}
                        </label>
                        <input
                            id="profileEmail"
                            type="email"
                            value={form.email}
                            onChange={(event) =>
                                setForm((prev) => ({
                                    ...prev,
                                    email: event.target.value,
                                }))
                            }
                            autoComplete="email"
                            data-testid="profile-email"
                            className="mt-1 w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                            placeholder={t('profile.overview.form.placeholders.email')}
                        />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label htmlFor="profilePassword" className="block text-sm font-medium text-gray-700">
                                {t('profile.overview.form.labels.newPassword')}
                            </label>
                            <input
                                id="profilePassword"
                                type="password"
                                value={form.password}
                                onChange={(event) =>
                                    setForm((prev) => ({
                                        ...prev,
                                        password: event.target.value,
                                    }))
                                }
                                autoComplete="new-password"
                                data-testid="profile-password"
                                className="mt-1 w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                                placeholder={t('profile.overview.form.placeholders.newPassword')}
                            />
                        </div>
                        <div>
                            <label htmlFor="profilePasswordConfirmation" className="block text-sm font-medium text-gray-700">
                                {t('profile.overview.form.labels.confirmPassword')}
                            </label>
                            <input
                                id="profilePasswordConfirmation"
                                type="password"
                                value={form.password_confirmation}
                                onChange={(event) =>
                                    setForm((prev) => ({
                                        ...prev,
                                        password_confirmation: event.target.value,
                                    }))
                                }
                                autoComplete="new-password"
                                data-testid="profile-password-confirmation"
                                className="mt-1 w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                                placeholder={t('profile.overview.form.placeholders.confirmPassword')}
                            />
                        </div>
                    </div>
                    <p className="text-xs text-gray-500">{t('profile.overview.form.hintPasswordOptional')}</p>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-xs text-gray-500">{t('profile.overview.form.hintApplyImmediately')}</p>
                        <button
                            type="submit"
                            disabled={profileSaving || isLoading}
                            data-testid="profile-save"
                            className="inline-flex w-full items-center justify-center rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                        >
                            {profileSaving || isLoading
                                ? t('profile.overview.form.submit.saving')
                                : t('profile.overview.form.submit.save')}
                        </button>
                    </div>
                </form>
                <dl className="mt-8 space-y-4 text-sm text-gray-700">
                    <div>
                        <dt className="font-medium text-gray-900">{t('profile.overview.info.id')}</dt>
                        <dd className="mt-1 text-gray-700" data-testid="profile-id">
                            {user?.id ?? '—'}
                        </dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">{t('profile.overview.info.name')}</dt>
                        <dd className="mt-1 text-gray-700" data-testid="profile-name-display">
                            {user?.name ?? '—'}
                        </dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">{t('profile.overview.info.email')}</dt>
                        <dd className="mt-1 break-words text-gray-700" data-testid="profile-email-display">
                            {user?.email ?? '—'}
                        </dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">{t('profile.overview.info.verified')}</dt>
                        <dd className="mt-1 text-gray-700">
                            {user?.email_verified_at
                                ? t('profile.overview.info.verifiedYes')
                                : t('profile.overview.info.verifiedNo')}
                        </dd>
                    </div>
                </dl>
                {logoutError && (
                    <div className="mt-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{logoutError}</div>
                )}
                <div className="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-xs text-gray-500">{t('profile.overview.session.tokenNote')}</p>
                    <button
                        type="button"
                        onClick={handleLogout}
                        disabled={logoutPending || isLoading}
                        className="w-full rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                    >
                        {logoutPending || isLoading
                            ? t('profile.overview.session.logout.processing')
                            : t('profile.overview.session.logout.action')}
                    </button>
                </div>
            </div>
            <div className="mt-4 w-full max-w-2xl rounded-lg border bg-white p-8 shadow-sm">
                <h2 className="mb-6 text-xl font-semibold">{t('profile.overview.twoFactor.title')}</h2>
                {twoFactorError && (
                    <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{twoFactorError}</div>
                )}
                {twoFactorMessage && (
                    <div className="mb-4 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">{twoFactorMessage}</div>
                )}
                <div className="space-y-2 text-sm text-gray-700">
                    <p>
                        {t('profile.overview.twoFactor.statusLabel')}{' '}
                        <span className="font-medium text-gray-900">
                            {twoFactorStatusText}
                        </span>
                    </p>
                    {confirmedAtText && (
                        <p>
                            {t('profile.overview.twoFactor.confirmedAtLabel')}{' '}
                            <span className="font-medium text-gray-900">{confirmedAtText}</span>
                        </p>
                    )}
                    <p className="text-xs text-gray-500">{t('profile.overview.twoFactor.description')}</p>
                </div>
                {twoFactorFetching ? (
                    <p className="mt-6 text-sm text-gray-500">{t('profile.overview.twoFactor.loadingStatus')}</p>
                ) : (
                    <div className="mt-6 space-y-6">
                        {twoFactorSetup ? (
                            <div className="space-y-4">
                                <div className="rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                    <p className="font-medium text-gray-900">{t('profile.overview.twoFactor.secret.title')}</p>
                                    <p className="mt-1 break-all font-mono text-xs text-gray-900">{twoFactorSetup.secret}</p>
                                    <p className="mt-3 text-xs text-gray-500">
                                        {t('profile.overview.twoFactor.secret.instructions')}
                                    </p>
                                    <a
                                        href={twoFactorSetup.otpauth_url}
                                        className="mt-3 inline-flex items-center gap-2 text-xs font-medium text-blue-600 hover:text-blue-500"
                                    >
                                        {t('profile.overview.twoFactor.secret.openApp')}
                                    </a>
                                </div>
                                <form onSubmit={handleConfirmTwoFactor} className="space-y-3">
                                    <div className="space-y-1">
                                        <label htmlFor="twoFactorCode" className="block text-sm font-medium text-gray-700">
                                            {t('profile.overview.twoFactor.confirm.codeLabel')}
                                        </label>
                                        <input
                                            id="twoFactorCode"
                                            type="text"
                                            inputMode="numeric"
                                            value={twoFactorCode}
                                            onChange={event => setTwoFactorCode(event.target.value)}
                                            autoComplete="one-time-code"
                                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                                            placeholder={t('profile.overview.twoFactor.confirm.codePlaceholder')}
                                        />
                                        <p className="text-xs text-gray-500">
                                            {t('profile.overview.twoFactor.confirm.helper')}
                                        </p>
                                    </div>
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                        <button
                                            type="submit"
                                            disabled={twoFactorLoading || twoFactorFetching || isLoading}
                                            className="inline-flex w-full items-center justify-center rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                                        >
                                            {twoFactorLoading
                                                ? t('profile.overview.twoFactor.confirm.submitting')
                                                : t('profile.overview.twoFactor.confirm.submit')}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={handleDisableTwoFactor}
                                            disabled={twoFactorLoading || twoFactorFetching || isLoading}
                                            className="inline-flex w-full items-center justify-center rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                                        >
                                            {t('profile.overview.twoFactor.confirm.cancel')}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        ) : twoFactorStatus?.enabled ? (
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <button
                                    type="button"
                                    onClick={handleDisableTwoFactor}
                                    disabled={twoFactorLoading || twoFactorFetching || isLoading}
                                    className="inline-flex w-full items-center justify-center rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                                >
                                    {t('profile.overview.twoFactor.disable.action')}
                                </button>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {twoFactorStatus?.pending && (
                                    <p className="text-xs text-gray-500">
                                        {t('profile.overview.twoFactor.callouts.pendingSetup')}
                                    </p>
                                )}
                                <button
                                    type="button"
                                    onClick={handleStartTwoFactor}
                                    disabled={twoFactorLoading || twoFactorFetching || isLoading}
                                    className="inline-flex items-center justify-center rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {twoFactorLoading
                                        ? t('profile.overview.twoFactor.enable.loading')
                                        : t('profile.overview.twoFactor.enable.action')}
                                </button>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}
