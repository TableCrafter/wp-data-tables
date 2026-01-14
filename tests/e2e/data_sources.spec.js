const { test, expect } = require('@playwright/test');

test.describe('TableCrafter Data Sources', () => {
  
  // Login before tests (Simplistic implementation - assumes standard WP login)
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-login.php');
    await page.fill('#user_login', process.env.WP_USER || 'admin');
    await page.fill('#user_pass', process.env.WP_PASS || 'password');
    await page.click('#wp-submit');
    await expect(page.locator('#wpadminbar')).toBeVisible();
  });

  test('Google Sheets URL renders table', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=tablecrafter-wp-data-tables');
    
    // Fill Google Sheet URL (Public Demo Sheet)
    const publicSheetUrl = 'https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit';
    await page.fill('#tc-preview-url', publicSheetUrl);
    
    // Trigger Preview
    await page.click('#tc-preview-btn');
    
    // Expect "Loading..." to appear then disappear
    await expect(page.locator('.tc-loading')).toBeVisible({ timeout: 2000 });
    
    // Expect Table to render
    // Check for a known cell or structure (e.g. <thead>)
    await expect(page.locator('#tc-preview-container table')).toBeVisible({ timeout: 15000 });
    
    // Check specific class from Smart Formatting (v2.3)
    // await expect(page.locator('.tc-badge').first()).toBeVisible();
  });

  test('CSV Upload Button exists', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=tablecrafter-wp-data-tables');
    await expect(page.locator('#tc-upload-csv-btn')).toBeVisible();
  });

});
