# Confirmation Modal - Quick Reference Card

## Overview
Replace all JavaScript `confirm()` with Bootstrap 5 modals for professional confirmations.

---

## 1. Link Actions (GET requests)

### Use Case
Status toggles, navigation actions

### Code Template
```html
<a href="#"
   data-bs-toggle="modal"
   data-bs-target="#confirmationModal"
   data-title="Confirm Action"
   data-message="Are you sure you want to proceed?"
   data-confirm-text="Yes, Proceed"
   data-confirm-class="btn-danger"
   data-action-url="{{ route('your.route', $id) }}"
   data-method="GET">
   Action Text
</a>
```

### Example: Deactivate Branch
```html
<a href="#"
   data-bs-toggle="modal"
   data-bs-target="#confirmationModal"
   data-title="Confirm Deactivation"
   data-message="Are you sure you want to deactivate <strong>{{ $branch->name }}</strong>?"
   data-confirm-text="Yes, Deactivate"
   data-confirm-class="btn-warning"
   data-action-url="{{ route('branches.status', [$branch->id, 0]) }}"
   data-method="GET">
   <i class="fas fa-ban"></i> Deactivate
</a>
```

---

## 2. Form Actions (POST/DELETE)

### Use Case
Deletions, updates requiring CSRF protection

### Code Template
```html
<form action="{{ route('your.route', $id) }}"
      method="POST"
      data-confirm-submit="true"
      data-title="Confirm Deletion"
      data-message="Are you sure you want to delete this item?"
      data-confirm-text="Yes, Delete"
      data-confirm-class="btn-danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger">
        <i class="fas fa-trash"></i> Delete
    </button>
</form>
```

### Example: Delete Record
```html
<form action="{{ route('addon-covers.delete', $addon_cover->id) }}"
      method="POST"
      data-confirm-submit="true"
      data-title="Confirm Deletion"
      data-message="Are you sure you want to delete <strong>{{ $addon_cover->name }}</strong>?"
      data-confirm-text="Yes, Delete"
      data-confirm-class="btn-danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">
        <i class="fas fa-trash"></i>
    </button>
</form>
```

---

## 3. JavaScript Function Call

### Use Case
AJAX operations, custom callbacks

### Code Template
```javascript
showConfirmationModal({
    title: 'Confirm Action',
    message: 'Your message with <strong>HTML</strong> support',
    confirmText: 'Yes, Proceed',
    cancelText: 'Cancel',  // Optional, default: "Cancel"
    confirmClass: 'btn-danger',  // btn-danger, btn-warning, btn-success
    onConfirm: function() {
        // Your custom logic here
        console.log('User confirmed!');
    }
});
```

### Example: AJAX Delete
```javascript
function deleteItem(itemId, itemName) {
    showConfirmationModal({
        title: 'Confirm Deletion',
        message: `Delete <strong>${itemName}</strong>?`,
        confirmText: 'Yes, Delete',
        confirmClass: 'btn-danger',
        onConfirm: function() {
            $.ajax({
                url: `/api/items/${itemId}`,
                method: 'DELETE',
                success: function(response) {
                    show_notification('success', 'Item deleted');
                    location.reload();
                },
                error: function(xhr) {
                    show_notification('error', 'Failed to delete');
                }
            });
        }
    });
}
```

---

## Data Attributes Reference

| Attribute | Required | Default | Description |
|-----------|----------|---------|-------------|
| `data-bs-toggle` | Yes | - | Must be "modal" |
| `data-bs-target` | Yes | - | Must be "#confirmationModal" |
| `data-title` | No | "Confirm Action" | Modal title |
| `data-message` | No | Default message | Confirmation message (HTML supported) |
| `data-confirm-text` | No | "Confirm" | Confirm button text |
| `data-cancel-text` | No | "Cancel" | Cancel button text |
| `data-confirm-class` | No | "btn-danger" | Button style class |
| `data-action-url` | Yes* | - | URL to navigate/submit to |
| `data-method` | No | "GET" | HTTP method (GET/POST/DELETE) |
| `data-confirm-submit` | Yes** | - | For form submissions |

\* Required for link actions
\*\* Required for form submissions

---

## Button Styling Classes

| Class | Color | Use For |
|-------|-------|---------|
| `btn-danger` | Red | Deletions, destructive actions |
| `btn-warning` | Orange | Deactivations, warnings |
| `btn-success` | Green | Activations, confirmations |
| `btn-primary` | Blue | General actions |
| `btn-info` | Cyan | Information actions |

---

## Common Patterns

### Activate/Deactivate Toggle
```html
@if ($item->status == 1)
    <a href="#" data-bs-toggle="modal" data-bs-target="#confirmationModal"
       data-title="Confirm Deactivation"
       data-message="Deactivate <strong>{{ $item->name }}</strong>?"
       data-confirm-text="Yes, Deactivate"
       data-confirm-class="btn-warning"
       data-action-url="{{ route('items.status', [$item->id, 0]) }}"
       data-method="GET">
       <i class="fas fa-ban"></i> Deactivate
    </a>
@else
    <a href="#" data-bs-toggle="modal" data-bs-target="#confirmationModal"
       data-title="Confirm Activation"
       data-message="Activate <strong>{{ $item->name }}</strong>?"
       data-confirm-text="Yes, Activate"
       data-confirm-class="btn-success"
       data-action-url="{{ route('items.status', [$item->id, 1]) }}"
       data-method="GET">
       <i class="fas fa-check"></i> Activate
    </a>
@endif
```

### Delete with Permission Check
```html
@if (auth()->user()->hasPermissionTo('item-delete'))
    <form action="{{ route('items.destroy', $item->id) }}"
          method="POST"
          style="display: inline;"
          data-confirm-submit="true"
          data-title="Confirm Deletion"
          data-message="Delete <strong>{{ $item->name }}</strong>? Cannot be undone."
          data-confirm-text="Yes, Delete"
          data-confirm-class="btn-danger">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i>
        </button>
    </form>
@endif
```

---

## Tips & Best Practices

### 1. Message Formatting
✅ **Good**: Use HTML for emphasis
```html
data-message="Delete <strong>{{ $item->name }}</strong>? Cannot be undone."
```

❌ **Avoid**: Plain text without context
```html
data-message="Are you sure?"
```

### 2. Button Text
✅ **Good**: Action-specific text
```html
data-confirm-text="Yes, Delete"
data-confirm-text="Yes, Deactivate"
```

❌ **Avoid**: Generic text
```html
data-confirm-text="OK"
data-confirm-text="Yes"
```

### 3. Button Colors
- 🔴 **Red (btn-danger)**: Deletions, permanent actions
- 🟠 **Orange (btn-warning)**: Deactivations, temporary changes
- 🟢 **Green (btn-success)**: Activations, positive actions

### 4. Security
✅ **Always**: Include CSRF tokens for POST/DELETE
```html
@csrf
@method('DELETE')
```

✅ **Always**: Use forms for mutations
```html
<form method="POST">
```

❌ **Never**: Use GET for destructive actions

---

## Troubleshooting

### Modal Not Showing?
1. Check if confirmation-modal.blade.php is included in layout
2. Verify confirmation-modal.js is loaded
3. Check browser console for errors

### Form Not Submitting?
1. Ensure `data-confirm-submit="true"` is on the form tag
2. Check CSRF token is present: `@csrf`
3. Verify form action URL is correct

### Callback Not Working?
1. Ensure function is defined before showConfirmationModal() call
2. Check `onConfirm` is a function, not a string
3. Verify modal closes before callback executes

---

## Migration Checklist

When converting old confirm() dialogs:

- [ ] Replace `onclick="return confirm(...)"` with data attributes
- [ ] Change href to `href="#"` for modal triggers
- [ ] Add `data-bs-toggle="modal"` and `data-bs-target="#confirmationModal"`
- [ ] Add confirmation data attributes (title, message, etc.)
- [ ] For forms: Add `data-confirm-submit="true"`
- [ ] For forms: Change button type from `type="button"` to `type="submit"`
- [ ] Test the action to ensure it works correctly
- [ ] Verify CSRF token is included for POST/DELETE

---

## File Locations

- **Modal Component**: `resources/views/common/confirmation-modal.blade.php`
- **JavaScript Handler**: `public/js/confirmation-modal.js`
- **Layout Inclusion**: `resources/views/layouts/app.blade.php`

---

## Need Help?

Refer to the full documentation: `claudedocs/CONFIRMATION_MODAL_IMPLEMENTATION.md`
