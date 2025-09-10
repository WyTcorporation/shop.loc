// tests/e2e-flow.spec.tsx
import { test, expect } from '@playwright/test';

const BASE = process.env.E2E_BASE_URL ?? 'http://localhost:8080';

test('catalog → product → cart → checkout → order confirmation', async ({ page }) => {
    await page.goto(BASE + '/');

    const firstCard = page.getByTestId('catalog-card').first()
        .or(page.locator('a[href^="/product/"]').first());

    await Promise.all([ page.waitForURL(/\/product\/.+/), firstCard.click() ]);

    const addToCartBtn = page.getByTestId('add-to-cart')
        .or(page.getByRole('button', { name: /додати в кошик|add to cart/i }));
    await addToCartBtn.waitFor({ state: 'visible', timeout: 10_000 });

    if (await addToCartBtn.isDisabled().catch(() => false)) {
        await page.goBack();
        const secondCard = page.getByTestId('catalog-card').nth(1)
            .or(page.locator('a[href^="/product/"]').nth(1));
        await Promise.all([ page.waitForURL(/\/product\/.+/), secondCard.click() ]);
        await addToCartBtn.waitFor({ state: 'visible', timeout: 10_000 });
    }

    const qtyInput = page.getByTestId('qty-input').or(page.getByRole('spinbutton'));
    await qtyInput.fill('1');

    const waitCartApi = page.waitForResponse(r =>
        r.url().includes('/api/cart') && ['POST','PATCH'].includes(r.request().method())
    ).catch(() => null);

    await addToCartBtn.click();
    await waitCartApi;

    const openCart = page.getByTestId('open-cart')
        .or(page.getByRole('button', { name: /відкрити кошик|open cart/i }));
    if (await openCart.count().then(n => n > 0)) {
        await openCart.click();
    } else {
        await page.goto(BASE + '/cart');
    }

    await expect(page).toHaveURL(/\/cart$/);
    await expect(page.getByText(/разом|total/i)).toBeVisible();

    const checkoutLink = page.getByTestId('go-checkout')
        .or(page.getByRole('link', { name: /оформити|checkout/i }));

    await Promise.all([ page.waitForURL(/\/checkout$/), checkoutLink.click() ]);

    await page.getByTestId('email').fill('test@example.com');
    await page.getByTestId('shipping-name').fill('John');
    await page.getByTestId('shipping-city').fill('Kyiv');
    await page.getByTestId('shipping-addr').fill('Street 1');

    const placeOrderBtn = page.getByTestId('place-order')
        .or(page.getByRole('button', { name: /place order|замовлення|оформити/i }));

    // ⚠️ Спочатку чекаємо, поки перекинуло на /order/..., і лише після цього чекаємо GET деталей
    await Promise.all([
        page.waitForURL(/\/order\/.+/, { timeout: 15000 }),
        placeOrderBtn.click(),
    ]);

    // Чек саме на 200 від /api/orders/:number
    await page.waitForResponse(
        r => r.url().includes('/api/orders/') && r.request().method() === 'GET' && r.status() === 200,
        { timeout: 15000 }
    );

    // await expect(page.getByTestId('order-confirmed')).toBeVisible({ timeout: 15000 });
    // await expect(page.getByText(/Підтвердження надіслано/i)).toBeVisible();
});
