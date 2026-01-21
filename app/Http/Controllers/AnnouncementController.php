<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\Priest;
use App\Services\AnnouncementService;
use App\Services\useValidator;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Try fetching announcements with Supabase fallback
        $announcements = collect([]);
        try {
            $announcements = Announcement::with('priest')->get();
        } catch (\Exception $e) {
            // Fallback to Supabase REST API
            try {
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get($supabaseUrl . '/rest/v1/tannouncements?select=*,priest:tpriests(*)&order=created_at.desc');
                
                if ($response->successful()) {
                    $announcements = collect($response->json())->map(function ($announcement) {
                        $obj = (object) $announcement;
                        // Convert priest array to object
                        if (isset($announcement['priest'])) {
                            $obj->priest = (object) $announcement['priest'];
                        }
                        return $obj;
                    });
                }
            } catch (\Exception $apiException) {
                // Silently fail and return empty collection
            }
        }
        
        // Try fetching priests with Supabase fallback
        $priests = collect([]);
        try {
            $priests = Priest::all();
        } catch (\Exception $e) {
            // Fallback to Supabase REST API
            try {
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get($supabaseUrl . '/rest/v1/tpriests?select=*');
                
                if ($response->successful()) {
                    $priests = collect($response->json())->map(function ($priest) {
                        return (object) $priest;
                    });
                }
            } catch (\Exception $apiException) {
                // Silently fail and return empty collection
            }
        }
        
        return view('admin.announcement', compact('announcements', 'priests'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $result = (new AnnouncementService(new useValidator))
            ->store(new AnnouncementRequest($request->all()));

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $result = (new AnnouncementService(new useValidator))
            ->update(new AnnouncementRequest($request->all()), $id);

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = (new AnnouncementService(new useValidator))
            ->destroy($id);

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }
}
