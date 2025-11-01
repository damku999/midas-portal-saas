# Inertia to Blade Views Fix

> 📝 **TECHNICAL NOTE** - Frontend stack decision reference
>
> This document describes the resolution of Inertia.js to Blade template migration issues.
> Keep for reference when understanding frontend architecture decisions or troubleshooting view-related issues.

---

**Date**: 2025-11-02
**Issue**: Lead Management Module controllers were using Inertia.js instead of Blade views

---

## Problem

The Lead Management Module was implemented with Inertia.js rendering (`Inertia::render()`), but this project uses traditional Blade views (`view()`).

**Error**:
```
Class "Inertia\Inertia" not found
C:\xampp\htdocs\webmonks\midas-portal\app\Http\Controllers\LeadController.php:47
```

---

## Root Cause

Controllers were created following modern Laravel + Inertia.js patterns, but the Midas Portal project:
- Uses Blade templates (traditional server-side rendering)
- Does not have Inertia.js installed
- All other modules (Customers, Claims, etc.) use Blade views

---

## Solution Applied

### 1. Fixed LeadController.php ✅

**Removed**:
```php
use Inertia\Inertia;
```

**Changed** (5 methods):
```php
// Before
return Inertia::render('Leads/Index', [...]);
return Inertia::render('Leads/Create', [...]);
return Inertia::render('Leads/Show', [...]);
return Inertia::render('Leads/Edit', [...]);

// After
return view('leads.index', [...]);
return view('leads.create', [...]);
return view('leads.show', [...]);
return view('leads.edit', [...]);
```

**Also Fixed User Query**:
```php
// Before (incorrect column name)
User::where('is_active', true)

// After (correct column name from users table)
User::where('status', true)
```

**Also Fixed RelationshipManager & ReferenceUser Query**:
```php
// Before
RelationshipManager::where('is_active', true)
ReferenceUser::where('is_active', true)

// After
RelationshipManager::where('status', true)
ReferenceUser::where('status', true)
```

---

### 2. Fixed LeadDashboardController.php ✅

**Removed**:
```php
use Inertia\Inertia;
```

**Changed**:
```php
// Before
return Inertia::render('Leads/Dashboard', [...]);

// After
return view('leads.dashboard', [...]);
```

---

### 3. Temporarily Disabled Lead Master Data Links ✅

**File**: `resources/views/common/sidebar.blade.php`

**Commented out** Lead Sources and Lead Statuses links until controllers are created:
```blade
{{-- TODO: Implement Lead Source and Status Controllers --}}
{{-- <a href="{{ route('lead-sources.index') }}">Lead Sources</a> --}}
{{-- <a href="{{ route('lead-statuses.index') }}">Lead Statuses</a> --}}
```

**Error Fixed**:
```
Route [lead-sources.index] not defined.
```

---

## Expected Blade View Structure

Based on the project's pattern (e.g., `resources/views/customers/`), the Lead module views should be created at:

```
resources/views/leads/
├── index.blade.php       (Lead list page)
├── create.blade.php      (Create lead form)
├── edit.blade.php        (Edit lead form)
├── show.blade.php        (Lead details page)
└── dashboard.blade.php   (Lead analytics dashboard)
```

---

## View Data Structure

### leads.index
```php
[
    'leads' => PaginatedCollection,  // Lead list with pagination
    'filters' => array,               // Current filter values
    'sources' => Collection,          // All active lead sources
    'statuses' => Collection,         // All active lead statuses
    'users' => Collection,            // Active users for assignment
]
```

### leads.create
```php
[
    'sources' => Collection,              // Lead sources dropdown
    'statuses' => Collection,             // Lead statuses dropdown
    'users' => Collection,                // Users for assignment
    'relationshipManagers' => Collection, // RM dropdown
    'referenceUsers' => Collection,       // Reference users dropdown
]
```

### leads.show
```php
[
    'lead' => Lead,         // Lead model with all relationships loaded
    'statuses' => Collection, // For status change dropdown
]
```

### leads.edit
```php
[
    'lead' => Lead,                       // Existing lead data
    'sources' => Collection,              // Lead sources dropdown
    'statuses' => Collection,             // Lead statuses dropdown
    'users' => Collection,                // Users for assignment
    'relationshipManagers' => Collection, // RM dropdown
    'referenceUsers' => Collection,       // Reference users dropdown
]
```

### leads.dashboard
```php
[
    'statistics' => array,          // Overview stats (total, active, converted, etc.)
    'myLeads' => int,               // User's assigned leads count
    'myFollowUps' => int,           // User's follow-ups due count
    'myOverdue' => int,             // User's overdue follow-ups count
    'recentActivities' => Collection, // Last 10 activities
    'upcomingActivities' => Collection, // Next 5 scheduled activities
]
```

---

## Next Steps (Frontend Implementation Required)

### 1. Create Blade Views

You need to create 5 Blade template files:

**Option A: Copy from existing module** (Recommended)
```bash
# Copy customer views as template
cp resources/views/customers/index.blade.php resources/views/leads/index.blade.php
cp resources/views/customers/add.blade.php resources/views/leads/create.blade.php
# ... etc
```

**Option B: Create from scratch**
Follow the same structure as `customers/index.blade.php`, `claims/index.blade.php`, etc.

### 2. View Requirements

Each view should include:
- **Layout**: Extend the main layout (`@extends('layouts.app')`)
- **Breadcrumbs**: Navigation breadcrumbs
- **Success/Error Messages**: Flash message handling
- **Form**: Proper form with CSRF token
- **DataTables** (for index): jQuery DataTables for listing
- **Modals** (if needed): For quick actions

### 3. Example View Template

```blade
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Leads</h3>
            <div class="card-tools">
                <a href="{{ route('leads.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Lead
                </a>
            </div>
        </div>
        <div class="card-body">
            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Lead Table --}}
            <table id="leads-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Lead #</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                    <tr>
                        <td>{{ $lead->lead_number }}</td>
                        <td>{{ $lead->name }}</td>
                        <td>{{ $lead->mobile_number }}</td>
                        <td>{{ $lead->source->name }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $lead->status->color }}">
                                {{ $lead->status->name }}
                            </span>
                        </td>
                        <td>{{ $lead->assignedUser->first_name ?? 'Unassigned' }}</td>
                        <td>
                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            {{ $leads->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#leads-table').DataTable({
            responsive: true,
            autoWidth: false,
        });
    });
</script>
@endsection
```

---

## Files Modified

1. `app/Http/Controllers/LeadController.php`
   - Removed Inertia import
   - Changed 5 `Inertia::render()` calls to `view()`
   - Fixed user status column name

2. `app/Http/Controllers/LeadDashboardController.php`
   - Removed Inertia import
   - Changed `Inertia::render()` to `view()`

3. `resources/views/common/sidebar.blade.php`
   - Commented out Lead Sources link
   - Commented out Lead Statuses link
   - Added TODO comment for future implementation

---

## Testing Commands

```bash
# Clear cache to ensure changes take effect
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Test routes
php artisan route:list --name=leads

# Verify no Inertia references remain
grep -r "Inertia::" app/Http/Controllers/Lead*
# Should return nothing
```

---

## Status

**Backend**: ✅ **FIXED** - Controllers now use Blade views
**Frontend**: ⚠️ **PENDING** - Blade view files need to be created

---

## Summary

The Lead Management Module is now properly configured to use Blade templates matching the rest of the Midas Portal project. All Inertia.js references have been removed. The next step is to create the actual Blade view files in `resources/views/leads/` following the project's existing patterns.

---

**Last Updated**: 2025-11-02
**Version**: 1.0
