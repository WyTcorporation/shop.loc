import { render, screen } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { afterEach, describe, expect, it, vi } from 'vitest';

import OrderConfirmation from '../OrderConfirmation';
import { OrdersApi } from '../../api';

type OrderResponse = Awaited<ReturnType<typeof OrdersApi.show>>;

vi.mock('../../components/SeoHead', () => ({
    default: () => null,
}));

vi.mock('@/shop/components/PayOrder', () => ({
    default: () => <div data-testid="pay-order" />,
}));

vi.mock('../../components/OrderChat', () => ({
    default: () => null,
}));

vi.mock('../../ui/ga', () => ({
    GA: {
        purchase: vi.fn(),
    },
}));

afterEach(() => {
    vi.restoreAllMocks();
});

function renderOrderPage(order: OrderResponse) {
    vi.spyOn(OrdersApi, 'show').mockResolvedValueOnce(order);

    render(
        <MemoryRouter initialEntries={[`/order/${order.number}`]}>
            <Routes>
                <Route path="/order/:number" element={<OrderConfirmation />} />
            </Routes>
        </MemoryRouter>,
    );
}

describe('OrderConfirmation billing details', () => {
    const baseOrder: OrderResponse = {
        id: 1,
        number: 'ORD-001',
        total: 120,
        email: 'buyer@example.com',
        status: 'placed',
        payment_status: 'pending',
        items: [],
        shipment: null,
        shipping_address: null,
        billing_address: null,
        currency: 'EUR',
    };

    it('renders billing block when billing address has fields', async () => {
        renderOrderPage({
            ...baseOrder,
            billing_address: {
                name: 'John Doe',
                city: 'Kyiv',
                addr: 'Main st. 1',
                postal_code: '01001',
                company: null,
                tax_id: null,
            },
        });

        await screen.findByTestId('order-confirmed');

        expect(screen.getByText('Платіжні дані')).toBeInTheDocument();
        expect(screen.getByText('John Doe')).toBeInTheDocument();
    });

    it('does not render billing block when billing address is empty', async () => {
        renderOrderPage({
            ...baseOrder,
            number: 'ORD-002',
            billing_address: {
                name: '',
                city: '   ',
                addr: '',
                postal_code: null,
                company: '',
                tax_id: undefined,
            },
        });

        await screen.findByTestId('order-confirmed');

        expect(screen.queryByText('Платіжні дані')).not.toBeInTheDocument();
    });

    it('shows coupon and totals summary when discount applied', async () => {
        renderOrderPage({
            ...baseOrder,
            number: 'ORD-003',
            items: [
                {
                    id: 11,
                    product_id: 5,
                    qty: 2,
                    price: 50,
                    product: null,
                },
            ],
            subtotal: 100,
            discount_total: 15,
            coupon_code: 'SAVE15',
            coupon_discount: 15,
            total: 85,
        });

        await screen.findByTestId('order-confirmed');

        expect(screen.getByText('Разом за товари')).toBeInTheDocument();
        expect(screen.getByText('Купон')).toBeInTheDocument();
        expect(screen.getByText('SAVE15')).toBeInTheDocument();
        expect(screen.getByText('Знижка')).toBeInTheDocument();
        expect(screen.getByText(/−15,00/)).toBeInTheDocument();
        expect(screen.getByText('До сплати')).toBeInTheDocument();
        expect(screen.getByText(/85,00/)).toBeInTheDocument();
    });
});
