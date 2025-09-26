import React from 'react';
import {render, screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {MemoryRouter} from 'react-router-dom';
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';

import WishlistPage from '../pages/Wishlist';
import {WishlistApi} from '../api';

const mockUseWishlist = vi.fn();
const { useAuthMock } = vi.hoisted(() => ({
    useAuthMock: vi.fn(),
}));

vi.mock('../hooks/useWishlist', () => ({
    default: () => mockUseWishlist(),
}));

vi.mock('../hooks/useAuth', () => ({
    default: useAuthMock,
}));

describe('WishlistPage remove button', () => {
    let removeSpy: ReturnType<typeof vi.fn>;

    beforeEach(() => {
        removeSpy = vi.fn();
        useAuthMock.mockReturnValue({
            isAuthenticated: true,
        });

        mockUseWishlist.mockImplementation(() => {
            const [items, setItems] = React.useState([
                {
                    id: 1,
                    name: 'Тестовий товар',
                    slug: 'test-product',
                    price: 129900,
                    preview_url: null,
                },
            ]);

            const remove = (id: number, options?: {sync?: boolean}) => {
                removeSpy(id, options);
                setItems(prev => prev.filter(item => item.id !== id));
            };

            return {
                items,
                remove,
                clear: vi.fn(),
                has: vi.fn(),
                add: vi.fn(),
                toggle: vi.fn(),
                isLoading: false,
                error: null,
            };
        });
    });

    afterEach(() => {
        vi.restoreAllMocks();
        mockUseWishlist.mockReset();
        useAuthMock.mockReset();
    });

    it('removes item after clicking the remove button', async () => {
        const user = userEvent.setup();
        const apiRemoveSpy = vi.spyOn(WishlistApi, 'remove').mockResolvedValue(undefined as never);

        render(
            <MemoryRouter initialEntries={['/wishlist']}>
                <WishlistPage />
            </MemoryRouter>,
        );

        const removeButton = await screen.findByRole('button', {
            name: 'Прибрати «Тестовий товар» зі списку бажаного',
        });

        await user.click(removeButton);

        await waitFor(() => {
            expect(apiRemoveSpy).toHaveBeenCalledWith(1);
        });

        await waitFor(() => {
            expect(screen.queryByText('Тестовий товар')).not.toBeInTheDocument();
        });

        expect(removeSpy).toHaveBeenCalledWith(1, {sync: false});
    });

    it('does not call API when guest removes item', async () => {
        const user = userEvent.setup();
        const apiRemoveSpy = vi.spyOn(WishlistApi, 'remove').mockResolvedValue(undefined as never);
        useAuthMock.mockReturnValue({
            isAuthenticated: false,
        });

        render(
            <MemoryRouter initialEntries={['/wishlist']}>
                <WishlistPage />
            </MemoryRouter>,
        );

        const removeButton = await screen.findByRole('button', {
            name: 'Прибрати «Тестовий товар» зі списку бажаного',
        });

        await user.click(removeButton);

        await waitFor(() => {
            expect(removeSpy).toHaveBeenCalledWith(1, undefined);
        });

        await waitFor(() => {
            expect(screen.queryByText('Тестовий товар')).not.toBeInTheDocument();
        });

        expect(apiRemoveSpy).not.toHaveBeenCalled();
    });
});
