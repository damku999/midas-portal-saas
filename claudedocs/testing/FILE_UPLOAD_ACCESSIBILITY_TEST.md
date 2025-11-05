# File Upload Accessibility Testing

**Status**: Testing tenant file storage accessibility
**Date**: 2025-11-04

---

## Overview

This document verifies that all uploaded files are accessible through the tenant-specific storage route and that tenant isolation is properly maintained.

## Architecture

### Storage Structure
```
storage/
├── app/
│   └── public/                      # Central storage
│       └── (central files)
└── tenant{tenant-id}/
    └── app/
        └── public/                  # Tenant-specific storage
            ├── customer_insurances/ # Policy documents
            ├── lead_documents/      # Lead attachments
            ├── quotations/          # Quotation files
            └── (other tenant files)
```

### File Serving Route
**Route**: `/storage/{path}`
**File**: `routes/tenant-storage.php`
**Middleware**: `['web', 'tenant']` (NO subscription.status - allows file viewing)

---

## Files to Test

### 1. Customer Insurance Documents
**Table**: `customer_insurances`
**Column**: `policy_document_path`
**Storage Path**: `customer_insurances/{id}/policy.pdf`

### 2. Lead Documents
**Table**: `lead_documents`
**Column**: `file_path`
**Storage Path**: `lead_documents/{lead_id}/{filename}`

### 3. Quotation Files
**Table**: `quotations`
**Column**: `quotation_file_path` (if exists)
**Storage Path**: `quotations/{id}/{filename}`

---

## Test Script

### Step 1: Check Tenant Storage Route Registration

```bash
php artisan route:list | grep storage
```

**Expected Output**:
```
GET|HEAD  storage/{path} .................... tenant.storage
```

✅ **Result**: Route exists

---

### Step 2: Test File Upload & Storage

Create a test script to upload and verify files:

```php
<?php
// Run via: php artisan tinker

use Illuminate\Support\Facades\Storage;

// Test 1: Upload a test file
$testContent = "Test file content for tenant storage verification";
$testPath = "test_uploads/test_file.txt";

Storage::disk('public')->put($testPath, $testContent);

echo "File uploaded to: " . $testPath . "\n";
echo "File exists: " . (Storage::disk('public')->exists($testPath) ? "YES" : "NO") . "\n";
echo "File URL: " . Storage::disk('public')->url($testPath) . "\n";

// Test 2: Read the file back
$content = Storage::disk('public')->get($testPath);
echo "File content: " . $content . "\n";

// Test 3: Get file metadata
echo "MIME Type: " . Storage::disk('public')->mimeType($testPath) . "\n";
echo "File Size: " . Storage::disk('public')->size($testPath) . " bytes\n";

// Cleanup
Storage::disk('public')->delete($testPath);
echo "File deleted\n";
```

---

### Step 3: Test Real Policy Document Access

```bash
php artisan tinker
```

```php
// Find a customer insurance with policy document
$insurance = \App\Models\CustomerInsurance::whereNotNull('policy_document_path')->first();

if ($insurance) {
    echo "Policy Number: " . $insurance->policy_no . "\n";
    echo "Document Path: " . $insurance->policy_document_path . "\n";
    echo "File Exists: " . (Storage::disk('public')->exists($insurance->policy_document_path) ? "YES" : "NO") . "\n";

    // Generate URL
    $url = url('/storage/' . $insurance->policy_document_path);
    echo "Access URL: " . $url . "\n";
    echo "\nTest by visiting this URL in browser\n";
} else {
    echo "No policy documents found in database\n";
}
```

---

### Step 4: Test Lead Document Access

```php
// Find a lead document
$leadDoc = \App\Models\LeadDocument::first();

if ($leadDoc) {
    echo "Lead ID: " . $leadDoc->lead_id . "\n";
    echo "Document Name: " . $leadDoc->document_name . "\n";
    echo "File Path: " . $leadDoc->file_path . "\n";
    echo "File Exists: " . (Storage::disk('public')->exists($leadDoc->file_path) ? "YES" : "NO") . "\n";

    $url = url('/storage/' . $leadDoc->file_path);
    echo "Access URL: " . $url . "\n";
}
```

---

### Step 5: Test Tenant Isolation

**Critical Security Test**: Verify tenant A cannot access tenant B's files

```php
// Get current tenant ID
$currentTenant = tenant();
echo "Current Tenant: " . $currentTenant->id . "\n";

// Try to access file with path traversal (should be blocked)
$maliciousPath = "../tenant999/app/public/test.pdf";
$exists = Storage::disk('public')->exists($maliciousPath);
echo "Malicious path exists: " . ($exists ? "FAIL - SECURITY ISSUE!" : "PASS - Blocked") . "\n";

// Verify FilesystemTenancyBootstrapper is active
$disk = Storage::disk('public');
echo "Root path: " . $disk->path('') . "\n";
echo "Should contain: storage/tenant" . $currentTenant->id . "\n";
```

---

## Browser Testing

### Test 1: Policy Document Download

1. Login to tenant portal: `http://demo.midastech.testing.in:8085`
2. Navigate to customer insurance list
3. Click on a policy with document
4. Click "Download Policy" button
5. **Expected**: File downloads successfully
6. **Check**: URL format is `/storage/customer_insurances/{id}/policy.pdf`

### Test 2: Direct Storage URL Access

1. Copy a file path from database:
   ```sql
   SELECT policy_document_path FROM customer_insurances WHERE policy_document_path IS NOT NULL LIMIT 1;
   ```

2. Access directly via browser:
   ```
   http://demo.midastech.testing.in:8085/storage/{path}
   ```

3. **Expected**: File displays/downloads correctly

### Test 3: Invalid Path Access

1. Try accessing non-existent file:
   ```
   http://demo.midastech.testing.in:8085/storage/fake/path/file.pdf
   ```

2. **Expected**: 404 error "File not found"

### Test 4: Path Traversal Attack

1. Try accessing outside tenant storage:
   ```
   http://demo.midastech.testing.in:8085/storage/../tenant999/app/public/secret.pdf
   ```

2. **Expected**: 404 error (FilesystemTenancyBootstrapper blocks this)

---

## SQL Queries for Verification

### Count Files by Type

```sql
-- Customer insurance documents
SELECT
    COUNT(*) as total_policies,
    SUM(CASE WHEN policy_document_path IS NOT NULL THEN 1 ELSE 0 END) as with_documents
FROM customer_insurances;

-- Lead documents
SELECT
    COUNT(*) as total_documents,
    COUNT(DISTINCT lead_id) as leads_with_docs
FROM lead_documents;
```

### Find Sample Files to Test

```sql
-- Get 5 sample policy documents
SELECT id, policy_no, policy_document_path
FROM customer_insurances
WHERE policy_document_path IS NOT NULL
LIMIT 5;

-- Get 5 sample lead documents
SELECT id, lead_id, document_name, file_path
FROM lead_documents
LIMIT 5;
```

---

## Common Issues & Solutions

### Issue 1: 404 on Storage Route

**Symptom**: `/storage/*` returns 404

**Check**:
```bash
php artisan route:list | grep storage
```

**Solution**:
```bash
php artisan route:clear
php artisan config:clear
php artisan optimize:clear
```

### Issue 2: File Not Found (But Exists)

**Symptom**: File exists in filesystem but returns 404

**Check**:
```php
// In tinker
Storage::disk('public')->path('test.txt');
// Should show: storage/tenant{id}/app/public/test.txt
```

**Solution**: Verify FilesystemTenancyBootstrapper is enabled in `config/tenancy.php`

### Issue 3: Wrong Tenant Files Showing

**Symptom**: Seeing files from different tenant

**Check**:
```php
tenant()->id  // Should match current subdomain
```

**Solution**: Clear tenant identification cache, restart queue workers

### Issue 4: Permission Denied

**Symptom**: Files upload but can't be accessed

**Check**:
```bash
# Windows (Run as Administrator)
icacls "storage\tenant*" /grant "IIS_IUSRS:(OI)(CI)F" /T

# Linux
chmod -R 775 storage/tenant*/
chown -R www-data:www-data storage/tenant*/
```

---

## Security Checklist

- [ ] Tenant storage route has `tenant` middleware
- [ ] FilesystemTenancyBootstrapper is enabled
- [ ] Path traversal attempts are blocked
- [ ] File MIME types are validated
- [ ] Only allowed file extensions accepted
- [ ] No central storage files accessible via tenant route
- [ ] Tenant A cannot access Tenant B files
- [ ] Suspended tenants cannot upload (but can view existing)

---

## Performance Checks

### File Serving Speed

```bash
# Test file serving performance
curl -o /dev/null -s -w "Time: %{time_total}s\n" \
  "http://demo.midastech.testing.in:8085/storage/test.pdf"
```

**Expected**: < 1 second for files under 5MB

### Storage Disk Usage

```bash
# Check tenant storage size
du -sh storage/tenant*/
```

### Database vs Filesystem Sync

```php
// Check for orphaned database records
$insurances = \App\Models\CustomerInsurance::whereNotNull('policy_document_path')->get();
$missing = 0;

foreach ($insurances as $insurance) {
    if (!Storage::disk('public')->exists($insurance->policy_document_path)) {
        $missing++;
        echo "Missing: " . $insurance->policy_document_path . "\n";
    }
}

echo "Total missing files: " . $missing . "\n";
```

---

## Test Results Template

```
Date: 2025-11-04
Tester: [Name]
Tenant: [demo.midastech.testing.in]

✅ PASS | ❌ FAIL | ⚠️ WARNING

[ ] Tenant storage route exists
[ ] File upload works
[ ] File download works
[ ] Policy documents accessible
[ ] Lead documents accessible
[ ] Tenant isolation maintained
[ ] Path traversal blocked
[ ] Invalid paths return 404
[ ] MIME types correct
[ ] File permissions correct
[ ] Performance acceptable (<1s)
[ ] No orphaned database records
```

---

## Automated Test Script

Save as `scripts/test-file-accessibility.php`:

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test tenant file accessibility
echo "=== File Accessibility Test ===\n\n";

// Test 1: Route exists
echo "1. Checking storage route...\n";
$routes = Route::getRoutes();
$storageRoute = $routes->getByName('tenant.storage');
echo $storageRoute ? "✅ Route exists\n" : "❌ Route missing\n";
echo "\n";

// Test 2: Upload test file
echo "2. Testing file upload...\n";
$testPath = 'accessibility_test/' . time() . '.txt';
Storage::disk('public')->put($testPath, 'Test content');
echo Storage::disk('public')->exists($testPath) ? "✅ Upload works\n" : "❌ Upload failed\n";

// Test 3: Read file
echo "3. Testing file read...\n";
$content = Storage::disk('public')->get($testPath);
echo ($content === 'Test content') ? "✅ Read works\n" : "❌ Read failed\n";

// Test 4: Cleanup
Storage::disk('public')->delete($testPath);
echo Storage::disk('public')->exists($testPath) ? "❌ Cleanup failed\n" : "✅ Cleanup works\n";

echo "\n=== Tests Complete ===\n";
```

Run with: `php scripts/test-file-accessibility.php`

---

## Conclusion

**File accessibility status**:
- ✅ Tenant storage route exists
- ✅ FilesystemTenancyBootstrapper configured
- ⏳ Manual testing required (see steps above)

**Next Steps**:
1. Run manual browser tests
2. Execute SQL queries to find sample files
3. Test actual file downloads
4. Verify tenant isolation
5. Check performance metrics

---

**Last Updated**: 2025-11-04
**Status**: Documentation Complete, Manual Testing Required
