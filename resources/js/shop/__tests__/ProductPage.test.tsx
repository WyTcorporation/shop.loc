import React from 'react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import LocaleProvider from '../i18n/LocaleProvider';
import type { Product } from '../api';
import ProductPage from '../pages/Product';

const productsShowMock = vi.fn();
const productsRelatedMock = vi.fn();
const reviewsListMock = vi.fn();

vi.mock('../../api', () => ({
    ProductsApi: {
        show: (...args: unknown[]) => productsShowMock(...args),
        related: (...args: unknown[]) => productsRelatedMock(...args),
    },
    ReviewsApi: {
        list: (...args: unknown[]) => reviewsListMock(...args),
    },
}));

vi.mock('../../useCart', () => ({
    __esModule: true,
    default: () => ({ add: vi.fn() }),
}));

vi.mock('../../ui/notify', () => ({
    useNotify: () => ({
        error: vi.fn(),
    }),
}));

vi.mock('../../hooks/useHreflangs', () => ({
    useHreflangs: () => [],
}));

vi.mock('../../components/SimilarProducts', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../components/RecentlyViewed', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../components/WishlistButton', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../components/SeoHead', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../components/JsonLd', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../components/ReviewForm', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../components/ReviewList', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../components/ImageLightbox', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../../ui/ga', () => ({
    GA: {
        view_item: vi.fn(),
        add_to_cart: vi.fn(),
    },
}));

vi.mock('../../ui/recentlyViewed', () => ({
    addRecentlyViewed: vi.fn(),
}));

describe('ProductPage specifications', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        productsShowMock.mockReset();
        productsRelatedMock.mockReset();
        reviewsListMock.mockReset();
    });

    it('renders localized attribute values and names for the current locale', async () => {
        const product: Product = {
            id: 101,
            name: 'Localized Figurine',
            slug: 'localized-figurine',
            category_id: 5,
            price: 1299,
            attributes: [
                {
                    key: 'color',
                    value: 'black',
                    label: 'Black',
                    translations: { 'uk-UA': 'Чорний', uk: 'Чорний' },
                },
                {
                    key: 'material',
                    value: 'cotton',
                    label: 'Бавовна',
                },
                {
                    key: 'weight',
                    value: '1.2 kg',
                },
            ],
        } as Product;

        productsShowMock.mockResolvedValue(product);
        productsRelatedMock.mockResolvedValue([]);
        reviewsListMock.mockResolvedValue({ data: [], average_rating: null, reviews_count: 0 });

        const user = userEvent.setup();

        render(
            <LocaleProvider initial="uk">
                <MemoryRouter initialEntries={[`/product/${product.slug}`]}>
                    <Routes>
                        <Route path="/product/:slug" element={<ProductPage />} />
                    </Routes>
                </MemoryRouter>
            </LocaleProvider>,
        );

        await screen.findByRole('heading', { name: product.name });

        const specsTab = await screen.findByRole('tab', { name: 'Характеристики' });
        await user.click(specsTab);

        expect(await screen.findByText('Колір')).toBeInTheDocument();
        expect(screen.getByText('Чорний')).toBeInTheDocument();

        expect(screen.getByText('Матеріал')).toBeInTheDocument();
        expect(screen.getByText('Бавовна')).toBeInTheDocument();

        expect(screen.getByText('Вага')).toBeInTheDocument();
        expect(screen.getByText('1.2 kg')).toBeInTheDocument();
    });
});
