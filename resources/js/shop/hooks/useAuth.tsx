import React from 'react';
import {AuthApi, api, type AuthUser} from '../api';

export type LoginPayload = {
    email: string;
    password: string;
    remember?: boolean;
    otp?: string;
};

export type RegisterPayload = {
    name: string;
    email: string;
    password: string;
    password_confirmation?: string;
    [key: string]: unknown;
};

type AuthContextValue = {
    user: AuthUser | null;
    token: string | null;
    isAuthenticated: boolean;
    isReady: boolean;
    isLoading: boolean;
    login: (payload: LoginPayload) => Promise<AuthUser>;
    register: (payload: RegisterPayload) => Promise<AuthUser>;
    logout: () => Promise<void>;
    refresh: () => Promise<AuthUser | null>;
    setToken: (value: string | null) => void;
};

const SANCTUM_TOKEN_KEY = 'sanctum_token';

const AuthContext = React.createContext<AuthContextValue | null>(null);

export function AuthProvider({children}: {children: React.ReactNode}) {
    const [token, setTokenState] = React.useState<string | null>(() => {
        if (typeof window === 'undefined') return null;
        return window.localStorage.getItem(SANCTUM_TOKEN_KEY);
    });
    const [user, setUser] = React.useState<AuthUser | null>(null);
    const [isReady, setIsReady] = React.useState<boolean>(() => !token);
    const [isLoading, setIsLoading] = React.useState<boolean>(() => Boolean(token));

    React.useEffect(() => {
        if (typeof window === 'undefined') return;
        if (token) {
            window.localStorage.setItem(SANCTUM_TOKEN_KEY, token);
            api.defaults.headers.common.Authorization = `Bearer ${token}`;
        } else {
            window.localStorage.removeItem(SANCTUM_TOKEN_KEY);
            delete api.defaults.headers.common.Authorization;
        }
    }, [token]);

    React.useEffect(() => {
        if (typeof window === 'undefined') return;
        const handleStorage = (event: StorageEvent) => {
            if (event.key === SANCTUM_TOKEN_KEY) {
                setTokenState(event.newValue);
            }
        };
        window.addEventListener('storage', handleStorage);
        return () => window.removeEventListener('storage', handleStorage);
    }, []);

    const setToken = React.useCallback((value: string | null) => {
        setTokenState(value);
    }, []);

    const refresh = React.useCallback(async () => {
        if (!token) {
            setUser(null);
            setIsReady(true);
            return null;
        }
        const profile = await AuthApi.me();
        setUser(profile);
        setIsReady(true);
        return profile;
    }, [token]);

    React.useEffect(() => {
        let ignore = false;

        if (!token) {
            setUser(null);
            setIsReady(true);
            setIsLoading(false);
            return;
        }

        setIsReady(false);
        setIsLoading(true);

        refresh()
            .catch(error => {
                if (ignore) return;
                console.error('Failed to load profile', error);
                setTokenState(null);
                setUser(null);
                setIsReady(true);
            })
            .finally(() => {
                if (ignore) return;
                setIsLoading(false);
            });

        return () => {
            ignore = true;
        };
    }, [token, refresh]);

    const login = React.useCallback(async (payload: LoginPayload) => {
        setIsLoading(true);
        try {
            const {token: newToken, user: profile} = await AuthApi.login(payload);
            setTokenState(newToken);
            setUser(profile);
            setIsReady(true);
            return profile;
        } catch (error) {
            setIsReady(true);
            throw error;
        } finally {
            setIsLoading(false);
        }
    }, []);

    const register = React.useCallback(async (payload: RegisterPayload) => {
        setIsLoading(true);
        try {
            const {token: newToken, user: profile} = await AuthApi.register(payload);
            setTokenState(newToken);
            setUser(profile);
            setIsReady(true);
            return profile;
        } catch (error) {
            setIsReady(true);
            throw error;
        } finally {
            setIsLoading(false);
        }
    }, []);

    const logout = React.useCallback(async () => {
        setIsLoading(true);
        try {
            await AuthApi.logout();
        } finally {
            setTokenState(null);
            setUser(null);
            setIsReady(true);
            setIsLoading(false);
        }
    }, []);

    const value = React.useMemo<AuthContextValue>(() => ({
        user,
        token,
        isAuthenticated: Boolean(token && user),
        isReady,
        isLoading,
        login,
        register,
        logout,
        refresh,
        setToken,
    }), [isLoading, isReady, login, logout, refresh, register, setToken, token, user]);

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export default function useAuth(): AuthContextValue {
    const ctx = React.useContext(AuthContext);
    if (!ctx) throw new Error('useAuth must be used within AuthProvider');
    return ctx;
}
