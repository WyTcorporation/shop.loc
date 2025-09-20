import { test, expect } from '@playwright/test';

test('wishlist: add → page shows item → clear → empty state', async ({ page }) => {
    // Каталог
    await page.goto('/');
    const firstCard = page.getByTestId('catalog-card').first();
    await expect(firstCard).toBeVisible();
    await firstCard.click();

    // Продукт → додати в обране
    const toggle = page
        .getByTestId('wishlist-toggle')
        .or(page.getByRole('button', { name: /в обране|в обраному|wishlist/i }));
    await expect(toggle).toBeVisible();
    await toggle.click();

    // Сторінка "Обране"
    await page.goto('/wishlist');
    await expect(page.getByTestId('wishlist-page')).toBeVisible();

    // Є хоч одна картка
    const firstWish = page.getByTestId('wishlist-card').first();
    await expect(firstWish).toBeVisible();

    // Очистити
    const clearBtn = page.getByRole('button', { name: /очистити/i });
    await expect(clearBtn).toBeVisible();
    await clearBtn.click();

    // Порожній стан
    await expect(page.getByTestId('wishlist-empty')).toBeVisible();
});
