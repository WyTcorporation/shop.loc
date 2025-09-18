import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Mock } from 'vitest';

import ProfilePointsPage from '../ProfilePoints';
import { ProfileApi } from '../../api';

vi.mock('../../components/ProfileNavigation', () => ({
    __esModule: true,
    default: () => <div data-testid="profile-navigation" />,
}));

vi.mock('../../hooks/useAuth', () => ({
    __esModule: true,
    default: vi.fn(),
}));

vi.mock('../../i18n/LocaleProvider', () => ({
    useLocale: vi.fn(),
}));

const useAuth = (await import('../../hooks/useAuth')).default as Mock;
const { useLocale } = await import('../../i18n/LocaleProvider');
const useLocaleMock = useLocale as Mock;

describe('ProfilePointsPage localization', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        useAuth.mockReset();
        useLocaleMock.mockReset();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('shows localized date and transaction type', async () => {
        const locale = 'pt-PT';
        const translations: Record<string, string> = {
            'profile.points.type.default': 'Movimento',
            'profile.points.type.earn': 'Acumulação',
            'profile.points.type.redeem': 'Utilização',
        };

        useAuth.mockReturnValue({
            isAuthenticated: true,
            isReady: true,
        });

        useLocaleMock.mockReturnValue({
            t: (key: string) => translations[key] ?? key,
            locale,
            lang: locale.split('-')[0],
            messages: {},
            setLang: vi.fn(),
        });

        const payload = {
            balance: 120,
            total_earned: 200,
            total_spent: 80,
            transactions: [
                {
                    id: 1,
                    type: 'earn',
                    description: 'Order bonus',
                    points: 15,
                    created_at: '2024-03-25T00:00:00Z',
                },
            ],
        };

        const fetchPointsSpy = vi.spyOn(ProfileApi, 'fetchPoints').mockResolvedValueOnce(payload as any);

        render(
            <MemoryRouter>
                <ProfilePointsPage />
            </MemoryRouter>,
        );

        expect(fetchPointsSpy).toHaveBeenCalledTimes(1);

        const expectedDate = new Intl.DateTimeFormat(locale, {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        }).format(new Date(payload.transactions[0]!.created_at));

        expect(await screen.findByText(expectedDate)).toBeInTheDocument();
        expect(screen.getByText(translations['profile.points.type.earn'])).toBeInTheDocument();
    });
});
