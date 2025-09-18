import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import ProfileNavigation from '../components/ProfileNavigation';
import { AuthApi, TwoFactorApi, type TwoFactorSetup, type TwoFactorStatus } from '../api';
import useAuth from '../hooks/useAuth';
import { resolveErrorMessage } from '../lib/errors';

export default function ProfilePage() {
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
            setTwoFactorError(resolveErrorMessage(err, 'Не вдалося завантажити статус двофакторної автентифікації.'));
        } finally {
            setTwoFactorFetching(false);
        }
    }, [isAuthenticated]);

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
            setProfileMessage('Дані профілю оновлено.');
            setForm((prev) => ({
                ...prev,
                name: updated?.name ?? prev.name,
                email: updated?.email ?? prev.email,
                password: '',
                password_confirmation: '',
            }));
            await refresh().catch(() => undefined);
        } catch (err) {
            setProfileError(resolveErrorMessage(err, 'Не вдалося оновити профіль. Спробуйте ще раз.'));
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
            setTwoFactorError(resolveErrorMessage(err, 'Не вдалося розпочати налаштування двофакторної автентифікації.'));
        } finally {
            setTwoFactorLoading(false);
        }
    };

    const handleConfirmTwoFactor = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (twoFactorLoading) return;

        const code = twoFactorCode.trim();
        if (!code) {
            setTwoFactorError('Введіть код підтвердження з застосунку.');
            return;
        }

        setTwoFactorError(null);
        setTwoFactorMessage(null);
        setTwoFactorLoading(true);
        try {
            const response = await TwoFactorApi.confirm({ code });
            setTwoFactorMessage(response?.message ?? 'Двофакторну автентифікацію увімкнено.');
            setTwoFactorSetup(null);
            setTwoFactorCode('');
            await loadTwoFactorStatus();
            await refresh().catch(() => undefined);
        } catch (err) {
            setTwoFactorError(resolveErrorMessage(err, 'Не вдалося підтвердити код. Спробуйте ще раз.'));
        } finally {
            setTwoFactorLoading(false);
        }
    };

    const handleDisableTwoFactor = async () => {
        if (!window.confirm('Ви впевнені, що хочете вимкнути двофакторну автентифікацію?')) {
            return;
        }

        setTwoFactorError(null);
        setTwoFactorMessage(null);
        setTwoFactorLoading(true);
        try {
            await TwoFactorApi.disable();
            setTwoFactorSetup(null);
            setTwoFactorCode('');
            setTwoFactorMessage('Двофакторну автентифікацію вимкнено.');
            await loadTwoFactorStatus();
            await refresh().catch(() => undefined);
        } catch (err) {
            setTwoFactorError(resolveErrorMessage(err, 'Не вдалося вимкнути двофакторну автентифікацію.'));
        } finally {
            setTwoFactorLoading(false);
        }
    };

    const confirmedAtText = React.useMemo(() => {
        if (!twoFactorStatus?.confirmed_at) return null;
        try {
            return new Date(twoFactorStatus.confirmed_at).toLocaleString('uk-UA');
        } catch {
            return twoFactorStatus.confirmed_at;
        }
    }, [twoFactorStatus?.confirmed_at]);

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
        setLogoutError(null);
        setLogoutPending(true);
        try {
            await logout();
        } catch (err) {
            setLogoutError(resolveErrorMessage(err, 'Не вдалося вийти. Спробуйте ще раз.'));
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
            setVerificationMessage(response?.message ?? 'Лист для підтвердження повторно надіслано.');
        } catch (err) {
            setVerificationError(
                resolveErrorMessage(err, 'Не вдалося надіслати лист підтвердження. Спробуйте ще раз.'),
            );
        } finally {
            setVerificationSending(false);
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
                        <p className="font-medium">Електронну пошту не підтверджено.</p>
                        <p className="mt-1 text-yellow-700">
                            Перевірте поштову скриньку або надішліть лист із підтвердженням повторно.
                        </p>
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
                                    Надсилання…
                                </>
                            ) : (
                                'Надіслати лист повторно'
                            )}
                        </button>
                    </div>
                )}
                <form onSubmit={handleProfileSubmit} className="space-y-4" data-testid="profile-form">
                    <div>
                        <label htmlFor="profileName" className="block text-sm font-medium text-gray-700">
                            Ім'я
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
                            placeholder="Введіть ім'я"
                        />
                    </div>
                    <div>
                        <label htmlFor="profileEmail" className="block text-sm font-medium text-gray-700">
                            Email
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
                            placeholder="your@email.com"
                        />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label htmlFor="profilePassword" className="block text-sm font-medium text-gray-700">
                                Новий пароль
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
                                placeholder="Залиште порожнім, щоб не змінювати"
                            />
                        </div>
                        <div>
                            <label htmlFor="profilePasswordConfirmation" className="block text-sm font-medium text-gray-700">
                                Підтвердження пароля
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
                                placeholder="Повторіть новий пароль"
                            />
                        </div>
                    </div>
                    <p className="text-xs text-gray-500">
                        Пароль можна не заповнювати, якщо ви не плануєте його змінювати. Email повинен бути унікальним.
                    </p>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-xs text-gray-500">Зміни набувають чинності одразу після збереження.</p>
                        <button
                            type="submit"
                            disabled={profileSaving || isLoading}
                            data-testid="profile-save"
                            className="inline-flex w-full items-center justify-center rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                        >
                            {profileSaving || isLoading ? 'Збереження…' : 'Зберегти зміни'}
                        </button>
                    </div>
                </form>
                <dl className="mt-8 space-y-4 text-sm text-gray-700">
                    <div>
                        <dt className="font-medium text-gray-900">ID</dt>
                        <dd className="mt-1 text-gray-700" data-testid="profile-id">
                            {user?.id ?? '—'}
                        </dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">Ім'я</dt>
                        <dd className="mt-1 text-gray-700" data-testid="profile-name-display">
                            {user?.name ?? '—'}
                        </dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">Email</dt>
                        <dd className="mt-1 break-words text-gray-700" data-testid="profile-email-display">
                            {user?.email ?? '—'}
                        </dd>
                    </div>
                    <div>
                        <dt className="font-medium text-gray-900">Email підтверджено</dt>
                        <dd className="mt-1 text-gray-700">{user?.email_verified_at ? 'Так' : 'Ні'}</dd>
                    </div>
                </dl>
                {logoutError && (
                    <div className="mt-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{logoutError}</div>
                )}
                <div className="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-xs text-gray-500">Токен Sanctum збережено локально для авторизованих запитів до API.</p>
                    <button
                        type="button"
                        onClick={handleLogout}
                        disabled={logoutPending || isLoading}
                        className="w-full rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                    >
                        {logoutPending || isLoading ? 'Вихід…' : 'Вийти'}
                    </button>
                </div>
            </div>
            <div className="mt-4 w-full max-w-2xl rounded-lg border bg-white p-8 shadow-sm">
                <h2 className="mb-6 text-xl font-semibold">Двофакторна автентифікація</h2>
                {twoFactorError && (
                    <div className="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{twoFactorError}</div>
                )}
                {twoFactorMessage && (
                    <div className="mb-4 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">{twoFactorMessage}</div>
                )}
                <div className="space-y-2 text-sm text-gray-700">
                    <p>
                        Статус:{' '}
                        <span className="font-medium text-gray-900">
                            {twoFactorStatus?.enabled
                                ? 'Увімкнено'
                                : twoFactorStatus?.pending
                                    ? 'Очікує підтвердження'
                                    : 'Вимкнено'}
                        </span>
                    </p>
                    {confirmedAtText && (
                        <p>
                            Підтверджено:{' '}
                            <span className="font-medium text-gray-900">{confirmedAtText}</span>
                        </p>
                    )}
                    <p className="text-xs text-gray-500">
                        Двофакторна автентифікація додає додатковий рівень безпеки для вашого облікового запису.
                    </p>
                </div>
                {twoFactorFetching ? (
                    <p className="mt-6 text-sm text-gray-500">Завантаження статусу…</p>
                ) : (
                    <div className="mt-6 space-y-6">
                        {twoFactorSetup ? (
                            <div className="space-y-4">
                                <div className="rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                    <p className="font-medium text-gray-900">Секретний ключ</p>
                                    <p className="mt-1 break-all font-mono text-xs text-gray-900">{twoFactorSetup.secret}</p>
                                    <p className="mt-3 text-xs text-gray-500">
                                        Додайте цей ключ у застосунок автентифікації (Google Authenticator, 1Password, Authy тощо).
                                        Ви також можете відкрити налаштування безпосередньо за посиланням нижче.
                                    </p>
                                    <a
                                        href={twoFactorSetup.otpauth_url}
                                        className="mt-3 inline-flex items-center gap-2 text-xs font-medium text-blue-600 hover:text-blue-500"
                                    >
                                        Відкрити в застосунку
                                    </a>
                                </div>
                                <form onSubmit={handleConfirmTwoFactor} className="space-y-3">
                                    <div className="space-y-1">
                                        <label htmlFor="twoFactorCode" className="block text-sm font-medium text-gray-700">
                                            Код підтвердження
                                        </label>
                                        <input
                                            id="twoFactorCode"
                                            type="text"
                                            inputMode="numeric"
                                            value={twoFactorCode}
                                            onChange={event => setTwoFactorCode(event.target.value)}
                                            autoComplete="one-time-code"
                                            className="w-full rounded border px-3 py-2 text-sm focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                                            placeholder="Введіть код з застосунку"
                                        />
                                        <p className="text-xs text-gray-500">
                                            Введіть шестизначний код з вашого застосунку автентифікації, щоб завершити налаштування.
                                        </p>
                                    </div>
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                        <button
                                            type="submit"
                                            disabled={twoFactorLoading || twoFactorFetching || isLoading}
                                            className="inline-flex w-full items-center justify-center rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                                        >
                                            {twoFactorLoading ? 'Підтвердження…' : 'Підтвердити'}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={handleDisableTwoFactor}
                                            disabled={twoFactorLoading || twoFactorFetching || isLoading}
                                            className="inline-flex w-full items-center justify-center rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                                        >
                                            Скасувати
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
                                    Вимкнути 2FA
                                </button>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {twoFactorStatus?.pending && (
                                    <p className="text-xs text-gray-500">
                                        Попереднє налаштування не завершено. Ви можете згенерувати новий секретний ключ, щоб почати знову.
                                    </p>
                                )}
                                <button
                                    type="button"
                                    onClick={handleStartTwoFactor}
                                    disabled={twoFactorLoading || twoFactorFetching || isLoading}
                                    className="inline-flex items-center justify-center rounded bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-black/90 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {twoFactorLoading ? 'Зачекайте…' : 'Увімкнути 2FA'}
                                </button>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}
