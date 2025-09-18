import clsx from 'clsx';
import React from 'react';
import { Link } from 'react-router-dom';
import { OrdersApi, type OrderMessage } from '../api';
import useAuth from '../hooks/useAuth';
import { resolveErrorMessage } from '../lib/errors';
import { formatDateTime } from '../lib/datetime';
import { Button } from '@/components/ui/button';
import { useLocale } from '../i18n/LocaleProvider';

type OrderChatProps = {
    orderId: number;
    orderNumber?: string;
    className?: string;
};

export default function OrderChat({ orderId, orderNumber, className }: OrderChatProps) {
    const { isAuthenticated, isReady, user } = useAuth();
    const { t, locale } = useLocale();
    const [messages, setMessages] = React.useState<OrderMessage[]>([]);
    const [loading, setLoading] = React.useState(true);
    const [loadError, setLoadError] = React.useState<string | null>(null);
    const [input, setInput] = React.useState('');
    const [sending, setSending] = React.useState(false);
    const [sendError, setSendError] = React.useState<string | null>(null);
    const listRef = React.useRef<HTMLDivElement | null>(null);

    React.useEffect(() => {
        if (!isReady) {
            return;
        }

        if (!isAuthenticated) {
            setMessages([]);
            setLoading(false);
            return;
        }

        let ignore = false;
        setLoadError(null);
        setLoading(true);

        OrdersApi.listMessages(orderId)
            .then((data) => {
                if (!ignore) {
                    setMessages(data);
                }
            })
            .catch((error) => {
                if (!ignore) {
                    setLoadError(resolveErrorMessage(error, () => t('orderChat.errors.load')));
                }
            })
            .finally(() => {
                if (!ignore) {
                    setLoading(false);
                }
            });

        return () => {
            ignore = true;
        };
    }, [isAuthenticated, isReady, orderId, t]);

    const refreshMessages = React.useCallback(async () => {
        if (!isAuthenticated) {
            return;
        }

        setLoadError(null);
        setLoading(true);
        try {
            const data = await OrdersApi.listMessages(orderId);
            setMessages(data);
        } catch (error) {
            setLoadError(resolveErrorMessage(error, () => t('orderChat.errors.load')));
        } finally {
            setLoading(false);
        }
    }, [isAuthenticated, orderId, t]);

    React.useEffect(() => {
        if (!listRef.current) return;
        listRef.current.scrollTop = listRef.current.scrollHeight;
    }, [messages]);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const trimmed = input.trim();
        if (!trimmed) {
            return;
        }

        setSendError(null);
        setSending(true);
        try {
            const message = await OrdersApi.sendMessage(orderId, trimmed);
            setMessages((prev) => [...prev, message]);
            setInput('');
            requestAnimationFrame(() => {
                if (listRef.current) {
                    listRef.current.scrollTop = listRef.current.scrollHeight;
                }
            });
        } catch (error) {
            setSendError(resolveErrorMessage(error, () => t('orderChat.errors.send')));
        } finally {
            setSending(false);
        }
    };

    const canSend = Boolean(input.trim()) && !sending;

    return (
        <div className={clsx('space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm', className)}>
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="text-lg font-semibold">{t('orderChat.title')}</h2>
                    {orderNumber && (
                        <p className="text-xs text-gray-500">
                            {t('orderChat.orderLabel', { number: orderNumber })}
                        </p>
                    )}
                </div>
                {isAuthenticated && (
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={refreshMessages}
                        disabled={loading || sending}
                    >
                        {t('orderChat.actions.refresh')}
                    </Button>
                )}
            </div>

            {loadError && (
                <div className="rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {loadError}
                </div>
            )}

            <div className="rounded-lg border bg-gray-50">
                {loading ? (
                    <div className="py-8 text-center text-sm text-gray-500">{t('orderChat.loading')}</div>
                ) : (
                    <div ref={listRef} className="max-h-64 space-y-3 overflow-y-auto p-3">
                        {messages.length === 0 ? (
                            <div className="py-6 text-center text-sm text-gray-500">
                                {t('orderChat.empty')}
                            </div>
                        ) : (
                            messages.map((message) => {
                                const isAuthor = message.is_author ?? (message.user_id === user?.id);
                                const timestamp = formatDateTime(locale, message.created_at);
                                return (
                                    <div
                                        key={message.id}
                                        className={clsx(
                                            'max-w-[80%] rounded-2xl px-3 py-2 text-sm shadow-sm',
                                            isAuthor
                                                ? 'ml-auto bg-blue-600 text-white'
                                                : 'bg-white text-gray-900',
                                        )}
                                    >
                                        <div className="flex items-center justify-between gap-3 text-[0.7rem] opacity-80">
                                            <span>
                                                {isAuthor ? t('orderChat.you') : message.user?.name ?? t('orderChat.seller')}
                                            </span>
                                            {timestamp && (
                                                <span data-testid={`order-chat-message-timestamp-${message.id}`}>
                                                    {timestamp}
                                                </span>
                                            )}
                                        </div>
                                        <p className="mt-1 whitespace-pre-wrap break-words text-sm leading-relaxed">
                                            {message.body}
                                        </p>
                                    </div>
                                );
                            })
                        )}
                    </div>
                )}
            </div>

            {isAuthenticated ? (
                <form onSubmit={handleSubmit} className="space-y-2">
                    {sendError && <div className="text-sm text-red-600">{sendError}</div>}
                    <textarea
                        value={input}
                        onChange={(event) => setInput(event.target.value)}
                        rows={3}
                        maxLength={2000}
                        className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black disabled:opacity-60"
                        placeholder={t('orderChat.inputPlaceholder')}
                        disabled={sending}
                    />
                    <div className="flex items-center justify-between gap-3 text-xs text-gray-500">
                        <span>{t('orderChat.inputHint.maxLength', { limit: 2000 })}</span>
                        <Button type="submit" disabled={!canSend}>
                            {sending ? t('orderChat.actions.sending') : t('orderChat.actions.send')}
                        </Button>
                    </div>
                </form>
            ) : (
                <div className="rounded border border-dashed border-gray-300 bg-white px-4 py-5 text-sm text-gray-600">
                    {t('orderChat.guestPrompt.prefix')}{' '}
                    <Link to="/login" className="font-medium text-blue-600 hover:underline">
                        {t('orderChat.guestPrompt.login')}
                    </Link>{' '}
                    {t('orderChat.guestPrompt.or')}{' '}
                    <Link to="/register" className="font-medium text-blue-600 hover:underline">
                        {t('orderChat.guestPrompt.register')}
                    </Link>
                    {t('orderChat.guestPrompt.suffix')}
                </div>
            )}
        </div>
    );
}
