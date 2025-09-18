import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import NotFoundPage from '../NotFound';
import { createTranslator, getMessages } from '../../i18n/messages';
import { DEFAULT_LANG } from '../../i18n/config';

const t = createTranslator(getMessages(DEFAULT_LANG));

describe('NotFoundPage', () => {
    it('renders translated texts', () => {
        render(
            <MemoryRouter>
                <NotFoundPage />
            </MemoryRouter>,
        );

        expect(screen.getByRole('heading', { name: t('common.notFound.title') })).toBeInTheDocument();
        expect(screen.getByText(t('common.notFound.description'))).toBeInTheDocument();
        expect(screen.getByRole('link', { name: t('common.notFound.action') })).toBeInTheDocument();
    });
});
