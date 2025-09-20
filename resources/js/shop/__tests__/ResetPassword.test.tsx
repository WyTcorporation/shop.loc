import { render, screen } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import ResetPasswordPage from '../pages/ResetPassword';
import { createTranslator, getMessages } from '../i18n/messages';
import { DEFAULT_LANG } from '../i18n/config';

const t = createTranslator(getMessages(DEFAULT_LANG));

describe('ResetPasswordPage', () => {
    it('renders the reset password form', () => {
        render(
            <MemoryRouter initialEntries={["/reset-password/test-token"]}>
                <Routes>
                    <Route path="/reset-password/:token" element={<ResetPasswordPage />} />
                </Routes>
            </MemoryRouter>,
        );

        expect(screen.getByRole('heading', { name: t('auth.reset.update.title') })).toBeInTheDocument();
        expect(screen.getByLabelText(t('auth.reset.fields.emailLabel'))).toBeInTheDocument();
        expect(screen.getByLabelText(t('auth.reset.fields.passwordLabel'))).toBeInTheDocument();
        expect(screen.getByRole('button', { name: t('auth.reset.update.submit') })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: t('auth.reset.update.backToLoginLink') })).toBeInTheDocument();
    });
});
