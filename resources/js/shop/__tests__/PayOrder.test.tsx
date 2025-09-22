import React from 'react';
import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { render, waitFor, cleanup } from '@testing-library/react';

import LocaleProvider from '../i18n/LocaleProvider';

const notifySuccessMock = vi.fn();
const notifyErrorMock = vi.fn();
const notifyInfoMock = vi.fn();
const clearMock = vi.fn();
const clearAllMock = vi.fn();

vi.mock('../ui/notify', () => ({
    useNotify: () => ({
        success: notifySuccessMock,
        error: notifyErrorMock,
        info: notifyInfoMock,
        clear: clearMock,
        clearAll: clearAllMock,
    }),
}));

vi.mock('@stripe/react-stripe-js', () => ({
    Elements: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
    PaymentElement: () => <div data-testid="payment-element" />,
    useStripe: () => null,
    useElements: () => null,
}));

vi.mock('@stripe/stripe-js', () => ({
    loadStripe: vi.fn(),
}));

const { default: PayOrder } = await import('../components/PayOrder');

describe('PayOrder', () => {
    let fetchMock: ReturnType<typeof vi.fn>;

    beforeEach(() => {
        vi.clearAllMocks();
        fetchMock = vi.fn();
        vi.stubGlobal('fetch', fetchMock);
    });

    afterEach(() => {
        cleanup();
        vi.unstubAllGlobals();
    });

    it('notifies about failed payment intent request', async () => {
        fetchMock.mockResolvedValue({
            ok: false,
            json: vi.fn(),
            status: 500,
            statusText: 'Internal Server Error',
        });

        const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

        render(
            <LocaleProvider initial="en">
                <PayOrder number="ORDER-1" />
            </LocaleProvider>
        );

        await waitFor(() => {
            expect(notifyErrorMock).toHaveBeenCalledWith({ title: 'Payment failed' });
        });

        expect(fetchMock).toHaveBeenCalledWith(
            '/api/payments/intent',
            expect.objectContaining({
                method: 'POST',
            })
        );

        consoleErrorSpy.mockRestore();
    });
});
