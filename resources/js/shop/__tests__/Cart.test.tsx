import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { describe, expect, it, vi, beforeEach } from 'vitest';
import type { Mock } from 'vitest';

import CartPage from '../pages/Cart';

vi.mock('../../useCart', () => ({
    __esModule: true,
    default: vi.fn(),
}));

vi.mock('../../components/SeoHead', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../ui/ga', () => ({
    GA: {
        view_cart: vi.fn(),
    },
}));

vi.mock('../../i18n/LocaleProvider', () => ({
    useLocale: vi.fn(),
}));

const useCart = (await import('../useCart')).default as Mock;
const { useLocale } = await import('../i18n/LocaleProvider');
const useLocaleMock = useLocale as Mock;

describe('CartPage currency formatting', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        useCart.mockReset();
        useLocaleMock.mockReset();
    });

    const scenarios = [
        { locale: 'en-US', currency: 'USD', total: 1234.56 },
        { locale: 'uk-UA', currency: 'EUR', total: 789.1 },
    ] as const;

    scenarios.forEach(({ locale, currency, total }) => {
        it(`renders totals with ${currency} for ${locale}`, async () => {
            useCart.mockReturnValue({
                cart: {
                    id: 'cart-1',
                    status: 'active',
                    currency,
                    items: [
                        {
                            id: 1,
                            product_id: 10,
                            name: 'Test item',
                            price: total,
                            qty: 1,
                        },
                    ],
                },
                total,
                update: vi.fn(),
                remove: vi.fn(),
                add: vi.fn(),
                clear: vi.fn(),
                reload: vi.fn(),
            });

            useLocaleMock.mockReturnValue({
                t: (key: string) => key,
                locale,
                lang: locale.split('-')[0],
                messages: {},
                setLang: vi.fn(),
            });

            render(
                <MemoryRouter>
                    <CartPage />
                </MemoryRouter>,
            );

            const expected = new Intl.NumberFormat(locale, {
                style: 'currency',
                currency,
                maximumFractionDigits: 2,
            }).format(total);

            const normalizedExpected = expected.replace(/\u00a0/g, ' ');
            const amountElements = await screen.findAllByText((content) => {
                return content.replace(/\u00a0/g, ' ') === normalizedExpected;
            });
            expect(amountElements.length).toBeGreaterThanOrEqual(3);
        });
    });
});
