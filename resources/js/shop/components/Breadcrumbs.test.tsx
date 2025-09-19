import React from 'react';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';

import Breadcrumbs from './Breadcrumbs';
import ImageLightbox from './ImageLightbox';
import LocaleProvider, { useLocale } from '../i18n/LocaleProvider';

describe('localized accessibility labels', () => {
    function BreadcrumbsTestbed() {
        const { setLang } = useLocale();
        return (
            <>
                <button type="button" data-testid="switch-lang" onClick={() => setLang('uk')}>
                    Switch language
                </button>
                <Breadcrumbs
                    items={[
                        { label: 'Home', href: '/' },
                        { label: 'Category', href: '/category' },
                        { label: 'Product' },
                    ]}
                />
            </>
        );
    }

    it('updates breadcrumb aria-label when locale changes', async () => {
        const user = userEvent.setup();

        render(
            <MemoryRouter>
                <LocaleProvider initial="en">
                    <BreadcrumbsTestbed />
                </LocaleProvider>
            </MemoryRouter>,
        );

        expect(screen.getByRole('navigation', { name: 'Breadcrumb navigation' })).toBeInTheDocument();

        await user.click(screen.getByTestId('switch-lang'));

        expect(
            await screen.findByRole('navigation', { name: 'Навігація «хлібні крихти»' }),
        ).toBeInTheDocument();
    });

    function LightboxTestbed() {
        const { setLang } = useLocale();
        const noop = () => {};

        return (
            <>
                <button type="button" data-testid="switch-lightbox-lang" onClick={() => setLang('uk')}>
                    Switch language
                </button>
                <ImageLightbox
                    images={[{ url: 'https://example.com/image.jpg', alt: 'Example' }]}
                    openIndex={0}
                    onClose={noop}
                    onPrev={noop}
                    onNext={noop}
                />
            </>
        );
    }

    it('updates lightbox controls when locale changes', async () => {
        const user = userEvent.setup();

        render(
            <LocaleProvider initial="en">
                <LightboxTestbed />
            </LocaleProvider>,
        );

        expect(screen.getByRole('button', { name: 'Close' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Previous image' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Next image' })).toBeInTheDocument();

        await user.click(screen.getByTestId('switch-lightbox-lang'));

        expect(await screen.findByRole('button', { name: 'Закрити' })).toBeInTheDocument();
        expect(await screen.findByRole('button', { name: 'Попереднє зображення' })).toBeInTheDocument();
        expect(await screen.findByRole('button', { name: 'Наступне зображення' })).toBeInTheDocument();
    });
});
