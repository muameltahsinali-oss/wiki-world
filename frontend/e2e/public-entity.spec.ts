import { expect, test } from '@playwright/test';

test.describe('Public entity pages', () => {
  test('Arabic locale sets RTL direction', async ({ page }) => {
    await page.goto('/ar/persons');
    await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
    await expect(page.locator('html')).toHaveAttribute('lang', 'ar');
  });

  test('English locale sets LTR direction', async ({ page }) => {
    await page.goto('/en/persons');
    await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
  });

  test('persons browse page renders', async ({ page }) => {
    await page.goto('/ar/persons');
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
  });

  test('draft entity UUID returns 404', async ({ page }) => {
    const draftUuid = '00000000-0000-4000-8000-000000000001';
    const response = await page.goto(`/ar/persons/${draftUuid}`);
    expect(response?.status()).toBe(404);
  });
});
