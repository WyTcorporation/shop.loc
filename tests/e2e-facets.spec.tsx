import { test, expect } from '@playwright/test';

test('facets: color/size & price range update URL and results', async ({ page }) => {
    await page.goto('/');

    // є товари
    const gridCards = page.getByTestId('catalog-card');
    await expect(gridCards.first()).toBeVisible();

    // спробуємо клікнути перший доступний колір (якщо є)
    const colorChip = page.locator('[data-testid^="facet-color-"]').first();
    if (await colorChip.count()) {
        await colorChip.click();
        await expect(page).toHaveURL(/color=/);
        await expect(gridCards.first()).toBeVisible(); // список перерендерився
    }

    // спробуємо клікнути перший доступний розмір (якщо є)
    const sizeChip = page.locator('[data-testid^="facet-size-"]').first();
    if (await sizeChip.count()) {
        await sizeChip.click();
        await expect(page).toHaveURL(/size=/);
        await expect(gridCards.first()).toBeVisible();
    }

    // застосувати діапазон цін
    await page.getByTestId('price-min').fill('1');
    await page.getByTestId('price-max').fill('999999');
    await page.getByTestId('apply-price').click();
    await expect(page).toHaveURL(/min_price=1/);
    await expect(page).toHaveURL(/max_price=999999/);
    await expect(gridCards.first()).toBeVisible();

    // скинути все
    await page.getByTestId('clear-filters').click();
    await expect(page).not.toHaveURL(/color=|size=|min_price=|max_price=/);
    await expect(gridCards.first()).toBeVisible();
});
