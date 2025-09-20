import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import ForgotPasswordPage from '../pages/ForgotPassword';
import { createTranslator, getMessages } from '../i18n/messages';
import { DEFAULT_LANG } from '../i18n/config';

const t = createTranslator(getMessages(DEFAULT_LANG));

describe('ForgotPasswordPage', () => {
    it('renders the forgot password form', () => {
        render(
            <MemoryRouter>
                <ForgotPasswordPage />
            </MemoryRouter>,
        );

        expect(screen.getByRole('heading', { name: t('auth.reset.request.title') })).toBeInTheDocument();
        expect(screen.getByLabelText(t('auth.reset.fields.emailLabel'))).toBeInTheDocument();
        expect(screen.getByRole('button', { name: t('auth.reset.request.submit') })).toBeInTheDocument();
        expect(screen.getByText(t('auth.reset.request.remember'), { exact: false })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: t('auth.reset.shared.backToLogin') })).toBeInTheDocument();
    });
});
