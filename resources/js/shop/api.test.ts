import { beforeEach, describe, expect, it, vi } from 'vitest';

const mocks = vi.hoisted(() => {
    const getMock = vi.fn();
    const postMock = vi.fn();
    const patchMock = vi.fn();
    const deleteMock = vi.fn();

    return { getMock, postMock, patchMock, deleteMock };
});

vi.mock('axios', () => {
    const createMock = vi.fn(() => ({
        get: mocks.getMock,
        post: mocks.postMock,
        patch: mocks.patchMock,
        delete: mocks.deleteMock,
    }));

    return {
        __esModule: true,
        default: {
            create: createMock,
        },
    };
});

import { CartApi, resetCartCache } from './api';

describe('CartApi', () => {
    const { getMock, postMock, patchMock, deleteMock } = mocks;

    beforeEach(() => {
        getMock.mockReset();
        postMock.mockReset();
        patchMock.mockReset();
        deleteMock.mockReset();
        resetCartCache();
    });

    it('creates a new cart when the current cart is already ordered before adding items', async () => {
        getMock
            .mockImplementationOnce(async (url: string) => {
                expect(url).toBe('/cart');
                return {
                    data: { id: 'existing-cart', status: 'active', items: [], total: 0 },
                };
            })
            .mockImplementationOnce(async (url: string) => {
                expect(url).toBe('/cart/existing-cart');
                return {
                    data: { id: 'existing-cart', status: 'ordered', items: [], total: 0 },
                };
            })
            .mockImplementationOnce(async (url: string) => {
                expect(url).toBe('/cart');
                return {
                    data: { id: 'new-cart', status: 'active', items: [], total: 0 },
                };
            });

        postMock.mockImplementationOnce(async (url: string, body: unknown) => {
            expect(url).toBe('/cart/new-cart/items');
            expect(body).toEqual({ product_id: 42, qty: 1 });
            return {
                data: { id: 'new-cart', status: 'active', items: [], total: 0 },
            };
        });

        const initialCart = await CartApi.get();
        expect(initialCart.id).toBe('existing-cart');

        const updatedCart = await CartApi.add(42, 1);
        expect(updatedCart.id).toBe('new-cart');

        expect(getMock).toHaveBeenCalledTimes(3);
        expect(postMock).toHaveBeenCalledTimes(1);
    });
});
