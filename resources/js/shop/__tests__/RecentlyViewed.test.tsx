import React from 'react';
import { describe, beforeEach, afterEach, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';

import RecentlyViewed from '../components/RecentlyViewed';
import LocaleProvider, { useLocale } from '../i18n/LocaleProvider';

type RecentlyViewedProps = React.ComponentProps<typeof RecentlyViewed>;

const SWITCH_BUTTON_ID = 'switch-lang';

function renderRecentlyViewed(props: RecentlyViewedProps = {}) {
    function Testbed() {
        const { setLang } = useLocale();
        return (
            <MemoryRouter>
                <button type="button" data-testid={SWITCH_BUTTON_ID} onClick={() => setLang('uk')}>
                    Switch language
                </button>
                <RecentlyViewed {...props} />
            </MemoryRouter>
        );
    }

    return render(
        <LocaleProvider initial="en">
            <Testbed />
        </LocaleProvider>,
    );
}

describe('RecentlyViewed', () => {
    beforeEach(() => {
        localStorage.clear();
    });

    afterEach(() => {
        localStorage.clear();
    });

    it('renders localized texts for the empty state when language changes', async () => {
        const user = userEvent.setup();

        renderRecentlyViewed();

        expect(screen.getByRole('heading', { level: 2 })).toHaveTextContent('Recently viewed');
        expect(await screen.findByTestId('recently-empty')).toHaveTextContent('You haven’t viewed any products yet.');

        await user.click(screen.getByTestId(SWITCH_BUTTON_ID));

        expect(screen.getByRole('heading', { level: 2 })).toHaveTextContent('Ви нещодавно переглядали');
        expect(await screen.findByText('Ще не переглядали жодного товару.')).toBeInTheDocument();
    });

    it('renders localized placeholder for items without images when language changes', async () => {
        localStorage.setItem(
            'recently_viewed',
            JSON.stringify([
                { id: 1, slug: 'demo', name: 'Demo product', price: 10, preview_url: null },
            ]),
        );

        const user = userEvent.setup();

        renderRecentlyViewed();

        expect(screen.getByRole('heading', { level: 2 })).toHaveTextContent('Recently viewed');
        expect(await screen.findByText('No photo')).toBeInTheDocument();

        await user.click(screen.getByTestId(SWITCH_BUTTON_ID));

        expect(screen.getByRole('heading', { level: 2 })).toHaveTextContent('Ви нещодавно переглядали');
        expect(await screen.findByText('без фото')).toBeInTheDocument();
    });
});
