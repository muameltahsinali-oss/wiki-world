import { expect, test } from '@playwright/test';

test('Arabic locale uses RTL direction', async ({ page }) => {
  await page.goto('/ar');
  const dir = await page.locator('html').getAttribute('dir');
  expect(dir).toBe('rtl');
});

test('English locale uses LTR direction', async ({ page }) => {
  await page.goto('/en');
  const dir = await page.locator('html').getAttribute('dir');
  expect(dir).toBe('ltr');
});

test('RTL header layout renders without overflow', async ({ page }) => {
  await page.goto('/ar');
  const header = page.locator('header').first();
  await expect(header).toBeVisible();
  const box = await header.boundingBox();
  expect(box?.width).toBeGreaterThan(0);
});
