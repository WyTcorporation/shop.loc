import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import ProfilePage from '../pages/Profile';
import { AuthApi, TwoFactorApi } from '../api';

const mockUseAuth = vi.fn();

vi.mock('../hooks/useAuth', () => ({
    default: () => mockUseAuth(),
}));

describe('ProfilePage email verification notice', () => {
    beforeEach(() => {
        mockUseAuth.mockReturnValue({
            user: {
                id: 1,
                name: 'Test User',
                email: 'test@example.com',
                email_verified_at: null,
            },
            isAuthenticated: true,
            isReady: true,
            isLoading: false,
            logout: vi.fn(),
            refresh: vi.fn().mockResolvedValue(null),
        });
        vi.spyOn(TwoFactorApi, 'status').mockResolvedValue({ enabled: false, pending: false });
    });

    afterEach(() => {
        vi.restoreAllMocks();
        mockUseAuth.mockReset();
    });

    it('sends verification email again when button is clicked', async () => {
        const resendSpy = vi.spyOn(AuthApi, 'resendVerification').mockResolvedValue({
            message: 'ok',
        });

        render(
            <MemoryRouter initialEntries={['/profile']}>
                <ProfilePage />
            </MemoryRouter>,
        );

        const button = await screen.findByRole('button', { name: 'Надіслати лист повторно' });

        await userEvent.click(button);

        await waitFor(() => {
            expect(resendSpy).toHaveBeenCalledTimes(1);
        });
    });
});
