import { render, screen } from '@testing-library/react';
import React from 'react';
import { MemoryRouter } from 'react-router-dom';
import { vi } from 'vitest';
import { AppErrorBoundary } from '../ErrorBoundary';
import { createTranslator, getMessages } from '../../i18n/messages';
import { DEFAULT_LANG } from '../../i18n/config';

const t = createTranslator(getMessages(DEFAULT_LANG));

function Thrower() {
    throw new Error('Test crash');
}

describe('AppErrorBoundary', () => {
    it('renders translated fallback UI', () => {
        const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

        try {
            render(
                <MemoryRouter>
                    <AppErrorBoundary>
                        <Thrower />
                    </AppErrorBoundary>
                </MemoryRouter>,
            );

            expect(screen.getByRole('heading', { name: t('common.errorBoundary.title') })).toBeInTheDocument();
            expect(screen.getByText('Test crash')).toBeInTheDocument();
            expect(screen.getByRole('button', { name: t('common.errorBoundary.reload') })).toBeInTheDocument();
            expect(screen.getByRole('link', { name: t('common.errorBoundary.home') })).toBeInTheDocument();
        } finally {
            consoleErrorSpy.mockRestore();
        }
    });
});
