# File Storage Multi-Tenancy Guide

## Overview

This application uses **Stancl Tenancy** package to automatically isolate file storage per tenant. All file uploads are automatically stored in tenant-specific directories.

## How It Works

### Automatic Storage Path Scoping

When a tenant is initialized, the `FilesystemTenancyBootstrapper` automatically modifies storage paths:

**Before Tenant Initialization:**
```php
storage_path() => storage/
Storage::disk('public')->path('') => storage/app/public/
```

**After Tenant Initialization:**
```php
storage_path() => storage/tenant{tenant-id}/
Storage::disk('public')->path('') => storage/tenant{tenant-id}/app/public/
```

### Directory Structure

```
storage/
├── app/                           # Central/shared storage (DON'T use for tenant data)
│   └── public/
│       ├── customer_insurances/   ⚠️  OLD: Legacy shared files (migrate to tenant storage)
│       ├── customers/             ⚠️  OLD: Legacy shared files (migrate to tenant storage)
│       └── lead-documents/        ⚠️  OLD: Legacy shared files (migrate to tenant storage)
│
└── tenant{tenant-id}/             # ✅ Tenant-specific storage (AUTOMATIC)
    └── app/
        ├── private/               # Private tenant files
        └── public/                # Public tenant files
            ├── app-settings/
            ├── customer_insurances/
            ├── customers/
            ├── lead-documents/
            ├── lead-whatsapp-attachments/
            └── marketing/
```

### Example: Tenant Storage Paths

For tenant ID: `2c57d255-1e54-4443-bc78-435694111bc4`

```
storage/tenant2c57d255-1e54-4443-bc78-435694111bc4/
└── app/
    └── public/
        ├── app-settings/
        ├── customer_insurances/
        ├── customers/
        ├── lead-documents/
        └── marketing/
```

## Using File Storage in Code

### ✅ Correct Way (Automatic Tenant Isolation)

```php
// In any tenant controller (routes with tenancy middleware)
use Illuminate\Support\Facades\Storage;

// Store file - automatically goes to tenant storage
$path = $request->file('document')->store('customer_insurances', 'public');
// Result: storage/tenant{id}/app/public/customer_insurances/filename.pdf

// Store with custom name
$path = $request->file('avatar')->storeAs('customers', $filename, 'public');
// Result: storage/tenant{id}/app/public/customers/avatar.jpg

// Read file
$contents = Storage::disk('public')->get($path);

// Delete file
Storage::disk('public')->delete($path);

// Check if file exists
if (Storage::disk('public')->exists($path)) {
    // File exists in current tenant's storage
}
```

### ❌ Wrong Way (Bypasses Tenant Isolation)

```php
// DON'T use direct paths - bypasses tenancy
$file->move(storage_path('app/public/customers'), $filename);  // ❌ WRONG

// DON'T use public_path() - bypasses tenancy
$file->move(public_path('uploads'), $filename);  // ❌ WRONG

// DON'T manually construct paths - bypasses tenancy
$path = storage_path('app/public/customers/' . $filename);  // ❌ WRONG
File::put($path, $contents);
```

## File Upload Best Practices

### 1. Always Use Storage Facade

```php
// Good
Storage::disk('public')->put($path, $contents);
Storage::disk('local')->put($path, $contents);

// Also good (uses default disk)
$request->file('upload')->store('folder', 'public');
```

### 2. Use Descriptive Folder Names

```php
'customers/{customer_id}'
'policies/{policy_id}/documents'
'claims/{claim_id}/attachments'
'quotations/{quotation_id}'
'leads/{lead_id}/documents'
```

### 3. Store Relative Paths in Database

```php
// ✅ Store relative path (works across tenants)
$insurance->document_path = $request->file('document')
    ->store('customer_insurances', 'public');
// Stored as: customer_insurances/filename.pdf

// ❌ Don't store absolute paths
$insurance->document_path = storage_path('tenant123/app/public/...'); // WRONG
```

## Tenant Deletion

When deleting a tenant with the "Delete Files" option checked:

**Deleted:**
```
storage/tenant{tenant-id}/          # Entire tenant storage directory
```

**Preserved:**
```
storage/app/                        # Central storage (unaffected)
storage/other-tenant-id/            # Other tenants (unaffected)
```

## Migration for Existing Files

If you have existing files in `storage/app/public/` from before multi-tenancy was implemented:

### Option 1: Leave Legacy Files (Recommended for Development)
- New uploads automatically use tenant storage
- Old files remain accessible but not tenant-isolated
- Clean up manually when convenient

### Option 2: Migrate to Tenant Storage
See `scripts/migrate_files_to_tenant_storage.php` for migration script.

## Configuration

File storage tenancy is configured in `config/tenancy.php`:

```php
'filesystem' => [
    'suffix_base' => 'tenant',              // Folder prefix
    'disks' => ['local', 'public'],         // Tenant-aware disks
    'suffix_storage_path' => true,          // Enable path suffixing
    'root_override' => [
        'local' => '%storage_path%/app/',
        'public' => '%storage_path%/app/public/',
    ],
],
```

## Troubleshooting

### Files Not Stored in Tenant Directory

**Check:**
1. Is tenancy initialized? (Are you on a tenant route with middleware?)
2. Using Storage facade? (Not direct file operations)
3. `FilesystemTenancyBootstrapper` enabled in config?

**Test:**
```php
// In tinker with tenant context
Storage::disk('public')->put('test.txt', 'test');
dd(Storage::disk('public')->path('test.txt'));
// Should show: storage/tenant{id}/app/public/test.txt
```

### Cannot Access Uploaded Files

**Check:**
1. File path stored correctly in database (relative, not absolute)?
2. Using same disk for retrieval as storage?
3. Tenant initialized when retrieving?

**Example:**
```php
// Store
$path = $file->store('documents', 'public');
$model->file_path = $path;  // e.g., "documents/abc123.pdf"

// Retrieve (in tenant context)
$url = Storage::disk('public')->url($model->file_path);
$contents = Storage::disk('public')->get($model->file_path);
```

## Summary

✅ **DO:**
- Use `Storage::disk()` facade for all file operations
- Store relative paths in database
- Use tenant middleware on routes that handle uploads
- Let automatic tenancy handle folder isolation

❌ **DON'T:**
- Use `storage_path()` or `public_path()` for tenant files
- Manually construct file paths
- Store absolute paths in database
- Use File facade or direct filesystem functions

---

**Last Updated:** 2025-11-03
**Author:** Multi-Tenancy File Storage Implementation Team
