import { expect, test } from '@playwright/test';

test.describe('Ahlulbayt module', () => {
  test('landing page renders', async ({ page }) => {
    await page.goto('/ar/ahlulbayt');
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
  });

  test('browse page renders with filters', async ({ page }) => {
    await page.goto('/ar/ahlulbayt/browse');
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    await expect(page.getByLabel(/بحث|search/i)).toBeVisible();
  });

  test('timeline page renders', async ({ page }) => {
    await page.goto('/ar/timeline');
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
  });
});
