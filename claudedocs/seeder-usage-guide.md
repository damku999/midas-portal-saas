# Notification Templates Seeder - Usage Guide

**File**: `database/seeders/NotificationTemplatesSeeder.php`
**Status**: ✅ Idempotent (Safe to run multiple times)

---

## ✅ Safe to Run Anytime

The seeder is now **idempotent** - it will **NOT create duplicates**.

### How It Works:

```
For each template:
  ├─ Check if template exists (notification_type_id + channel)
  ├─ If exists → UPDATE template content
  └─ If not exists → CREATE new template
```

---

## 🚀 Usage

### Basic Usage (Safe - No Duplicates)

```bash
# Run the seeder (safe to run multiple times)
php artisan db:seed --class=NotificationTemplatesSeeder
```

**Output Example**:
```
🌱 Seeding notification templates...
Processing 20 templates...

  + Created: policy_created (email)
  + Created: renewal_30_days (email)
  ✓ Updated: policy_created (whatsapp)
  ✓ Updated: customer_welcome (whatsapp)
  + Created: renewal_15_days (email)
  ...

✅ Seeding completed successfully!
+--------------------+-------+
| Action             | Count |
+--------------------+-------+
| Templates Created  | 6     |
| Templates Updated  | 14    |
| Total Processed    | 20    |
+--------------------+-------+
```

---

## 📊 What Happens When You Run It

### First Time Running:
- **Creates** all 20 templates (14 WhatsApp + 6 Email)
- Shows "Created" for each new template
- Output: `Templates Created: 20, Templates Updated: 0`

### Second Time Running (No Changes):
- **Updates** all existing templates with same content
- Shows "Updated" for each template
- Output: `Templates Created: 0, Templates Updated: 20`
- **No duplicates created** ✅

### After Manual Edits via Admin Panel:
- **Overwrites** your manual changes with seeder content
- Shows "Updated" for templates
- ⚠️ **Warning**: Manual edits will be lost!

---

## ⚠️ Important Notes

### 1. Manual Edits Will Be Overwritten

If you edit templates via admin panel (`/notification-templates`), running the seeder again will **overwrite** your changes.

**Solution**:
```bash
# Don't run seeder after making manual edits
# Or modify the seeder file with your custom templates
```

### 2. Template Versioning

The seeder does **NOT** create version history entries. It only updates the main template record.

**Manual edits via admin panel** do create version history (recommended for production changes).

### 3. Active/Inactive Status

The seeder sets all templates to `is_active = true`. If you've deactivated templates manually, the seeder will **re-activate** them.

---

## 🎯 When to Run the Seeder

### ✅ Good Times to Run:

1. **Initial Setup** - First time deploying notification templates
2. **After Fresh Install** - Setting up new environment
3. **Testing Environment** - Resetting templates to default state
4. **Bug Fixes** - Fixing template syntax errors en masse
5. **New Template Additions** - Adding newly created templates from dev

### ❌ Avoid Running:

1. **Production After Customization** - Will overwrite custom templates
2. **Without Backup** - Always backup database first
3. **During Active Campaigns** - May disrupt ongoing notifications

---

## 🔄 Idempotency Verification

### Test the Seeder (Safe):

```bash
# Run seeder first time
php artisan db:seed --class=NotificationTemplatesSeeder

# Check count
php artisan tinker
>>> App\Models\NotificationTemplate::count();
// Output: 20

# Run seeder AGAIN
php artisan db:seed --class=NotificationTemplatesSeeder

# Check count again (should be same)
>>> App\Models\NotificationTemplate::count();
// Output: 20 (NOT 40!) ✅
```

---

## 🛡️ Safety Features

### 1. Duplicate Prevention

```php
// Checks for existing template before inserting
$existing = DB::table('notification_templates')
    ->where('notification_type_id', $template['notification_type_id'])
    ->where('channel', $template['channel'])
    ->first();

if ($existing) {
    // UPDATE (no duplicate created)
    DB::table('notification_templates')->where('id', $existing->id)->update(...);
} else {
    // INSERT (new template)
    DB::table('notification_templates')->insert($template);
}
```

### 2. Progress Logging

Shows exactly what's happening:
- `+ Created` - New template added
- `✓ Updated` - Existing template refreshed
- Summary table with counts

### 3. Error Handling

If notification type doesn't exist, template is skipped (no error thrown).

---

## 📝 Backup Before Running (Production)

### Recommended Backup Process:

```bash
# 1. Backup notification_templates table
php artisan tinker
>>> DB::table('notification_templates')->get()->toJson();
// Copy output to file

# 2. Or use mysqldump
mysqldump -u username -p database_name notification_templates > backup_templates.sql

# 3. Run seeder
php artisan db:seed --class=NotificationTemplatesSeeder

# 4. If something goes wrong, restore from backup
mysql -u username -p database_name < backup_templates.sql
```

---

## 🔍 Verify After Running

### Quick Verification:

```bash
# 1. Count templates
php artisan tinker
>>> App\Models\NotificationTemplate::count();
// Expected: 20

# 2. Check by channel
>>> App\Models\NotificationTemplate::where('channel', 'whatsapp')->count();
// Expected: 14

>>> App\Models\NotificationTemplate::where('channel', 'email')->count();
// Expected: 6

# 3. Validate templates
php artisan notification:validate

# 4. Test templates
php artisan notification:test-templates
```

---

## 🐛 Troubleshooting

### Issue: "Call to undefined method info()"

**Error**:
```
Call to undefined method Illuminate\Database\Seeder::info()
```

**Cause**: Running seeder without Artisan command context

**Solution**: Always run via `php artisan db:seed --class=...`

---

### Issue: Templates Count is Wrong

**Symptoms**: Count shows 13 instead of 20

**Cause**: Some notification types are inactive

**Solution**:
```bash
# Check inactive notification types
php artisan tinker
>>> App\Models\NotificationType::where('is_active', false)->pluck('code');

# Activate if needed
>>> App\Models\NotificationType::where('code', 'claim_closed')->update(['is_active' => true]);

# Re-run seeder
php artisan db:seed --class=NotificationTemplatesSeeder
```

---

### Issue: Seeder Completes but Templates Not Updated

**Symptoms**: Seeder shows "Updated: 20" but templates haven't changed

**Cause**: Browser cache or database cache

**Solution**:
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Refresh browser hard (Ctrl+Shift+R)
```

---

## 📋 Checklist: Running Seeder Safely

### Pre-Run:
- [ ] ✅ Seeder is idempotent (updated version)
- [ ] Backup database (production only)
- [ ] Check notification types are active
- [ ] Review seeder file for any custom changes needed

### Run:
- [ ] Execute: `php artisan db:seed --class=NotificationTemplatesSeeder`
- [ ] Review output (Created vs Updated counts)
- [ ] Check for any warnings or errors

### Post-Run:
- [ ] Verify template count: `App\Models\NotificationTemplate::count()`
- [ ] Validate templates: `php artisan notification:validate`
- [ ] Test templates: `php artisan notification:test-templates`
- [ ] Clear caches: `php artisan config:clear && php artisan cache:clear`
- [ ] Test notification sending manually

---

## 🎯 Summary

### Key Points:

✅ **Safe to run multiple times** - No duplicates created
✅ **Updates existing templates** - Keeps database clean
✅ **Progress logging** - See what's being created/updated
✅ **Idempotent operation** - Same result every time

⚠️ **Caution**: Will overwrite manual template edits

### Quick Command:

```bash
# The only command you need (safe to run anytime)
php artisan db:seed --class=NotificationTemplatesSeeder
```

---

**Last Updated**: 2025-10-31
**Version**: Idempotent (No Duplicates) ✅
