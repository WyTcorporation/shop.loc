import { render, screen } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import ResetPasswordPage from '../ResetPassword';

describe('ResetPasswordPage', () => {
    it('renders the reset password form', () => {
        render(
            <MemoryRouter initialEntries={["/reset-password/test-token"]}>
                <Routes>
                    <Route path="/reset-password/:token" element={<ResetPasswordPage />} />
                </Routes>
            </MemoryRouter>,
        );

        expect(screen.getByRole('heading', { name: /скидання пароля/i })).toBeInTheDocument();
        expect(screen.getByLabelText(/новий пароль/i)).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /змінити пароль/i })).toBeInTheDocument();
    });
});
