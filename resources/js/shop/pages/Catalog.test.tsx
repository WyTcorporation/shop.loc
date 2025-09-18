import React from 'react';
import { describe, expect, it, beforeEach, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';

const cartApiMock = {
    add: vi.fn<[number, number?], Promise<unknown>>(),
};

const fetchCategoriesMock = vi.fn();
const fetchProductsMock = vi.fn();

vi.mock('../api', () => ({
    fetchCategories: fetchCategoriesMock,
    fetchProducts: fetchProductsMock,
    CartApi: cartApiMock,
}));

vi.mock('../useCart', () => ({
    __esModule: true,
    default: () => ({
        add: cartApiMock.add,
    }),
}));

vi.mock('../components/WishlistButton', () => ({
    __esModule: true,
    default: () => <div data-testid="wishlist-button" />, // stub to simplify rendering
}));

vi.mock('../components/SeoHead', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../components/JsonLd', () => ({
    __esModule: true,
    default: () => null,
}));

vi.mock('../ui/ga', () => ({
    GA: { view_item_list: vi.fn() },
}));

vi.mock('@/components/ui/button', () => ({
    Button: ({ children, ...props }: React.ButtonHTMLAttributes<HTMLButtonElement>) => (
        <button {...props}>{children}</button>
    ),
}), { virtual: true });

vi.mock('@/components/ui/card', () => ({
    Card: ({ children, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
        <div {...props}>{children}</div>
    ),
}), { virtual: true });

vi.mock('@/components/ui/input', () => ({
    Input: (props: React.InputHTMLAttributes<HTMLInputElement>) => <input {...props} />,
}), { virtual: true });

vi.mock('@/components/ui/skeleton', () => ({
    Skeleton: ({ children, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
        <div {...props}>{children}</div>
    ),
}), { virtual: true });

vi.mock('@/components/ui/select', () => {
    const Select = ({ children }: { children: React.ReactNode }) => <div>{children}</div>;
    const SelectTrigger = ({ children, ...props }: React.ButtonHTMLAttributes<HTMLButtonElement>) => (
        <button {...props}>{children}</button>
    );
    const SelectValue = ({ children }: { children?: React.ReactNode }) => <span>{children}</span>;
    const SelectContent = ({ children }: { children: React.ReactNode }) => <div>{children}</div>;
    const SelectItem = ({ children, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
        <div {...props}>{children}</div>
    );
    return { Select, SelectTrigger, SelectValue, SelectContent, SelectItem };
}, { virtual: true });

const { default: Catalog } = await import('./Catalog');

function createDeferred<T>() {
    let resolve!: (value: T | PromiseLike<T>) => void;
    let reject!: (reason?: unknown) => void;
    const promise = new Promise<T>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
}

describe('Catalog page', () => {
    beforeEach(() => {
        vi.clearAllMocks();

        const product = {
            id: 101,
            name: 'Тестовий товар',
            slug: 'test-product',
            price: 1999,
            images: [],
            stock: 5,
        };

        fetchCategoriesMock.mockResolvedValue([]);
        fetchProductsMock.mockResolvedValue({
            data: [product],
            current_page: 1,
            last_page: 1,
            per_page: 12,
            total: 1,
            from: 1,
            to: 1,
            facets: {},
        });
        cartApiMock.add.mockResolvedValue({});
    });

    it('calls CartApi.add when clicking the Buy button', async () => {
        const user = userEvent.setup();
        const deferred = createDeferred<unknown>();
        cartApiMock.add.mockReturnValueOnce(deferred.promise);

        render(
            <MemoryRouter>
                <Catalog />
            </MemoryRouter>
        );

        const buyButton = await screen.findByRole('button', { name: 'Купити' });
        await user.click(buyButton);

        expect(cartApiMock.add).toHaveBeenCalledWith(101);

        await waitFor(() =>
            expect(screen.getByRole('button', { name: 'Купуємо…' })).toBeDisabled()
        );

        deferred.resolve({});

        await waitFor(() =>
            expect(screen.getByRole('button', { name: 'Купити' })).toBeEnabled()
        );
    });

    it('deduplicates category and color facets', async () => {
        const product = {
            id: 202,
            name: 'Ще один товар',
            slug: 'another-product',
            price: 2599,
            images: [],
            stock: 4,
        };

        fetchCategoriesMock.mockResolvedValueOnce([
            { id: 10, name: 'Кросівки' },
        ]);
        fetchProductsMock.mockResolvedValueOnce({
            data: [product],
            current_page: 1,
            last_page: 1,
            per_page: 12,
            total: 1,
            from: 1,
            to: 1,
            facets: {
                category_id: {
                    '10': 5,
                    misc: 2,
                },
                'attrs.color': {
                    Black: 2,
                    ' black ': 1,
                    BLACK: 4,
                },
            },
        });

        render(
            <MemoryRouter>
                <Catalog />
            </MemoryRouter>
        );

        const categoryButton = await screen.findByTestId('facet-cat-10');
        expect(categoryButton).toBeInTheDocument();
        expect(screen.queryByTestId('facet-cat-misc')).not.toBeInTheDocument();

        const colorButtons = await screen.findAllByTestId('facet-color-black');
        expect(colorButtons).toHaveLength(1);
        expect(colorButtons[0]).toHaveTextContent('Black');
        expect(colorButtons[0]).toHaveTextContent('(7)');
    });

    it('disables buying for products with zero stock', async () => {
        const user = userEvent.setup();
        fetchProductsMock.mockResolvedValueOnce({
            data: [
                {
                    id: 303,
                    name: 'Товар без залишків',
                    slug: 'out-of-stock-product',
                    price: 1499,
                    images: [],
                    stock: 0,
                },
            ],
            current_page: 1,
            last_page: 1,
            per_page: 12,
            total: 1,
            from: 1,
            to: 1,
            facets: {},
        });

        render(
            <MemoryRouter>
                <Catalog />
            </MemoryRouter>
        );

        const outOfStockButton = await screen.findByRole('button', { name: 'Немає в наявності' });
        expect(outOfStockButton).toBeDisabled();

        await user.click(outOfStockButton);
        expect(cartApiMock.add).not.toHaveBeenCalled();

        expect(screen.getAllByText('Немає в наявності')[0]).toBeInTheDocument();
    });
});
