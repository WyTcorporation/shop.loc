import React from 'react';
import { describe, expect, it, beforeEach, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import CookieConsent from '../components/CookieConsent';
import LocaleProvider, { useLocale } from '../i18n/LocaleProvider';

vi.mock('../ui/analytics', () => ({
    getAnalyticsId: vi.fn(() => 'G-123'),
    getConsent: vi.fn(() => null),
    setConsent: vi.fn(),
}));

function Testbed() {
    const { setLang } = useLocale();
    return (
        <>
            <button type="button" data-testid="switch-lang" onClick={() => setLang('uk')}>
                Switch language
            </button>
            <CookieConsent />
        </>
    );
}

describe('CookieConsent', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders localized texts for the active language', async () => {
        const user = userEvent.setup();

        render(
            <LocaleProvider initial="en">
                <Testbed />
            </LocaleProvider>,
        );

        expect(await screen.findByRole('dialog', { name: 'Cookie preferences' })).toBeInTheDocument();
        expect(
            screen.getByText(
                'We use cookies for analytics (GA4). Click “Allow” to enable them. You can change your choice anytime.',
            ),
        ).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Decline' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Allow' })).toBeInTheDocument();
        expect(
            screen.getByText('Required cookies do not track you. Analytics is enabled only with consent.'),
        ).toBeInTheDocument();

        await user.click(screen.getByTestId('switch-lang'));

        expect(await screen.findByRole('dialog', { name: 'Налаштування cookies' })).toBeInTheDocument();
        expect(
            screen.getByText(
                'Ми використовуємо cookies для аналітики (GA4). Натисніть «Дозволити», щоб увімкнути. Ви можете змінити вибір будь-коли.',
            ),
        ).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Відхилити' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Дозволити' })).toBeInTheDocument();
        expect(
            screen.getByText('Обов’язкові cookies не відслідковують вас. Аналітика вмикається лише за згодою.'),
        ).toBeInTheDocument();
    });
});
