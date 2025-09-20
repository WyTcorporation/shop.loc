import { test, expect } from '@playwright/test';

const BASE = (process.env.E2E_BASE_URL ?? 'http://localhost:8080').replace(/\/$/, '');
const API_BASE = (process.env.E2E_API_URL ?? `${BASE}/api`).replace(/\/$/, '');

function uniqueSuffix() {
    return Math.random().toString(36).slice(2, 10);
}

test('profile: user can update personal data', async ({ page }) => {
    const suffix = `${Date.now()}-${uniqueSuffix()}`;
    const email = `user+${suffix}@example.com`;
    const password = 'Secret123!';

    const registerResponse = await page.request.post(`${API_BASE}/auth/register`, {
        data: {
            name: 'Test User',
            email,
            password,
            password_confirmation: password,
        },
    });

    expect(registerResponse.ok()).toBeTruthy();
    const body = await registerResponse.json();
    expect(body?.token).toBeTruthy();
    const token = body.token as string;

    await page.addInitScript((tokenValue: string) => {
        window.localStorage.setItem('sanctum_token', tokenValue);
    }, token);

    await page.goto(`${BASE}/profile`);

    await expect(page.getByTestId('profile-form')).toBeVisible();

    const newName = `Updated ${suffix}`;
    const newEmail = `updated+${suffix}@example.com`;

    await page.getByTestId('profile-name').fill(newName);
    await page.getByTestId('profile-email').fill(newEmail);
    await page.getByTestId('profile-password').fill(password);
    await page.getByTestId('profile-password-confirmation').fill(password);

    const waitUpdate = page.waitForResponse(
        (response) =>
            response.url().includes('/api/auth/me') &&
            response.request().method() === 'PUT' &&
            response.status() >= 200 &&
            response.status() < 300,
    );

    await page.getByTestId('profile-save').click();
    await waitUpdate;

    await expect(page.getByTestId('profile-success')).toBeVisible();
    await expect(page.getByTestId('profile-name-display')).toHaveText(newName);
    await expect(page.getByTestId('profile-email-display')).toHaveText(newEmail);
});
