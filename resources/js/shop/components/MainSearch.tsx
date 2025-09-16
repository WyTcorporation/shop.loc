import { FormEvent, useEffect, useMemo, useRef, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Loader2, Search } from 'lucide-react';

import { Input } from '@/components/ui/input';

import {
    fetchSearchSuggestions,
    type SearchSuggestion,
} from '../api';
import { useDebounce } from '../hooks/useDebounce';
import { SUPPORTED_LANGS } from '../i18n/config';
import { formatPrice } from '../ui/format';

const MIN_QUERY_LENGTH = 2;

export default function MainSearch() {
    const [query, setQuery] = useState('');
    const [isFocused, setIsFocused] = useState(false);
    const [loading, setLoading] = useState(false);
    const [suggestions, setSuggestions] = useState<SearchSuggestion[]>([]);
    const [error, setError] = useState<string | null>(null);
    const [retryTick, setRetryTick] = useState(0);

    const debouncedQuery = useDebounce(query, 250);
    const navigate = useNavigate();
    const location = useLocation();

    const inputRef = useRef<HTMLInputElement | null>(null);
    const blurTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const requestIdRef = useRef(0);

    const trimmedQuery = query.trim();

    const langPrefix = useMemo(() => {
        const parts = location.pathname.split('/').filter(Boolean);
        if (parts.length > 0 && SUPPORTED_LANGS.includes(parts[0] as any)) {
            return `/${parts[0]}`;
        }
        return '';
    }, [location.pathname]);

    useEffect(() => {
        return () => {
            if (blurTimeoutRef.current) {
                clearTimeout(blurTimeoutRef.current);
            }
        };
    }, []);

    useEffect(() => {
        const term = debouncedQuery.trim();

        if (term.length < MIN_QUERY_LENGTH) {
            setSuggestions([]);
            setError(null);
            setLoading(false);
            return;
        }

        const requestId = ++requestIdRef.current;

        setLoading(true);
        setError(null);

        fetchSearchSuggestions(term)
            .then((items) => {
                if (requestIdRef.current !== requestId) {
                    return;
                }
                setSuggestions(items);
            })
            .catch((err) => {
                if (requestIdRef.current !== requestId) {
                    return;
                }
                console.error('Failed to fetch search suggestions', err);
                setSuggestions([]);
                setError('Не вдалося завантажити підказки');
            })
            .finally(() => {
                if (requestIdRef.current === requestId) {
                    setLoading(false);
                }
            });
    }, [debouncedQuery, retryTick]);

    const showPanel = isFocused && (
        trimmedQuery.length > 0 ||
        loading ||
        error !== null ||
        suggestions.length > 0
    );

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        const term = trimmedQuery;
        if (term === '') {
            return;
        }

        const base = langPrefix || '';
        navigate(`${base}/?q=${encodeURIComponent(term)}`);

        closePanel();
        inputRef.current?.blur();
    };

    const handleRetry = () => {
        if (trimmedQuery.length >= MIN_QUERY_LENGTH) {
            setRetryTick((tick) => tick + 1);
        }
    };

    const handleSelect = (item: SearchSuggestion) => {
        const base = langPrefix || '';
        const target = `${base}/product/${encodeURIComponent(item.slug)}`;
        navigate(target);
        setQuery('');
        setSuggestions([]);
        closePanel();
        inputRef.current?.blur();
    };

    const handleFocus = () => {
        if (blurTimeoutRef.current) {
            clearTimeout(blurTimeoutRef.current);
            blurTimeoutRef.current = null;
        }
        setIsFocused(true);
    };

    const handleBlur = () => {
        if (blurTimeoutRef.current) {
            clearTimeout(blurTimeoutRef.current);
        }
        blurTimeoutRef.current = setTimeout(() => {
            setIsFocused(false);
        }, 120);
    };

    const closePanel = () => {
        if (blurTimeoutRef.current) {
            clearTimeout(blurTimeoutRef.current);
            blurTimeoutRef.current = null;
        }
        setIsFocused(false);
    };

    return (
        <div className="relative w-full max-w-xl">
            <form onSubmit={handleSubmit} className="w-full">
                <div className="relative">
                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <Input
                        ref={inputRef}
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        onFocus={handleFocus}
                        onBlur={handleBlur}
                        placeholder="Пошук товарів…"
                        className="pl-9"
                        aria-autocomplete="list"
                        aria-expanded={showPanel}
                        role="combobox"
                    />
                </div>
            </form>

            {showPanel && (
                <div className="absolute left-0 right-0 top-full z-40 mt-2 overflow-hidden rounded-lg border bg-white shadow-xl">
                    {trimmedQuery.length < MIN_QUERY_LENGTH ? (
                        <div className="px-4 py-3 text-sm text-gray-500">
                            Введіть щонайменше {MIN_QUERY_LENGTH} символи для пошуку.
                        </div>
                    ) : loading ? (
                        <div className="flex items-center gap-2 px-4 py-3 text-sm text-gray-500">
                            <Loader2 className="h-4 w-4 animate-spin" />
                            Завантаження…
                        </div>
                    ) : error ? (
                        <div className="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                            <span className="text-red-600">{error}</span>
                            <button
                                type="button"
                                onMouseDown={(event) => event.preventDefault()}
                                onClick={handleRetry}
                                className="text-sm font-medium text-blue-600 hover:text-blue-700"
                            >
                                Повторити
                            </button>
                        </div>
                    ) : suggestions.length > 0 ? (
                        <ul className="divide-y text-sm">
                            {suggestions.map((item) => (
                                <li key={item.id}>
                                    <button
                                        type="button"
                                        onMouseDown={(event) => {
                                            event.preventDefault();
                                            handleSelect(item);
                                        }}
                                        className="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-100"
                                    >
                                        {item.preview_url ? (
                                            <img
                                                src={item.preview_url}
                                                alt={item.name}
                                                loading="lazy"
                                                className="h-10 w-10 flex-shrink-0 rounded object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded bg-gray-100 text-xs font-semibold text-gray-500">
                                                {item.name.slice(0, 1)}
                                            </div>
                                        )}
                                        <div className="flex min-w-0 flex-1 flex-col">
                                            <span className="truncate font-medium text-gray-900">{item.name}</span>
                                            {typeof item.price === 'number' && (
                                                <span className="text-xs text-gray-500">
                                                    {formatPrice(item.price, item.currency ?? 'EUR')}
                                                </span>
                                            )}
                                        </div>
                                    </button>
                                </li>
                            ))}
                            <li>
                                <button
                                    type="button"
                                    onMouseDown={(event) => event.preventDefault()}
                                    onClick={() => {
                                        const term = trimmedQuery;
                                        if (term) {
                                            const base = langPrefix || '';
                                            navigate(`${base}/?q=${encodeURIComponent(term)}`);
                                            closePanel();
                                            inputRef.current?.blur();
                                        }
                                    }}
                                    className="flex w-full items-center justify-between gap-3 bg-gray-50 px-4 py-3 text-left text-sm font-medium text-blue-600 hover:bg-gray-100"
                                >
                                    Показати всі результати для “{trimmedQuery}”
                                </button>
                            </li>
                        </ul>
                    ) : (
                        <div className="px-4 py-3 text-sm text-gray-500">Нічого не знайдено</div>
                    )}
                </div>
            )}
        </div>
    );
}
