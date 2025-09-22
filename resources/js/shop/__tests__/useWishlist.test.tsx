import {render, screen, waitFor} from '@testing-library/react';
import React from 'react';
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import LocaleProvider from '../i18n/LocaleProvider';
import {WishlistApi} from '../api';
import useWishlist, {WishlistProvider} from '../hooks/useWishlist';

type AuthMock = {
    user: { id: number } | null;
    token: string | null;
    isAuthenticated: boolean;
    isReady: boolean;
    isLoading: boolean;
};

const authState: AuthMock = {
    user: {id: 1},
    token: 'token',
    isAuthenticated: true,
    isReady: true,
    isLoading: false,
};

vi.mock('./useAuth', () => ({
    __esModule: true,
    default: () => authState,
}));

describe('useWishlist localization', () => {
    function ErrorProbe() {
        const {error} = useWishlist();
        return <div data-testid="error">{error}</div>;
    }

    function renderWithProviders() {
        return render(
            <LocaleProvider initial="en">
                <WishlistProvider>
                    <ErrorProbe />
                </WishlistProvider>
            </LocaleProvider>
        );
    }

    beforeEach(() => {
        Object.assign(authState, {
            user: {id: 1},
            token: 'token',
            isAuthenticated: true,
            isReady: true,
            isLoading: false,
        });
        localStorage.clear();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('returns localized auth error on 401 responses', async () => {
        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
        vi.spyOn(WishlistApi, 'list').mockRejectedValue({
            response: {status: 401, data: {}},
        });

        renderWithProviders();

        await waitFor(() => {
            expect(screen.getByTestId('error')).toHaveTextContent('Sign in to sync your wishlist.');
        });

        consoleSpy.mockRestore();
    });

    it('returns localized sync error on non-auth failures', async () => {
        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
        vi.spyOn(WishlistApi, 'list').mockRejectedValue({
            response: {status: 500, data: {}},
        });

        renderWithProviders();

        await waitFor(() => {
            expect(screen.getByTestId('error')).toHaveTextContent('Could not sync your wishlist.');
        });

        consoleSpy.mockRestore();
    });

    it('returns localized partial sync error when some items fail to sync', async () => {
        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
        localStorage.setItem(
            'wishlist_v1',
            JSON.stringify([
                {id: 1, name: 'Local item', price: 100},
            ])
        );

        vi.spyOn(WishlistApi, 'list').mockResolvedValue([
            {id: 2, name: 'Remote item', price: 200},
        ]);
        vi.spyOn(WishlistApi, 'add').mockRejectedValue(new Error('Failed to sync item'));

        renderWithProviders();

        await waitFor(() => {
            expect(screen.getByTestId('error')).toHaveTextContent(
                'Some items could not be synced with the wishlist.'
            );
        });

        consoleSpy.mockRestore();
    });
});
