import { useCallback } from 'react';
import { useSearchParams } from 'react-router-dom';

export function useQueryParamNumber(key: string, initial?: number) {
    const [params, setParams] = useSearchParams();
    const raw = params.get(key);
    const value = raw == null || raw === '' ? initial : Number(raw);

    const setValue = useCallback((v: number | undefined) => {
        const next = new URLSearchParams(params);
        if (typeof v === 'number' && !Number.isNaN(v)) next.set(key, String(v));
        else next.delete(key);
        setParams(next, { replace: true });
    }, [key, params, setParams]);

    return [value, setValue] as const;
}
