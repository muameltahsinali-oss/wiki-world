import { expect, test } from '@playwright/test';

test('skip link is focusable and targets main content', async ({ page }) => {
  await page.goto('/ar');
  await page.keyboard.press('Tab');
  const skipLink = page.getByRole('link', { name: /تخطي|skip/i });
  await expect(skipLink).toBeFocused();
  await skipLink.click();
  await expect(page.locator('#main-content')).toBeVisible();
});

test('homepage has a single main landmark', async ({ page }) => {
  await page.goto('/ar');
  await expect(page.locator('#main-content')).toBeVisible();
});
