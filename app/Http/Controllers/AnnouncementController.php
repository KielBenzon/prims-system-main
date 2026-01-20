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
        $announcements = $this->executeWithFallback(function () {
            return Announcement::all();
        }, collect([]));
        
        $priests = $this->executeWithFallback(function () {
            return Priest::all();
        }, collect([]));
        
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
