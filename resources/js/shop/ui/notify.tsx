import React, {createContext, useCallback, useContext, useMemo, useRef, useState} from 'react';

type ToastAction = { label: string; onClick: () => void };
type Variant = 'success' | 'error' | 'info';

type ToastInput =
    | string
    | {
    title?: React.ReactNode;
    description?: React.ReactNode;
    action?: ToastAction;
    variant?: Variant;
    autoCloseMs?: number | null; // null/0 — не закривати
};

type Toast = {
    id: string;
    title?: React.ReactNode;
    description?: React.ReactNode;
    action?: ToastAction;
    variant: Variant;
    autoCloseMs: number | null;
};

type Ctx = {
    success: (t: ToastInput) => void;
    error: (t: ToastInput) => void;
    info: (t: ToastInput) => void;
    clear: (id: string) => void;
    clearAll: () => void;
};

const NotifyCtx = createContext<Ctx | null>(null);

export function useNotify(): Ctx {
    const ctx = useContext(NotifyCtx);
    if (!ctx) throw new Error('useNotify must be used within <NotifyProvider>');
    return ctx;
}

function normalize(input: ToastInput, fallbackVariant: Variant, defaultAutoClose: number | null): Toast {
    const base: Partial<Toast> =
        typeof input === 'string'
            ? { title: input }
            : { title: input.title, description: input.description, action: input.action, variant: input.variant };

    return {
        id: Math.random().toString(36).slice(2),
        title: base.title,
        description: base.description,
        action: base.action,
        variant: (base.variant as Variant) ?? fallbackVariant,
        autoCloseMs:
            typeof input === 'object' && input && 'autoCloseMs' in input
                ? (input as any).autoCloseMs ?? defaultAutoClose
                : defaultAutoClose,
    };
}

export function NotifyProvider({
                                   children,
                                   autoCloseMs = 3000,
                               }: {
    children: React.ReactNode;
    /** 0 або null — не закривати автоматично */
    autoCloseMs?: number | null;
}) {
    const [toasts, setToasts] = useState<Toast[]>([]);
    const timers = useRef<Record<string, number>>({});

    const clear = useCallback((id: string) => {
        setToasts((list) => list.filter((t) => t.id !== id));
        const tm = timers.current[id];
        if (tm) {
            window.clearTimeout(tm);
            delete timers.current[id];
        }
    }, []);

    const clearAll = useCallback(() => {
        setToasts([]);
        Object.values(timers.current).forEach((tm) => window.clearTimeout(tm));
        timers.current = {};
    }, []);

    const push = useCallback(
        (input: ToastInput, variant: Variant) => {
            const t = normalize(input, variant, autoCloseMs ?? null);
            setToasts((list) => [t, ...list]);

            if (t.autoCloseMs && t.autoCloseMs > 0) {
                timers.current[t.id] = window.setTimeout(() => clear(t.id), t.autoCloseMs);
            }
        },
        [autoCloseMs, clear]
    );

    const api = useMemo<Ctx>(
        () => ({
            success: (t) => push(t, 'success'),
            error: (t) => push(t, 'error'),
            info: (t) => push(t, 'info'),
            clear,
            clearAll,
        }),
        [push, clear, clearAll]
    );

    return (
        <NotifyCtx.Provider value={api}>
            {children}

            {/* Контейнер тостів */}
            <div className="fixed inset-x-0 bottom-3 z-50 mx-auto flex w-full max-w-xl flex-col gap-2 px-3 sm:bottom-4 sm:right-4 sm:left-auto sm:mx-0 sm:w-96">
                {toasts.map((t) => (
                    <div
                        key={t.id}
                        role="alert"
                        className={`rounded-lg border p-3 shadow-md bg-white ${
                            t.variant === 'success'
                                ? 'border-green-200'
                                : t.variant === 'error'
                                    ? 'border-red-200'
                                    : 'border-gray-200'
                        }`}
                    >
                        <div className="flex items-start gap-3">
                            <div className="flex-1">
                                {t.title ? <div className="font-medium">{t.title}</div> : null}
                                {t.description ? (
                                    <div className="mt-0.5 text-sm text-gray-600">{t.description}</div>
                                ) : null}
                                {t.action ? (
                                    <button
                                        className="mt-2 inline-flex rounded-md border px-2 py-1 text-xs hover:bg-gray-50"
                                        onClick={() => {
                                            try {
                                                t.action!.onClick();
                                            } finally {
                                                clear(t.id);
                                            }
                                        }}
                                    >
                                        {t.action.label}
                                    </button>
                                ) : null}
                            </div>
                            <button
                                aria-label="Close"
                                className="rounded p-1 text-gray-500 hover:bg-gray-100"
                                onClick={() => clear(t.id)}
                            >
                                ×
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </NotifyCtx.Provider>
    );
}
