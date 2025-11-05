/**
 * Browser Test: Tenant Creation Flow
 *
 * This test validates the complete tenant creation process including:
 * - Authentication
 * - Form validation
 * - Progress tracking
 * - Error handling
 */

const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false, slowMo: 500 });
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    ignoreHTTPSErrors: true
  });
  const page = await context.newPage();

  try {
    console.log('🚀 Starting Tenant Creation Test...\n');

    // Step 1: Navigate to login page
    console.log('📍 Step 1: Navigating to login page...');
    await page.goto('http://midastech.testing.in:8085/midas-admin/login');
    await page.waitForLoadState('networkidle');
    console.log('✅ Login page loaded\n');

    // Step 2: Login
    console.log('📍 Step 2: Logging in as admin...');
    await page.fill('input[name="email"]', 'admin@midastech.in');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Verify login success
    const currentUrl = page.url();
    if (currentUrl.includes('/midas-admin') && !currentUrl.includes('/login')) {
      console.log('✅ Login successful\n');
    } else {
      throw new Error('❌ Login failed - still on login page');
    }

    // Step 3: Navigate to tenant creation page
    console.log('📍 Step 3: Navigating to tenant creation page...');
    await page.goto('http://midastech.testing.in:8085/midas-admin/tenants/create');
    await page.waitForLoadState('networkidle');
    console.log('✅ Tenant creation page loaded\n');

    // Step 4: Test validation - submit empty form
    console.log('📍 Step 4: Testing form validation (empty form)...');
    await page.click('button#createTenantBtn');
    await page.waitForTimeout(1000);

    // Check for validation errors
    const validationErrors = await page.$$('.invalid-feedback');
    if (validationErrors.length > 0) {
      console.log(`✅ Validation working - ${validationErrors.length} validation errors displayed\n`);
    } else {
      console.log('⚠️  Warning: No validation errors shown for empty form\n');
    }

    // Step 5: Fill in tenant creation form
    console.log('📍 Step 5: Filling tenant creation form...');
    const timestamp = Date.now();
    const testSubdomain = `test${timestamp}`;

    await page.fill('input[name="company_name"]', `Test Company ${timestamp}`);
    await page.fill('input[name="subdomain"]', testSubdomain);
    await page.selectOption('select[name="domain"]', 'midastech.testing.in');
    await page.fill('input[name="email"]', `test${timestamp}@example.com`);
    await page.fill('input[name="phone"]', '9876543210');

    // Admin details
    await page.fill('input[name="admin_first_name"]', 'Test');
    await page.fill('input[name="admin_last_name"]', 'Admin');
    await page.fill('input[name="admin_email"]', `admin${timestamp}@example.com`);
    await page.fill('input[name="admin_password"]', 'TestPassword123');

    // Select plan
    await page.selectOption('select[name="plan_id"]', '1');

    // Set trial days explicitly (default is 14)
    await page.fill('input[name="trial_days"]', '14');

    console.log(`✅ Form filled with subdomain: ${testSubdomain}\n`);

    // Step 6: Submit form and watch progress
    console.log('📍 Step 6: Submitting form and monitoring progress...');

    // Listen for console logs from the page
    page.on('console', msg => {
      if (msg.type() === 'error') {
        console.log(`   🔴 Browser Error: ${msg.text()}`);
      }
    });

    // Click submit button
    await page.click('button#createTenantBtn');

    // Wait for progress modal to appear
    console.log('   ⏳ Waiting for progress modal...');
    await page.waitForSelector('#progressModal.show', { timeout: 5000 });
    console.log('   ✅ Progress modal appeared\n');

    // Monitor progress
    console.log('📊 Monitoring tenant creation progress:');
    let lastPercentage = 0;
    let progressComplete = false;
    let hasError = false;

    for (let i = 0; i < 60; i++) { // Monitor for up to 60 seconds
      await page.waitForTimeout(1000);

      // Get progress percentage
      const percentage = await page.textContent('#progressPercentage').catch(() => '0%');
      const currentPercentage = parseInt(percentage);

      if (currentPercentage > lastPercentage) {
        console.log(`   📈 Progress: ${percentage}`);
        lastPercentage = currentPercentage;
      }

      // Check for completion
      const successDisplay = await page.$('#successDisplay:not(.d-none)');
      if (successDisplay) {
        progressComplete = true;
        console.log('\n✅ Tenant creation completed successfully!\n');
        break;
      }

      // Check for errors
      const errorDisplay = await page.$('#errorDisplay:not(.d-none)');
      if (errorDisplay) {
        hasError = true;
        const errorText = await page.textContent('#errorMessage');
        console.log(`\n❌ Error occurred: ${errorText}\n`);
        break;
      }

      // Check if still processing
      const progressStatus = await page.$('.progress-bar-animated');
      if (!progressStatus && i > 10) {
        console.log('\n⚠️  Progress bar stopped animating\n');
        break;
      }
    }

    // Step 7: Capture final state
    console.log('📍 Step 7: Capturing final state...');

    if (progressComplete) {
      // Take success screenshot
      await page.screenshot({ path: 'tests/Browser/screenshots/tenant-creation-success.png', fullPage: true });
      console.log('📸 Success screenshot saved to: tests/Browser/screenshots/tenant-creation-success.png');

      // Get progress steps
      const stepsHtml = await page.textContent('#progressSteps');
      console.log('\n📋 Progress Steps:');
      console.log(stepsHtml);

      console.log('\n🎉 TEST PASSED: Tenant created successfully!');

    } else if (hasError) {
      // Take error screenshot
      await page.screenshot({ path: 'tests/Browser/screenshots/tenant-creation-error.png', fullPage: true });
      console.log('📸 Error screenshot saved to: tests/Browser/screenshots/tenant-creation-error.png');

      // Get error details
      const errorDetails = await page.textContent('#errorMessage');
      console.log('\n❌ Error Details:', errorDetails);

      // Get progress steps to see where it failed
      const stepsHtml = await page.textContent('#progressSteps');
      console.log('\n📋 Progress Steps (before failure):');
      console.log(stepsHtml);

      console.log('\n❌ TEST FAILED: Error during tenant creation');
      process.exit(1);

    } else {
      // Take timeout screenshot
      await page.screenshot({ path: 'tests/Browser/screenshots/tenant-creation-timeout.png', fullPage: true });
      console.log('📸 Timeout screenshot saved to: tests/Browser/screenshots/tenant-creation-timeout.png');

      console.log('\n⏱️  TEST TIMEOUT: Tenant creation did not complete within expected time');
      process.exit(1);
    }

  } catch (error) {
    console.error('\n💥 TEST ERROR:', error.message);

    // Take error screenshot
    await page.screenshot({ path: 'tests/Browser/screenshots/test-error.png', fullPage: true });
    console.log('📸 Error screenshot saved to: tests/Browser/screenshots/test-error.png');

    console.log('\n❌ TEST FAILED with exception');
    process.exit(1);

  } finally {
    await browser.close();
    console.log('\n🏁 Browser closed');
  }
})();
