import { useCallback } from 'react';
import { useSearchParams } from 'react-router-dom';

export function useQueryParam(key: string, initial = '') {
    const [params, setParams] = useSearchParams();

    const value = params.get(key) ?? initial;

    const setValue = useCallback((v: string) => {
        const next = new URLSearchParams(params);
        if (v && v.length) next.set(key, v);
        else next.delete(key);
        setParams(next, { replace: true });
    }, [key, params, setParams]);

    return [value, setValue] as const;
}
