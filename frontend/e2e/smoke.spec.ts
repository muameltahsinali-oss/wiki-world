import { expect, test } from '@playwright/test';

test('redirects root to Arabic locale', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveURL(/\/ar/);
});

test('foundation homepage renders', async ({ page }) => {
  await page.goto('/ar');
  await expect(page.locator('body')).toBeVisible();
});
