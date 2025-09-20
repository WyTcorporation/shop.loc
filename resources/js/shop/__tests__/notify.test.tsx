import React from 'react';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import LocaleProvider, { useLocale } from '../i18n/LocaleProvider';
import { NotifyProvider, useNotify } from '../ui/notify';

function ToastTestbed() {
    const notify = useNotify();
    const { setLang } = useLocale();

    React.useEffect(() => {
        notify.info('Test notification');
    }, [notify]);

    return (
        <button type="button" data-testid="switch-toast-lang" onClick={() => setLang('uk')}>
            Switch language
        </button>
    );
}

describe('NotifyProvider localization', () => {
    it('updates toast close aria-label when locale changes', async () => {
        const user = userEvent.setup();

        render(
            <LocaleProvider initial="en">
                <NotifyProvider autoCloseMs={null}>
                    <ToastTestbed />
                </NotifyProvider>
            </LocaleProvider>,
        );

        expect(await screen.findByRole('button', { name: 'Close notification' })).toBeInTheDocument();

        await user.click(screen.getByTestId('switch-toast-lang'));

        expect(await screen.findByRole('button', { name: 'Закрити сповіщення' })).toBeInTheDocument();
    });
});
