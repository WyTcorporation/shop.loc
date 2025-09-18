import React from 'react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { act, render, screen, waitFor } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import LocaleProvider, { useLocale } from '../../i18n/LocaleProvider';
import type { Lang } from '../../i18n/config';
import SellerPage from '../SellerPage';
import type { Product, SellerProductsResponse, Vendor } from '../../api';

type FetchSellerProducts = typeof import('../../api').fetchSellerProducts;

const fetchSellerProductsMock = vi.fn<ReturnType<FetchSellerProducts>, Parameters<FetchSellerProducts>>();
const viewItemListMock = vi.fn();
const seoHeadCalls: Array<{ title: string; description: string }> = [];

vi.mock('../../api', async () => {
    const actual = await vi.importActual<typeof import('../../api')>('../../api');
    return {
        __esModule: true,
        ...actual,
        fetchSellerProducts: (...args: Parameters<typeof actual.fetchSellerProducts>) =>
            fetchSellerProductsMock(...args),
    };
});

vi.mock('../../components/WishlistButton', () => ({
    __esModule: true,
    default: () => <div data-testid="wishlist-button" />,
}));

vi.mock('../../components/SeoHead', () => ({
    __esModule: true,
    default: (props: { title: string; description: string }) => {
        seoHeadCalls.push({ title: props.title, description: props.description });
        return null;
    },
}));

vi.mock('../../ui/ga', () => ({
    GA: {
        view_item_list: (...args: any[]) => viewItemListMock(...args),
    },
}));

describe('SellerPage localization', () => {
    let setLangRef: ((lang: Lang) => void) | null = null;

    function LocaleObserver() {
        const { setLang } = useLocale();
        React.useEffect(() => {
            setLangRef = setLang;
            return () => {
                setLangRef = null;
            };
        }, [setLang]);
        return null;
    }

    beforeEach(() => {
        vi.clearAllMocks();
        fetchSellerProductsMock.mockReset();
        viewItemListMock.mockReset();
        seoHeadCalls.length = 0;
        setLangRef = null;
    });

    it('updates texts and SEO data when locale changes', async () => {
        const vendor: Vendor = {
            id: 10,
            name: 'ACME Studio',
            slug: 'acme',
            contact_email: 'acme@example.com',
            contact_phone: '+380501234567',
            description: 'Handmade miniatures and accessories.',
        };
        const products: Product[] = [
            {
                id: 101,
                name: 'Miniature Statue',
                slug: 'miniature-statue',
                category_id: 5,
                price: 49.99,
                currency: 'USD',
            },
        ];

        fetchSellerProductsMock.mockResolvedValue({
            vendor,
            data: products,
            last_page: 3,
            current_page: 1,
        } as SellerProductsResponse);

        render(
            <LocaleProvider initial="uk">
                <LocaleObserver />
                <MemoryRouter initialEntries={['/seller/acme']}>
                    <Routes>
                        <Route path="/seller/:id" element={<SellerPage />} />
                    </Routes>
                </MemoryRouter>
            </LocaleProvider>,
        );

        expect(fetchSellerProductsMock).toHaveBeenCalledWith('acme', { page: 1 });

        await screen.findByText('Miniature Statue');

        expect(viewItemListMock).toHaveBeenCalledWith(
            products,
            'Продавець ACME Studio',
        );

        await screen.findByText('Товари продавця');
        await screen.findByText('Сторінка 1 з 3');

        expect(document.title).toBe('ACME Studio — Продавець — 3D-Print Shop');

        const lastSeoCallUk = seoHeadCalls.at(-1);
        expect(lastSeoCallUk).toBeDefined();
        expect(lastSeoCallUk?.title).toBe('ACME Studio — Продавець — 3D-Print Shop');
        expect(lastSeoCallUk?.description).toBe(
            'Handmade miniatures and accessories. Email: acme@example.com Телефон: +380501234567',
        );

        await act(async () => {
            setLangRef?.('en');
        });

        await screen.findByText('Seller products');
        await screen.findByText('Page 1 of 3');

        await waitFor(() => {
            expect(viewItemListMock).toHaveBeenCalledWith(products, 'Seller ACME Studio');
        });

        expect(document.title).toBe('ACME Studio — Seller — 3D-Print Shop');

        const lastSeoCallEn = seoHeadCalls.at(-1);
        expect(lastSeoCallEn).toBeDefined();
        expect(lastSeoCallEn?.title).toBe('ACME Studio — Seller — 3D-Print Shop');
        expect(lastSeoCallEn?.description).toBe(
            'Handmade miniatures and accessories. Email: acme@example.com Phone: +380501234567',
        );
    });
});
