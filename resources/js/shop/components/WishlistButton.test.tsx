import React from 'react';
import { describe, expect, it, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import WishlistButton from './WishlistButton';
import LocaleProvider, { useLocale } from '../i18n/LocaleProvider';

type WishlistButtonProps = React.ComponentProps<typeof WishlistButton>;

type WishlistMock = {
    has: (id: number) => boolean;
    toggle: ReturnType<typeof vi.fn>;
};

const SWITCH_ID = 'wishlist-switch-lang';

function createWishlistMock({ has }: { has: (id: number) => boolean }): WishlistMock {
    return {
        has,
        toggle: vi.fn(),
    };
}

const mockUseWishlist = vi.fn<[], WishlistMock>();

vi.mock('../hooks/useWishlist', () => ({
    default: () => mockUseWishlist(),
}));

function renderWishlistButton(props: WishlistButtonProps) {
    function Testbed() {
        const { setLang } = useLocale();
        return (
            <>
                <button type="button" data-testid={SWITCH_ID} onClick={() => setLang('uk')}>
                    Switch language
                </button>
                <WishlistButton {...props} />
            </>
        );
    }

    return render(
        <LocaleProvider initial="en">
            <Testbed />
        </LocaleProvider>,
    );
}

describe('WishlistButton', () => {
    const product = { id: 1, name: 'Demo', price: 10 };

    beforeEach(() => {
        mockUseWishlist.mockReset();
    });

    it('switches translations for add state when language changes', async () => {
        mockUseWishlist.mockReturnValue(createWishlistMock({ has: () => false }));
        const user = userEvent.setup();

        renderWishlistButton({ product });

        const button = screen.getByRole('button', { name: 'Add to wishlist' });
        expect(button).toHaveTextContent('Add to wishlist');
        expect(button).toHaveAttribute('title', 'Add to wishlist');

        await user.click(screen.getByTestId(SWITCH_ID));

        const translated = await screen.findByRole('button', { name: 'Додати в обране' });
        await waitFor(() => {
            expect(translated).toHaveTextContent('В обране');
            expect(translated).toHaveAttribute('title', 'Додати в обране');
        });
    });

    it('uses remove translations when product is already in wishlist', () => {
        mockUseWishlist.mockReturnValue(createWishlistMock({ has: () => true }));

        renderWishlistButton({ product });

        const button = screen.getByRole('button', { name: 'Remove from wishlist' });
        expect(button).toHaveTextContent('In wishlist');
        expect(button).toHaveAttribute('title', 'Remove from wishlist');
    });
});
