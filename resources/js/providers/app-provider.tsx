import { router } from '@inertiajs/react';
import React, {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useRef,
    useState,
    type ReactNode,
} from 'react';

type Option = {
    value: string;
    label: string;
};

type Dictionary = Record<string, unknown>;

export interface AppContextValue {
    locale: string;
    currency: string;
    dictionary: Dictionary;
    isDictionaryLoading: boolean;
    locales: Option[];
    currencies: Option[];
    setLocale: (locale: string) => void;
    setCurrency: (currency: string) => void;
}

interface AppProviderProps {
    children: ReactNode;
    initialLocale?: string;
    initialCurrency?: string;
    locales?: Option[];
    currencies?: Option[];
}

const DEFAULT_LOCALE_OPTIONS: Option[] = [
    { value: 'uk', label: 'Українська' },
    { value: 'en', label: 'English' },
];

const FALLBACK_BASE_CURRENCY = { code: 'EUR', symbol: '€' } as const;

function readBaseCurrencyMeta(): { code: string; symbol: string } {
    if (typeof document !== 'undefined') {
        const { baseCurrency, baseCurrencySymbol } = document.documentElement.dataset;

        if (baseCurrency) {
            const code = baseCurrency.trim().toUpperCase();
            const symbol = (baseCurrencySymbol ?? '').trim();

            return {
                code: code || FALLBACK_BASE_CURRENCY.code,
                symbol: symbol || code || FALLBACK_BASE_CURRENCY.symbol,
            };
        }
    }

    if (typeof globalThis !== 'undefined' && typeof (globalThis as Record<string, unknown>).APP === 'object') {
        const app = (globalThis as Record<string, any>).APP;
        const code = typeof app?.baseCurrency === 'string' ? app.baseCurrency.trim().toUpperCase() : '';
        const symbol = typeof app?.baseCurrencySymbol === 'string' ? app.baseCurrencySymbol.trim() : '';

        if (code) {
            return {
                code,
                symbol: symbol || code,
            };
        }
    }

    return FALLBACK_BASE_CURRENCY;
}

function buildDefaultCurrencyOptions(): Option[] {
    const base = readBaseCurrencyMeta();
    const baseOption: Option = {
        value: base.code,
        label: `${base.code} ${base.symbol}`.trim(),
    };

    const extras: Option[] = [
        { value: 'USD', label: 'USD $' },
        { value: 'UAH', label: 'UAH ₴' },
    ];

    return [baseOption, ...extras.filter((option) => option.value !== baseOption.value)];
}

const DEFAULT_CURRENCY_OPTIONS: Option[] = buildDefaultCurrencyOptions();

const LOCALE_STORAGE_KEY = 'app.locale';
const CURRENCY_STORAGE_KEY = 'app.currency';
const LOCALE_COOKIE_KEY = 'lang';
const CURRENCY_COOKIE_KEY = 'currency';

const AppContext = createContext<AppContextValue | null>(null);

const safeStorage = {
    get(key: string) {
        if (typeof window === 'undefined') {
            return null;
        }

        try {
            return window.localStorage.getItem(key);
        } catch (error) {
            console.warn(`[AppProvider] Unable to read localStorage key "${key}"`, error);
            return null;
        }
    },
    set(key: string, value: string) {
        if (typeof window === 'undefined') {
            return;
        }

        try {
            window.localStorage.setItem(key, value);
        } catch (error) {
            console.warn(`[AppProvider] Unable to write localStorage key "${key}"`, error);
        }
    },
};

function getCookie(name: string): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const pattern = new RegExp(`(?:^|; )${name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}=([^;]*)`);
    const match = pattern.exec(document.cookie);

    return match ? decodeURIComponent(match[1]) : null;
}

function setCookie(name: string, value: string, days = 365) {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)};path=/;max-age=${maxAge};SameSite=Lax`;
}

function normalizeLocale(raw: string | null | undefined, allowed: Set<string>): string | null {
    if (!raw) {
        return null;
    }

    const trimmed = raw.trim();
    if (!trimmed) {
        return null;
    }

    const canonical = trimmed.toLowerCase().replace('_', '-');
    if (allowed.has(canonical)) {
        return canonical;
    }

    const base = canonical.split('-')[0];
    if (allowed.has(base)) {
        return base;
    }

    return null;
}

function normalizeCurrency(raw: string | null | undefined, allowed: Set<string>): string | null {
    if (!raw) {
        return null;
    }

    const canonical = raw.trim().toUpperCase();
    if (!canonical) {
        return null;
    }

    return allowed.has(canonical) ? canonical : null;
}

function detectDocumentLocale(): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    return document.documentElement.lang || null;
}

function detectNavigatorLocale(): string | null {
    if (typeof navigator === 'undefined') {
        return null;
    }

    const language = navigator.language || (Array.isArray(navigator.languages) ? navigator.languages[0] : null);
    return language || null;
}

function pickLocale(
    candidates: Array<string | null | undefined>,
    allowed: Set<string>,
    fallback: string,
): string {
    for (const candidate of candidates) {
        const normalized = normalizeLocale(candidate, allowed);
        if (normalized) {
            return normalized;
        }
    }

    return fallback;
}

function pickCurrency(
    candidates: Array<string | null | undefined>,
    allowed: Set<string>,
    fallback: string,
): string {
    for (const candidate of candidates) {
        const normalized = normalizeCurrency(candidate, allowed);
        if (normalized) {
            return normalized;
        }
    }

    return fallback;
}

export function AppProvider({
    children,
    initialLocale,
    initialCurrency,
    locales,
    currencies,
}: AppProviderProps) {
    const localeOptions = useMemo(
        () => (locales && locales.length ? locales : DEFAULT_LOCALE_OPTIONS),
        [locales],
    );

    const currencyOptions = useMemo(
        () => (currencies && currencies.length ? currencies : DEFAULT_CURRENCY_OPTIONS),
        [currencies],
    );

    const localeValues = useMemo(() => new Set(localeOptions.map((option) => option.value)), [localeOptions]);
    const currencyValues = useMemo(() => new Set(currencyOptions.map((option) => option.value)), [currencyOptions]);

    const localeFallback = localeOptions[0]?.value ?? DEFAULT_LOCALE_OPTIONS[0].value;
    const currencyFallback = currencyOptions[0]?.value ?? DEFAULT_CURRENCY_OPTIONS[0].value;

    const [locale, setLocaleState] = useState(() =>
        pickLocale(
            [
                initialLocale,
                safeStorage.get(LOCALE_STORAGE_KEY),
                getCookie(LOCALE_COOKIE_KEY),
                detectDocumentLocale(),
                detectNavigatorLocale(),
            ],
            localeValues,
            localeFallback,
        ),
    );

    const [currency, setCurrencyState] = useState(() =>
        pickCurrency(
            [initialCurrency, safeStorage.get(CURRENCY_STORAGE_KEY), getCookie(CURRENCY_COOKIE_KEY)],
            currencyValues,
            currencyFallback,
        ),
    );

    const [dictionary, setDictionary] = useState<Dictionary>({});
    const [isDictionaryLoading, setIsDictionaryLoading] = useState(true);
    const dictionaryCacheRef = useRef<Record<string, Dictionary>>({});

    const currencyUpdateSourceRef = useRef<'server' | 'client'>('server');

    useEffect(() => {
        if (!initialLocale) {
            return;
        }

        const normalized = normalizeLocale(initialLocale, localeValues);
        if (normalized && normalized !== locale) {
            setLocaleState(normalized);
        }
    }, [initialLocale, localeValues, locale]);

    useEffect(() => {
        if (!initialCurrency) {
            return;
        }

        const normalized = normalizeCurrency(initialCurrency, currencyValues);
        if (normalized && normalized !== currency) {
            currencyUpdateSourceRef.current = 'server';
            setCurrencyState(normalized);
        }
    }, [initialCurrency, currencyValues, currency]);

    useEffect(() => {
        safeStorage.set(LOCALE_STORAGE_KEY, locale);
        setCookie(LOCALE_COOKIE_KEY, locale);

        if (typeof document !== 'undefined') {
            document.documentElement.lang = locale;
        }
    }, [locale]);

    useEffect(() => {
        safeStorage.set(CURRENCY_STORAGE_KEY, currency);
        setCookie(CURRENCY_COOKIE_KEY, currency);
    }, [currency]);

    useEffect(() => {
        const cached = dictionaryCacheRef.current[locale];
        if (cached) {
            setDictionary(cached);
            setIsDictionaryLoading(false);
            return;
        }

        if (typeof fetch !== 'function') {
            setDictionary({});
            setIsDictionaryLoading(false);
            return;
        }

        let isActive = true;
        const controller = typeof AbortController !== 'undefined' ? new AbortController() : undefined;

        setIsDictionaryLoading(true);

        (async () => {
            try {
                const response = await fetch(`/lang/${locale}.json`, controller ? { signal: controller.signal } : undefined);

                if (!response.ok) {
                    throw new Error(
                        `[AppProvider] Failed to load dictionary for locale "${locale}": ${response.status} ${response.statusText}`,
                    );
                }

                const data = (await response.json()) as Dictionary;

                if (!isActive) {
                    return;
                }

                dictionaryCacheRef.current[locale] = data ?? {};
                setDictionary(data ?? {});
            } catch (error) {
                if (!isActive || (error as Error)?.name === 'AbortError') {
                    return;
                }

                console.error(error);
                dictionaryCacheRef.current[locale] = {};
                setDictionary({});
            } finally {
                if (isActive) {
                    setIsDictionaryLoading(false);
                }
            }
        })();

        return () => {
            isActive = false;
            controller?.abort();
        };
    }, [locale]);

    useEffect(() => {
        if (currencyUpdateSourceRef.current === 'server') {
            currencyUpdateSourceRef.current = 'client';
            return;
        }

        if (typeof window === 'undefined') {
            return;
        }

        router.reload({
            data: { currency },
            preserveScroll: true,
            preserveState: true,
        });
    }, [currency]);

    const setLocale = useCallback(
        (next: string) => {
            const normalized = normalizeLocale(next, localeValues);
            if (!normalized || normalized === locale) {
                return;
            }

            setLocaleState(normalized);
        },
        [locale, localeValues],
    );

    const setCurrency = useCallback(
        (next: string) => {
            const normalized = normalizeCurrency(next, currencyValues);
            if (!normalized || normalized === currency) {
                return;
            }

            currencyUpdateSourceRef.current = 'client';
            setCurrencyState(normalized);
        },
        [currency, currencyValues],
    );

    const value = useMemo<AppContextValue>(
        () => ({
            locale,
            currency,
            dictionary,
            isDictionaryLoading,
            locales: localeOptions,
            currencies: currencyOptions,
            setLocale,
            setCurrency,
        }),
        [
            currency,
            currencyOptions,
            dictionary,
            isDictionaryLoading,
            locale,
            localeOptions,
            setCurrency,
            setLocale,
        ],
    );

    return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp() {
    const context = useContext(AppContext);

    if (!context) {
        throw new Error('useApp must be used within an AppProvider');
    }

    return context;
}

