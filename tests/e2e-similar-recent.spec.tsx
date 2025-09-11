import { test, expect } from '@playwright/test';

test.describe('similar & recently viewed', () => {
    test('recently viewed: first empty → then shows item; similar present', async ({ page }) => {
        // Каталог
        await page.goto('/');

        // Відкриваємо перший товар
        const gridCards = page.getByTestId('catalog-card');
        await expect(gridCards.first()).toBeVisible();
        await gridCards.first().click();

        // На сторінці товару:
        await expect(page.getByTestId('recently-section')).toBeVisible();

        // На першому заході в "Нещодавно переглянуті" — порожньо (після короткої 150мс ініціалізації)
        const recentlyEmpty = page.getByTestId('recently-empty');
        await expect(recentlyEmpty).toBeVisible();

        // Секція "Схожі" відображається (може бути скелетон або картки або порожньо — достатньо, що секція є)
        await expect(page.getByTestId('similar-section')).toBeVisible();

        // Повертаємось у каталог
        await page.getByRole('link', { name: /до каталогу/i }).click();
        await expect(page).toHaveURL('/');

        // Відкриваємо наступний товар
        await expect(gridCards.nth(1)).toBeVisible();
        await gridCards.nth(1).click();

        // Тепер "Нещодавно переглянуті" повинні містити щонайменше 1 картку
        const recentlyCards = page.getByTestId('recently-card');
        await expect(recentlyCards.first()).toBeVisible();
    });
});
