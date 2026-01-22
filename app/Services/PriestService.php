<?php

namespace App\Services;

use App\Constant\MyConstant;
use App\Models\Notification;
use App\Models\Priest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PriestService
{
    private $validator;

    public function __construct($validator)
    {
        $this->validator = $validator;
    }

    /**
     * Store a new priest
     */
    public function store($request)
    {
        $validator = Validator::make($request->all(), $this->validator->priestValidator());

        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }

        try {
            // Manual email uniqueness check with Supabase fallback
            $email = $request->email_address;
            $emailExists = false;
            
            try {
                // Try local database first
                $emailExists = Priest::where('email_address', $email)->exists();
            } catch (\Exception $e) {
                // Fallback to Supabase REST API
                \Illuminate\Support\Facades\Log::warning('Local email check failed, using Supabase API: ' . $e->getMessage());
                
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get("{$supabaseUrl}/rest/v1/tpriests?email_address=eq.{$email}&select=id");
                
                if ($response->successful()) {
                    $emailExists = !empty($response->json());
                }
            }
            
            if ($emailExists) {
                session()->flash('error', 'Email address already exists');
                return [
                    'error_code' => MyConstant::FAILED_CODE,
                    'status_code' => MyConstant::BAD_REQUEST,
                    'message' => 'Email address already exists',
                ];
            }

            $data = $validator->validated();

            // Handle image upload to Supabase Storage
            if ($request->hasFile('image')) {
                $storageService = new SupabaseStorageService();
                $bucket = env('SUPABASE_STORAGE_BUCKET_PRIESTS', 'priests');
                $result = $storageService->upload($request->file('image'), $bucket);
                
                if ($result['success']) {
                    $data['image'] = $result['url'];
                    Log::info('Priest image uploaded to Supabase', ['url' => $result['url']]);
                } else {
                    Log::error('Supabase image upload failed', ['error' => $result['error']]);
                    session()->flash('error', 'Failed to upload image');
                    return [
                        'error_code' => MyConstant::FAILED_CODE,
                        'status_code' => MyConstant::BAD_REQUEST,
                        'message' => 'Failed to upload image',
                    ];
                }
            }

            // Try to save to local database first
            try {
                $priest = Priest::create($data);
                \Illuminate\Support\Facades\Log::info('Priest saved to local database');
            } catch (\Exception $dbError) {
                // Fallback to Supabase REST API
                \Illuminate\Support\Facades\Log::warning('Local DB insert failed, using Supabase API: ' . $dbError->getMessage());
                
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->post("{$supabaseUrl}/rest/v1/tpriests", $data);
                
                if (!$response->successful()) {
                    \Illuminate\Support\Facades\Log::error('Supabase API insert failed', ['response' => $response->body()]);
                    session()->flash('error', 'Failed to save priest');
                    return [
                        'error_code' => MyConstant::FAILED_CODE,
                        'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                        'message' => 'Failed to save priest',
                    ];
                }
                
                \Illuminate\Support\Facades\Log::info('Priest saved to Supabase via REST API');
            }

            // Create notification (with fallback)
            try {
                Notification::create([
                    'type' => 'Priest',
                    'message' => 'A new priest has been added by ' . Auth::user()->name,
                    'user_id' => null, // Admin only
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to create notification: ' . $e->getMessage());
            }

            session()->flash('success', 'Priest created successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Priest created successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing priest
     */
    public function update($request, $id)
    {
        $validator = Validator::make($request->all(), $this->validator->priestValidator());

        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }

        try {
            $priest = null;
            $oldImageUrl = null;
            
            // Try to find priest locally first
            try {
                $priest = Priest::find($id);
                if ($priest) {
                    $oldImageUrl = $priest->image;
                }
            } catch (\Exception $e) {
                Log::warning('Local priest lookup failed: ' . $e->getMessage());
                
                // Get from Supabase
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get("{$supabaseUrl}/rest/v1/tpriests?id=eq.{$id}");
                
                if ($response->successful() && !empty($response->json())) {
                    $oldImageUrl = $response->json()[0]['image'] ?? null;
                }
            }

            $data = $validator->validated();

            // Handle image update with Supabase Storage
            if ($request->hasFile('image')) {
                // Delete old image from Supabase Storage if it exists
                if ($oldImageUrl && str_contains($oldImageUrl, 'supabase.co/storage')) {
                    $storageService = new SupabaseStorageService();
                    $bucket = env('SUPABASE_STORAGE_BUCKET_PRIESTS', 'priests');
                    
                    preg_match('/\/priests\/(.+)$/', $oldImageUrl, $matches);
                    $oldFilePath = $matches[1] ?? null;
                    
                    if ($oldFilePath) {
                        $deleteResult = $storageService->delete($bucket, $oldFilePath);
                        if ($deleteResult['success']) {
                            Log::info('Old priest image deleted from Supabase', ['path' => $oldFilePath]);
                        }
                    }
                }
                
                // Upload new image to Supabase Storage
                $storageService = new SupabaseStorageService();
                $bucket = env('SUPABASE_STORAGE_BUCKET_PRIESTS', 'priests');
                $result = $storageService->upload($request->file('image'), $bucket);
                
                if ($result['success']) {
                    $data['image'] = $result['url'];
                    Log::info('Priest image updated in Supabase', ['url' => $result['url']]);
                } else {
                    Log::error('Supabase image upload failed', ['error' => $result['error']]);
                    session()->flash('error', 'Failed to upload image');
                    return [
                        'error_code' => MyConstant::FAILED_CODE,
                        'status_code' => MyConstant::BAD_REQUEST,
                        'message' => 'Failed to upload image',
                    ];
                }
            }

            // Try local update first
            if ($priest) {
                try {
                    $priest->update($data);
                    Log::info('Priest updated in local database');
                    session()->flash('success', 'Priest updated successfully');
                    return [
                        'error_code' => MyConstant::SUCCESS_CODE,
                        'status_code' => MyConstant::OK,
                        'message' => 'Priest updated successfully',
                    ];
                } catch (\Exception $dbError) {
                    Log::warning('Local DB update failed, using Supabase API: ' . $dbError->getMessage());
                }
            }
            
            // Fallback to Supabase REST API
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
            
            $response = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->patch("{$supabaseUrl}/rest/v1/tpriests?id=eq.{$id}", $data);
            
            if (!$response->successful()) {
                Log::error('Supabase API update failed', ['response' => $response->body()]);
                session()->flash('error', 'Failed to update priest');
                return [
                    'error_code' => MyConstant::FAILED_CODE,
                    'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to update priest',
                ];
            }
            
            Log::info('Priest updated in Supabase via REST API');
            session()->flash('success', 'Priest updated successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Priest updated successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a priest
     */
    public function destroy($id)
    {
        try {
            $priest = null;
            $imageUrl = null;
            
            // Try to find priest locally first
            try {
                $priest = Priest::find($id);
                if ($priest) {
                    $imageUrl = $priest->image;
                }
            } catch (\Exception $e) {
                Log::warning('Local priest lookup failed: ' . $e->getMessage());
                
                // Get from Supabase
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get("{$supabaseUrl}/rest/v1/tpriests?id=eq.{$id}");
                
                if ($response->successful() && !empty($response->json())) {
                    $imageUrl = $response->json()[0]['image'] ?? null;
                }
            }

            // Delete image from Supabase Storage if it exists
            if ($imageUrl && str_contains($imageUrl, 'supabase.co/storage')) {
                $storageService = new SupabaseStorageService();
                $bucket = env('SUPABASE_STORAGE_BUCKET_PRIESTS', 'priests');
                
                preg_match('/\/priests\/(.+)$/', $imageUrl, $matches);
                $filePath = $matches[1] ?? null;
                
                if ($filePath) {
                    $deleteResult = $storageService->delete($bucket, $filePath);
                    if ($deleteResult['success']) {
                        Log::info('Priest image deleted from Supabase Storage', ['path' => $filePath]);
                    } else {
                        Log::warning('Failed to delete priest image from Supabase Storage', ['path' => $filePath]);
                    }
                }
            }
            
            // Try local delete first
            if ($priest) {
                try {
                    $priest->delete();
                    Log::info('Priest deleted from local database');
                    session()->flash('success', 'Priest deleted successfully');
                    return [
                        'error_code' => MyConstant::SUCCESS_CODE,
                        'status_code' => MyConstant::OK,
                        'message' => 'Priest deleted successfully',
                    ];
                } catch (\Exception $dbError) {
                    Log::warning('Local DB delete failed, using Supabase API: ' . $dbError->getMessage());
                }
            }
            
            // Fallback to Supabase REST API
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
            
            $response = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->delete("{$supabaseUrl}/rest/v1/tpriests?id=eq.{$id}");
            
            if (!$response->successful()) {
                Log::error('Supabase API delete failed', ['response' => $response->body()]);
                return [
                    'error_code' => MyConstant::FAILED_CODE,
                    'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to delete priest',
                ];
            }
            
            Log::info('Priest deleted from Supabase via REST API');
            session()->flash('success', 'Priest deleted successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Priest deleted successfully',
            ];
        } catch (QueryException $e) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $e->getMessage(),
            ];
        }
    }
}
