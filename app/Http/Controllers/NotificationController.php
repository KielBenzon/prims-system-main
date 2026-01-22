<?php

namespace App\Http\Controllers;

use App\Constant\MyConstant;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\RecordApprovedNotification;
use App\Services\NotificationService;
use App\Services\useValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $this->getUserNotifications($user);

        return view('admin.notification', compact('notifications'));
    }

    /**
     * Get notifications for the current user based on their role
     */
    private function getUserNotifications($user)
    {
        $query = Notification::query();

        if ($user->role === 'Admin') {
            // Admin sees ALL notifications from all users
            // These include: requests made, edits, payments, donations
            $query->orderBy('created_at', 'desc');
        } else {
            // Parishioner sees ONLY their own notifications
            // These include: request status changes (approved/completed), donation approvals
            $query->where('user_id', $user->id)
                  ->whereIn('type', ['Request', 'Donation', 'Payment', 'Announcement'])
                  ->orderBy('created_at', 'desc');
        }

        return $query->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Ensure user can only mark their own notifications as read (unless admin)
        if (Auth::user()->role !== 'Admin' && $notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        if ($user->role === 'Admin') {
            Notification::whereNull('read_at')->update(['read_at' => now()]);
        } else {
            Notification::where('user_id', $user->id)
                       ->whereNull('read_at')
                       ->update(['read_at' => now()]);
        }
        
        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $notificationService = new NotificationService(new useValidator);
        $result = $notificationService->store($request);

        return $result['error_code'] !== MyConstant::SUCCESS_CODE
            ? response()->json($this->formatErrorResponse($result), $result['status_code'])
            : redirect()->back()->with($this->formatSuccessResponse($result));
    }

    private function formatErrorResponse($result)
    {
        return [
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ];
    }

    private function formatSuccessResponse($result)
    {
        return [
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ];
    }
}
