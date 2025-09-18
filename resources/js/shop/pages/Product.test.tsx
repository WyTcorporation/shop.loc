import React from 'react';
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { render, screen, waitFor, cleanup } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import LocaleProvider from '../i18n/LocaleProvider';
import { createTranslator, localeMessages } from '../i18n/messages';
import type { Lang } from '../i18n/config';

const productsShowMock = vi.fn();
const productsRelatedMock = vi.fn();
const reviewsListMock = vi.fn();
const cartAddMock = vi.fn();
const notifySuccessMock = vi.fn();
const notifyErrorMock = vi.fn();

vi.mock('../api', () => ({
    ProductsApi: {
        show: productsShowMock,
        related: productsRelatedMock,
    },
    ReviewsApi: {
        list: reviewsListMock,
    },
}));

vi.mock('../useCart', () => ({
    __esModule: true,
    default: () => ({
        add: cartAddMock,
    }),
}));

vi.mock('../ui/notify', () => ({
    useNotify: () => ({
        success: notifySuccessMock,
        error: notifyErrorMock,
    }),
}));

vi.mock('../hooks/useHreflangs', () => ({
    useHreflangs: () => [],
}));

vi.mock('../components/SimilarProducts', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../components/RecentlyViewed', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../components/WishlistButton', () => ({
    __esModule: true,
    default: () => <div data-testid="wishlist-button" />,
}));

vi.mock('../components/SeoHead', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../components/JsonLd', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../components/ReviewForm', () => ({
    __esModule: true,
    default: () => <div data-testid="review-form" />,
}));

vi.mock('../components/ImageLightbox', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../ui/ga', () => ({
    GA: {
        view_item: vi.fn(),
        add_to_cart: vi.fn(),
    },
}));

vi.mock('../ui/recentlyViewed', () => ({
    addRecentlyViewed: vi.fn(),
}));

const { default: ProductPage } = await import('./Product');

type Scenario = {
    lang: Lang;
    translator: ReturnType<typeof createTranslator>;
};

const scenarios: Scenario[] = [
    { lang: 'uk', translator: createTranslator(localeMessages.uk) },
    { lang: 'en', translator: createTranslator(localeMessages.en) },
];

const AVERAGE_RATING = 4.5;

const baseProduct = {
    id: 1,
    slug: 'test-product',
    name: 'Test Product',
    price: 1999,
    stock: 5,
    category_id: 10,
    preview_url: null,
    images: [
        { url: 'https://example.com/primary.jpg', alt: 'Primary', is_primary: true },
        { url: 'https://example.com/secondary.jpg', alt: 'Secondary' },
    ],
    attributes: {},
};

function renderProduct(lang: Lang) {
    return render(
        <LocaleProvider initial={lang}>
            <MemoryRouter initialEntries={[`/product/${baseProduct.slug}`]}>
                <Routes>
                    <Route path="/product/:slug" element={<ProductPage />} />
                </Routes>
            </MemoryRouter>
        </LocaleProvider>
    );
}

describe('Product page localization', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        cleanup();

        productsShowMock.mockImplementation(() => Promise.resolve({ ...baseProduct }));
        productsRelatedMock.mockResolvedValue([]);
        reviewsListMock.mockResolvedValue({ data: [], average_rating: AVERAGE_RATING });
    });

    it.each(scenarios)('renders localized labels for %s locale', async ({ lang, translator }) => {
        const user = userEvent.setup();
        renderProduct(lang);

        await screen.findByRole('heading', { name: baseProduct.name });

        const stockText = translator('product.stock.available', { count: baseProduct.stock });
        await screen.findByText(stockText);

        expect(screen.getByText(translator('product.description.empty'))).toBeInTheDocument();

        expect(
            screen.getByRole('button', { name: translator('product.actions.addToCart') })
        ).toBeInTheDocument();

        expect(
            screen.getByRole('button', { name: translator('product.gallery.openImage', { index: 2 }) })
        ).toBeInTheDocument();

        const deliveryTab = screen.getByRole('tab', { name: translator('product.tabs.delivery') });
        expect(deliveryTab).toBeInTheDocument();
        await user.click(deliveryTab);
        expect(screen.getByText(translator('product.delivery.items.payment'))).toBeInTheDocument();
        const summaryText = `${translator('product.reviews.summary.label')} ${AVERAGE_RATING.toFixed(1)} ${translator('product.reviews.summary.of', { max: 5 })}`;
        const summaryElements = screen.getAllByText((_, node) => node?.textContent === summaryText);
        expect(summaryElements.length).toBeGreaterThan(0);
        expect(
            screen.getByRole('heading', { name: translator('product.reviews.title') })
        ).toBeInTheDocument();

        await waitFor(() => {
            expect(screen.queryByText(translator('product.reviews.loading'))).not.toBeInTheDocument();
        });

        expect(screen.getByText(translator('product.reviews.empty'))).toBeInTheDocument();
    });
});
