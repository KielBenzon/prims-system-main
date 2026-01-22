# Notification System Redesign - Implementation Guide

## Overview
This document explains the notification system changes and how to properly create notifications for different user roles.

## Database Changes

### Migration: `2026_01_23_000000_add_user_id_to_notifications_table.php`

**Changes Made:**
1. Added `user_id` foreign key - Links notification to specific user
2. Changed `is_read` boolean to `read_at` timestamp - Better tracking of when notification was read
3. Foreign key cascades on delete - If user deleted, their notifications are too

**To Run:**
```bash
php artisan migrate
```

## Model Updates

### `app/Models/Notification.php`
**New Methods:**
- `markAsRead()` - Marks notification as read with timestamp
- `isUnread()` - Check if notification is unread
- `user()` relationship - Get the user who owns the notification

## Controller Updates

### `app/Http/Controllers/NotificationController.php`

**New Logic:**

**For Admin:**
- Sees ALL notifications from ALL users
- Types: Request, Donation, Payment, Document uploads, etc.

**For Parishioner:**
- Sees ONLY their own notifications (filtered by `user_id`)
- Types: Request status changes, Donation approvals, Announcements

**New Routes Needed:**
```php
// Add to routes/web.php
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
```

## How to Create Notifications

### For Admin (when parishioner does something):
```php
use App\Models\Notification;
use App\Models\User;

// When parishioner creates a request
Notification::create([
    'user_id' => null, // null means for all admins
    'type' => 'Request',
    'message' => 'New request created by ' . $parishioner->name,
    'read_at' => null
]);
```

### For Parishioner (when admin updates their stuff):
```php
use App\Models\Notification;

// When admin approves a request
Notification::create([
    'user_id' => $request->user_id, // Specific parishioner
    'type' => 'Request',
    'message' => 'Your request has been approved',
    'read_at' => null
]);

// When admin approves a donation
Notification::create([
    'user_id' => $donation->user_id, // Specific parishioner
    'type' => 'Donation',
    'message' => 'Your donation has been received',
    'read_at' => null
]);
```

## UI Changes Needed in navbar.blade.php

### Current Issues:
1. Shows all notifications at once
2. Red dot doesn't disappear when opening
3. No limit on display

### Required Changes:
```blade
<!-- Show only 3 initially -->
@forelse ($notifications->take(3) as $notification)
    <!-- notification item -->
@endforelse

<!-- View All button shows 7-8 more, then scrollable -->
<li class="p-2 text-center border-t">
    <a href="javascript:void(0)"
       id="viewAllNotifications"
       class="text-sm text-blue-500 hover:underline"
       onclick="markNotificationsAsRead()">
        View all notifications
    </a>
</li>
```

### JavaScript to add:
```javascript
// Mark as read when notification panel opens
document.querySelector('.dropdown').addEventListener('click', function() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(() => {
        // Hide red dot
        document.getElementById('notification-dot').style.display = 'none';
    });
});

// View All functionality
function markNotificationsAsRead() {
    // Show modal with all notifications (max 7-8, then scrollable)
    // Implementation depends on your modal structure
}
```

## Notification Types by Role

### Admin Notifications (from parishioners):
- `Request` - New certificate request created
- `Request` - Certificate request edited
- `Payment` - Payment submitted
- `Donation` - New donation made

### Parishioner Notifications (from admin):
- `Request` - Request status changed to "Approved"
- `Request` - Request status changed to "Completed"
- `Request` - Request status changed to "Declined"
- `Donation` - Donation status changed to "Received"
- `Payment` - Payment verified
- `Announcement` - New announcement posted

## Example: Complete Request Approval Flow

```php
// In RequestController@approve method
public function approve($id)
{
    $request = Request::findOrFail($id);
    $request->status = 'Approved';
    $request->save();

    // Create notification for the parishioner
    Notification::create([
        'user_id' => $request->user_id,
        'type' => 'Request',
        'message' => 'Your ' . $request->document_type . ' request has been approved',
        'read_at' => null
    ]);

    return redirect()->back()->with('success', 'Request approved and parishioner notified');
}
```

## Example: Complete Donation Submission Flow

```php
// In DonationController@store method (parishioner submits donation)
public function store(Request $request)
{
    $donation = Donation::create([
        'user_id' => Auth::id(),
        'donor_name' => $request->donor_name,
        'amount' => $request->amount,
        'status' => 'Pending',
        // ... other fields
    ]);

    // Notify ALL admins about new donation
    $admins = User::where('role', 'Admin')->get();
    foreach ($admins as $admin) {
        Notification::create([
            'user_id' => null, // or $admin->id if you want individual tracking
            'type' => 'Donation',
            'message' => 'New donation of ₱' . number_format($request->amount, 2) . ' by ' . Auth::user()->name,
            'read_at' => null
        ]);
    }

    return redirect()->back()->with('success', 'Donation submitted');
}

// In DonationController@updateStatus method (admin approves)
public function updateStatus($id)
{
    $donation = Donation::findOrFail($id);
    $donation->status = 'Received';
    $donation->save();

    // Notify the parishioner who made the donation
    Notification::create([
        'user_id' => $donation->user_id,
        'type' => 'Donation',
        'message' => 'Your donation of ₱' . number_format($donation->amount, 2) . ' has been received. Thank you!',
        'read_at' => null
    ]);

    return redirect()->back()->with('success', 'Donation marked as received');
}
```

## Testing Checklist

1. ✅ Run migration
2. ✅ Test Admin login - should see notifications from all parishioners
3. ✅ Test Parishioner login - should only see their own notifications
4. ✅ Create new request as parishioner - admin should be notified
5. ✅ Approve request as admin - parishioner should be notified
6. ✅ Test red dot disappears when opening notifications
7. ✅ Test "View All" shows limited notifications with scroll
8. ✅ Test clicking outside notification panel closes it

## Important Notes

- **Always set `user_id`** when creating notifications for parishioners
- **Set `user_id` to null** when creating notifications for admins (or create one per admin)
- **Use proper notification types** for filtering
- **Mark as read** is automatic when user opens notification panel
- **Old notifications** without user_id will still work for admins (they see all)

## Next Steps

1. Update navbar.blade.php with new UI (3 notifications, view all, scrollable)
2. Add routes for markAsRead and markAllAsRead
3. Update all controllers that create notifications to use user_id
4. Test thoroughly with both admin and parishioner accounts
