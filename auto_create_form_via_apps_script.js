let playwright;
try {
  playwright = require('playwright');
} catch (e) {
  playwright = require('C:/Users/aadedeji/node_modules/playwright');
}
const { chromium } = playwright;
const fs = require('fs');

(async () => {
  console.log('====================================================');
  console.log('🚀 Opening Apps Script Editor to generate Google Form...');
  console.log('====================================================');

  const browser = await chromium.launch({ 
    headless: false,
    args: ['--start-maximized']
  });
  
  const context = await browser.newContext({ viewport: null });
  const page = await context.newPage();

  console.log('Navigating to Google Apps Script (script.google.com/create)...');
  await page.goto('https://script.google.com/create');

  console.log('\n📌 Waiting for Apps Script editor to load (Sign in if required)...');
  
  try {
    await page.waitForURL(url => url.href.includes('script.google.com'), { timeout: 180000 });
    console.log('✅ Google Apps Script Editor is ready!');

    // Read the script code
    const scriptCode = fs.readFileSync('C:/Users/aadedeji/.gemini/antigravity-ide/brain/46b7229e-1f75-4b40-ae4b-a1c14eca5794/create_histoplasmosis_pretest_google_form.gs', 'utf8');

    console.log('Copying script to clipboard and focusing editor...');
    await page.waitForTimeout(4000);

    // Click on editor area
    const editor = page.locator('.monaco-editor').first();
    if (await editor.isVisible()) {
      await editor.click();
      await page.keyboard.press('Control+A');
      await page.keyboard.press('Backspace');
      
      // Paste script via evaluate or typing
      await page.evaluate((code) => {
        navigator.clipboard.writeText(code);
      }, scriptCode);
      
      await page.keyboard.press('Control+V');
      console.log('✅ Script pasted successfully into Apps Script editor!');
      
      console.log('\n👉 Simply click the "Run" ▶ button at the top of the Apps Script window to create your Google Form!');
    } else {
      console.log('Editor ready! You can paste the code from create_histoplasmosis_pretest_google_form.gs and click Run.');
    }
  } catch (err) {
    console.log('Notice: ' + err.message);
  }
})();
