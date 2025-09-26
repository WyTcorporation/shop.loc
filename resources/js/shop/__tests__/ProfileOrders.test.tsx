import { render, screen, within } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Mock } from 'vitest';

import ProfileOrdersPage from '../pages/ProfileOrders';
import { OrdersApi } from '../api';

vi.mock('../components/ProfileNavigation', () => ({
    __esModule: true,
    default: () => <div data-testid="profile-navigation" />,
}));

vi.mock('../components/SeoHead', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../hooks/useAuth', () => ({
    __esModule: true,
    default: vi.fn(),
}));

vi.mock('../i18n/LocaleProvider', () => ({
    useLocale: vi.fn(),
}));

const useAuth = (await import('../hooks/useAuth')).default as Mock;
const { useLocale } = await import('../i18n/LocaleProvider');
const useLocaleMock = useLocale as Mock;

describe('ProfileOrdersPage localization', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        useAuth.mockReset();
        useLocaleMock.mockReset();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('formats totals and dates using active locale and currency', async () => {
        const locale = 'ru-RU';
        const order = {
            id: 1,
            number: 'ORD-100',
            total: 456.78,
            currency: 'USD',
            status: 'processing',
            created_at: '2024-02-15T12:30:00Z',
            unread_responses_count: 2,
        } as const;

        useAuth.mockReturnValue({
            isAuthenticated: true,
            isReady: true,
        });

        useLocaleMock.mockReturnValue({
            t: (key: string) => key,
            locale,
            lang: locale.split('-')[0],
            messages: {},
            setLang: vi.fn(),
        });

        const listMineSpy = vi.spyOn(OrdersApi, 'listMine').mockResolvedValueOnce([order] as any);

        render(
            <MemoryRouter>
                <ProfileOrdersPage />
            </MemoryRouter>,
        );

        expect(listMineSpy).toHaveBeenCalledTimes(1);

        const numberCell = await screen.findByText(order.number);
        const orderRow = numberCell.closest('tr');
        expect(orderRow).not.toBeNull();

        const expectedTotal = new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: order.currency!,
            maximumFractionDigits: 2,
        }).format(order.total);
        const expectedDate = new Intl.DateTimeFormat(locale, {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(order.created_at));

        const amountMatcher = (content: string) => content.replace(/\u00a0/g, ' ') === expectedTotal.replace(/\u00a0/g, ' ');
        expect(within(orderRow as HTMLTableRowElement).getByText(amountMatcher)).toBeInTheDocument();
        expect(within(orderRow as HTMLTableRowElement).getByText(expectedDate)).toBeInTheDocument();
        expect(within(orderRow as HTMLTableRowElement).getByTestId(`order-unread-${order.number}`)).toHaveTextContent(
            'profile.orders.table.unreadBadge',
        );
    });
});
