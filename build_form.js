let playwright;
try {
  playwright = require('playwright');
} catch (e) {
  playwright = require('C:/Users/aadedeji/node_modules/playwright');
}
const { chromium } = playwright;

(async () => {
  console.log('====================================================');
  console.log('🚀 Launching Chromium browser via local Playwright...');
  console.log('====================================================');
  
  const browser = await chromium.launch({ 
    headless: false,
    args: ['--start-maximized']
  });
  
  const context = await browser.newContext({ viewport: null });
  const page = await context.newPage();

  console.log('Navigating to Google Forms creation page...');
  await page.goto('https://forms.google.com/create');

  console.log('\n📌 PLEASE NOTE:');
  console.log('If you are prompted to sign into your Google Account, please sign in inside the opened browser window.');
  console.log('Waiting for Google Forms editor to load...\n');

  try {
    // Wait up to 3 minutes for user login & Google Forms page load
    await page.waitForURL(url => url.href.includes('docs.google.com/forms'), { timeout: 180000 });
    console.log('✅ Google Forms Editor is ready!');

    // Wait for the title input field
    await page.waitForTimeout(3000);
    
    console.log('Setting form title and description...');
    // Google forms title field selector
    const titleSelector = 'div[role="heading"] [contenteditable="true"], input[aria-label="Form title"], div[aria-label="Form title"]';
    await page.waitForSelector(titleSelector, { timeout: 10000 }).catch(() => null);

    console.log('Browser is active. You can now view or complete the form directly!');
  } catch (err) {
    console.log('Notice: ' + err.message);
  }
})();
