import React from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { MemoryRouter } from 'react-router-dom';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import OrderChat from '../components/OrderChat';
import LocaleProvider, { useLocale } from '../i18n/LocaleProvider';

const { authState, useAuthMock, listMessagesMock, sendMessageMock } = vi.hoisted(() => {
    const authState = {
        isAuthenticated: true,
        isReady: true,
        user: { id: 1, name: 'Customer' },
    };

    return {
        authState,
        useAuthMock: vi.fn(() => authState),
        listMessagesMock: vi.fn(),
        sendMessageMock: vi.fn(),
    };
});

vi.mock('../hooks/useAuth', () => ({
    default: useAuthMock,
}));

vi.mock('../api', () => ({
    OrdersApi: {
        listMessages: listMessagesMock,
        sendMessage: sendMessageMock,
    },
    setApiLocale: vi.fn(),
}));

function Testbed(props: React.ComponentProps<typeof OrderChat>) {
    const { setLang } = useLocale();
    return (
        <>
            <button type="button" data-testid="switch-lang" onClick={() => setLang('en')}>
                Switch language
            </button>
            <OrderChat {...props} />
        </>
    );
}

describe('OrderChat', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        Object.assign(authState, {
            isAuthenticated: true,
            isReady: true,
            user: { id: 1, name: 'Customer' },
        });
        listMessagesMock.mockResolvedValue([
            {
                id: 1,
                order_id: 42,
                user_id: 1,
                body: 'Привіт',
                created_at: '2024-01-01T00:00:00Z',
                user: { id: 1, name: 'Customer' },
                is_author: true,
            },
            {
                id: 2,
                order_id: 42,
                user_id: 2,
                body: 'Доброго дня',
                created_at: '2024-01-01T00:01:00Z',
                user: null,
                is_author: false,
            },
        ]);
        sendMessageMock.mockResolvedValue({
            id: 3,
            order_id: 42,
            user_id: 1,
            body: 'Test',
            created_at: '2024-01-01T00:02:00Z',
            user: { id: 1, name: 'Customer' },
            is_author: true,
        });
    });

    it('updates visible texts when the locale changes', async () => {
        const user = userEvent.setup();

        render(
            <MemoryRouter>
                <LocaleProvider initial="uk">
                    <Testbed orderId={42} orderNumber="A-001" />
                </LocaleProvider>
            </MemoryRouter>,
        );

        expect(await screen.findByRole('heading', { name: 'Чат з продавцем' })).toBeInTheDocument();
        expect(screen.getByText('Замовлення A-001')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Оновити' })).toBeInTheDocument();
        expect(screen.getByPlaceholderText('Ваше повідомлення продавцю…')).toBeInTheDocument();
        expect(screen.getByText('До 2000 символів')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Надіслати' })).toBeInTheDocument();
        expect(await screen.findByText('Ви')).toBeInTheDocument();
        expect(screen.getByText('Продавець')).toBeInTheDocument();

        await user.click(screen.getByTestId('switch-lang'));

        expect(await screen.findByRole('heading', { name: 'Chat with the seller' })).toBeInTheDocument();
        expect(screen.getByText('Order A-001')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Refresh' })).toBeInTheDocument();
        expect(await screen.findByPlaceholderText('Your message to the seller…')).toBeInTheDocument();
        expect(screen.getByText('Up to 2000 characters')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Send' })).toBeInTheDocument();
        expect(await screen.findByText('You')).toBeInTheDocument();
        expect(screen.getByText('Seller')).toBeInTheDocument();
    });

    it('formats timestamps according to locale', async () => {
        const user = userEvent.setup();

        render(
            <MemoryRouter>
                <LocaleProvider initial="uk">
                    <Testbed orderId={42} orderNumber="A-001" />
                </LocaleProvider>
            </MemoryRouter>,
        );

        const timestamp = await screen.findByTestId('order-chat-message-timestamp-1');
        expect(timestamp).toHaveTextContent(/\d{2}\.\d{2}/);
        const initialValue = timestamp.textContent;
        expect(initialValue).not.toBeNull();
        const initialText = initialValue ?? '';

        await user.click(screen.getByTestId('switch-lang'));

        await screen.findByRole('heading', { name: 'Chat with the seller' });
        const updatedTimestamp = await screen.findByTestId('order-chat-message-timestamp-1');
        expect(updatedTimestamp.textContent).not.toBe(initialText);
        expect(updatedTimestamp).toHaveTextContent(/\//);
    });
});
