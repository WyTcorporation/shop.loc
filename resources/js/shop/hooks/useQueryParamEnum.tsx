import { useCallback } from 'react';
import { useSearchParams } from 'react-router-dom';

export function useQueryParamEnum<T extends string>(key: string, allowed: readonly T[], initial: T) {
    const [params, setParams] = useSearchParams();
    const raw = params.get(key) as T | null;
    const value = (raw && allowed.includes(raw)) ? raw : initial;

    const setValue = useCallback((v: T) => {
        const next = new URLSearchParams(params);
        next.set(key, v);
        setParams(next, { replace: true });
    }, [key, params, setParams]);

    return [value, setValue] as const;
}
