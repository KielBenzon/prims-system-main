<?php

namespace App\Services;

use App\Constant\MyConstant;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class AnnouncementService
{
    private $validator;

    public function __construct(useValidator $validator)
    {
        $this->validator = $validator;
    }

    public function store(AnnouncementRequest $request)
{
    try {
        // Count existing announcements
        $announcementCount = Announcement::count();

        if ($announcementCount >= 5) {
            session()->flash('error', 'Maximum of 5 announcements allowed.');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => 'Maximum of 5 announcements allowed.',
            ];
        }

        // Create announcement
        $announcement = Announcement::create($request->all());

        // Create notification
        Notification::create([
            'type' => 'Announcement',
            'message' => 'A new announcement has been created by ' . Auth::user()->name,
            'is_read' => '0',
        ]);

        session()->flash('success', 'Announcement created successfully');
        return [
            'error_code' => MyConstant::SUCCESS_CODE,
            'status_code' => MyConstant::OK,
            'message' => 'Announcement created successfully',
        ];

    } catch (QueryException $e) {
        // Fallback to Supabase REST API
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');

            // Count existing announcements via Supabase
            $countResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->get($supabaseUrl . '/rest/v1/tannouncements?select=id');

            $announcementCount = count($countResponse->json() ?? []);

            if ($announcementCount >= 5) {
                session()->flash('error', 'Maximum of 5 announcements allowed.');
                return [
                    'error_code' => MyConstant::FAILED_CODE,
                    'status_code' => MyConstant::BAD_REQUEST,
                    'message' => 'Maximum of 5 announcements allowed.',
                ];
            }

            // Create announcement via Supabase
            $announcementResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->post($supabaseUrl . '/rest/v1/tannouncements', [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'assigned_priest' => $request->input('assigned_priest'),
                'created_at' => now('Asia/Manila')->toIso8601String(),
                'updated_at' => now('Asia/Manila')->toIso8601String(),
            ]);

            if (!$announcementResponse->successful()) {
                throw new \Exception('Failed to create announcement via API: ' . $announcementResponse->body());
            }

            // Create notification via Supabase
            \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json',
            ])->post($supabaseUrl . '/rest/v1/tnotifications', [
                'type' => 'Announcement',
                'message' => 'A new announcement has been created by ' . Auth::user()->name,
                'is_read' => '0',
                'created_at' => now('Asia/Manila')->toIso8601String(),
                'updated_at' => now('Asia/Manila')->toIso8601String(),
            ]);

            session()->flash('success', 'Announcement created successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Announcement created successfully',
            ];

        } catch (\Exception $apiException) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => $apiException->getMessage(),
            ];
        }
    }
}

    public function update(AnnouncementRequest $request, $id)
    {
        try {
            $announcement = Announcement::find($id);
            $announcement->update($request->all());

            Notification::where('type', 'Announcement')->update([
                'message' => 'An announcement has been updated by ' . Auth::user()->name,
            ]);

            session()->flash('success', 'Announcement updated successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Announcement updated successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function destroy($id)
    {
        try {
            $announcement = Announcement::find($id);
            $announcement->delete();

            session()->flash('success', 'Announcement deleted successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Announcement deleted successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ];
        }
    }
}
