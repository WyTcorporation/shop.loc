import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import ForgotPasswordPage from '../ForgotPassword';

describe('ForgotPasswordPage', () => {
    it('renders the forgot password form', () => {
        render(
            <MemoryRouter>
                <ForgotPasswordPage />
            </MemoryRouter>,
        );

        expect(screen.getByRole('heading', { name: /відновлення пароля/i })).toBeInTheDocument();
        expect(screen.getByLabelText(/email/i)).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /надіслати посилання/i })).toBeInTheDocument();
    });
});
