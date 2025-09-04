import React, {createContext, useCallback, useContext, useMemo, useState} from 'react';
import { createPortal } from 'react-dom';

type Action = { label: string; onClick: () => void };
type Toast = { id: number; type: 'success'|'error'|'info'; title: string; description?: string; action?: Action; ttl?: number };

const Ctx = createContext<{
    push: (t: Omit<Toast,'id'>) => void;
    success: (title: string, opts?: Partial<Omit<Toast,'id'|'type'|'title'>>) => void;
    error: (title: string, opts?: Partial<Omit<Toast,'id'|'type'|'title'>>) => void;
    clearAll: () => void;
} | null>(null);

export const NotifyProvider: React.FC<{ children: React.ReactNode; autoCloseMs?: number }> = ({ children,
                                                                            autoCloseMs = 0 }) => {
    const [toasts, setToasts] = useState<Toast[]>([]);
    const clearAll = useCallback(() => setToasts([]), []);
    const push = useCallback((t: Omit<Toast,'id'>) => {
        const id = Date.now() + Math.floor(Math.random()*1000);
        const ttl = typeof t.ttl === 'number' ? t.ttl : autoCloseMs;
        const toast: Toast = { id, ...t, ttl };
        setToasts((arr) => [...arr, toast]);
        if (ttl > 0) setTimeout(() => setToasts((arr) => arr.filter(x => x.id !== id)), ttl);
    }, []);
    const success = useCallback((title: string, opts?: Partial<Omit<Toast,'id'|'type'|'title'>>) => push({ type:'success', title, ...opts }), [push]);
    const error   = useCallback((title: string, opts?: Partial<Omit<Toast,'id'|'type'|'title'>>) => push({ type:'error',   title, ...opts }), [push]);

    const value = useMemo(() => ({ push, success, error, clearAll }), [push, success, error, clearAll]);

    return (
        <Ctx.Provider value={value}>
            {children}
            {createPortal(
                <div className="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2">
                    {toasts.map(t => (
                        <div key={t.id}
                             className={`min-w-[260px] max-w-[360px] rounded-xl border px-4 py-3 shadow-md bg-white
                   ${t.type==='success' ? 'border-emerald-300' : t.type==='error' ? 'border-red-300' : 'border-gray-300'}`}>
                            <div className="flex justify-between items-start gap-3">
                                <div>
                                    <div className="font-medium">{t.title}</div>
                                    {t.description ? <div className="text-sm text-gray-600 mt-0.5">{t.description}</div> : null}
                                </div>
                                <button
                                    className="text-gray-400 hover:text-gray-700"
                                    onClick={() => setToasts(arr => arr.filter(x => x.id !== t.id))}
                                    aria-label="Dismiss"
                                >×</button>
                            </div>
                            {t.action ? (
                                <div className="mt-2">
                                    <button
                                        onClick={t.action.onClick}
                                        className="text-sm underline underline-offset-2 hover:no-underline">
                                        {t.action.label}
                                    </button>
                                </div>
                            ) : null}
                        </div>
                    ))}
                </div>,
                document.body
            )}
        </Ctx.Provider>
    );
};

export function useNotify() {
    const ctx = useContext(Ctx);
    if (!ctx) throw new Error('useNotify must be used within <NotifyProvider>');
    return ctx;
}
