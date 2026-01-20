<?php

namespace App\Http\Controllers;

use App\Constant\MyConstant;
use App\Models\Notification;
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

    private function getUserNotifications($user)
    {
        $query = Notification::query();

        if ($user->role === 'Parishioner') {
            $query->whereIn('type', ['Request', 'Donation', 'Announcement', 'Approved']);
        }

        return $query->get()->map(function ($notification) use ($user) {
            $notification->message .= ' by ' . $user->name;
            return $notification;
        });
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

    // public function updateRecordStatus($recordId)
    // {
    //     $record = Record::findOrFail($recordId);

    //     if ($record->status !== 'approved') {
    //         $record->status = 'approved';
    //         $record->save();

    //         // Trigger notification to the record owner
    //         $record->user->notify(new RecordApprovedNotification($record, Auth::user()->name));
    //     }

    //     return redirect()->back()->with('success', 'Record status updated to approved and notification sent.');
    // }
}
