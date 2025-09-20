import { test, expect } from '@playwright/test';

test.describe('mini-cart', () => {
    test('opens popover, shows item and navigates', async ({ page }) => {
        // каталог
        await page.goto('/');
        const firstCard = page.getByTestId('catalog-card').first();
        await expect(firstCard).toBeVisible();
        await firstCard.click();

        // продукт
        const addBtn = page.getByTestId('add-to-cart').or(page.getByRole('button', { name: /додати в кошик|add to cart/i }));
        await expect(addBtn).toBeVisible();
        await page.getByTestId('qty-input').fill('1');
        await addBtn.click();

        // відкрити міні-кошик
        const trigger = page.getByTestId('open-mini-cart').or(page.getByRole('button', { name: /кошик|cart/i }));
        await trigger.click();

        // контент поповера
        const mini = page.getByTestId('mini-cart');
        await expect(mini).toBeVisible();

        // є хоча б один рядок з товарами (перевірка по сумі/назві — на вибір)
        await expect(mini).toContainText(/Разом/i);

        // перехід у кошик
        await page.getByTestId('mini-to-cart').click();
        await expect(page).toHaveURL(/\/cart$/);

        // повернутися і перевірити авто-закриття на роут-чейндж
        await page.goBack(); // назад на продукт
        await expect(page.getByTestId('mini-cart')).toHaveCount(0); // поповер закритий
    });
});
