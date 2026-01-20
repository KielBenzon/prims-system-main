<?php

namespace App\Http\Controllers;

use App\Constant\MyConstant;
use App\Models\Priest;
use App\Services\PriestService;
use App\Services\useValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PriestController extends Controller
{
    public function index()
    {
        $search = request('search');
        
        // Try local database first
        try {
            $priests = Priest::query()
                ->when($search, function ($query, $search) {
                    return $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('middle_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('title', 'like', '%' . $search . '%')
                        ->orWhere('date_of_birth', 'like', '%' . $search . '%')
                        ->orWhere('phone_number', 'like', '%' . $search . '%')
                        ->orWhere('email_address', 'like', '%' . $search . '%')
                        ->orWhere('ordination_date', 'like', '%' . $search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } catch (\Exception $e) {
            // Fallback to Supabase REST API
            Log::warning('Local priests query failed, using Supabase API: ' . $e->getMessage());
            $priests = $this->getPriestsFromSupabase($search);
        }
        
        return view('admin.priest', compact('priests'));
    }
    
    private function getPriestsFromSupabase($search = null)
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
        
        $query = "{$supabaseUrl}/rest/v1/tpriests?select=*&order=created_at.desc";
        
        if ($search) {
            $query .= "&or=(first_name.ilike.*{$search}*,middle_name.ilike.*{$search}*,last_name.ilike.*{$search}*,title.ilike.*{$search}*,email_address.ilike.*{$search}*)";
        }
        
        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
        ])->get($query);
        
        if ($response->successful()) {
            $data = collect($response->json())->map(function($item) {
                return (object) $item;
            });
            
            Log::info('Priests loaded from Supabase', ['count' => $data->count()]);
            
            // Create a simple paginator
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $data,
                $data->count(),
                10,
                1,
                ['path' => request()->url()]
            );
        }
        
        Log::error('Failed to load priests from Supabase', ['response' => $response->body()]);
        return new \Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 10, 1);
    }

    public function store(Request $request)
    {
        $result = (new PriestService(new useValidator))
            ->store($request);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }

    public function update(Request $request, $id)
    {
        $result = (new PriestService(new useValidator))
            ->update($request, $id);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }

    public function destroy($id)
    {
        $result = (new PriestService(new useValidator))
            ->destroy($id);

        if ($result['error_code'] !== MyConstant::SUCCESS_CODE) {
            return response()->json([
                'error_code' => $result['error_code'],
                'message' => $result['message'],
            ], $result['status_code']);
        }

        return redirect()->back()->with([
            'error_code' => $result['error_code'],
            'message' => $result['message'],
        ]);
    }
}