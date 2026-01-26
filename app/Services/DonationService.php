<?php

namespace App\Services;

use App\Constant\MyConstant;
// use App\Jobs\DonationJob;
use App\Models\Donation;
use App\Models\Notification;
use App\Services\SupabaseStorageService;
use App\Services\SupabaseService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DonationService
{
    private $validator;

    public function __construct(useValidator $validator)
    {
        $this->validator = $validator;
    }

    public function store(Request $request)
    {
        try {
            // Handle File Upload
            $transactionIdUrl = null;

            if ($request->hasFile('transaction_id')) {
                $file = $request->file('transaction_id');
                
                // Validate file
                $validator = Validator::make($request->all(), [
                    'transaction_id' => 'file|mimes:jpg,jpeg,png,pdf|max:10240'
                ]);
                
                if ($validator->fails()) {
                    session()->flash('error', 'Invalid transaction proof file. Please ensure the file is JPG, JPEG, PNG, or PDF format and under 10MB in size.');
                    return [
                        'error_code' => MyConstant::FAILED_CODE,
                        'status_code' => MyConstant::BAD_REQUEST,
                        'message' => 'Invalid transaction proof file. Please ensure the file is JPG, JPEG, PNG, or PDF format and under 10MB in size.',
                    ];
                }
                
                // Upload to Supabase
                $storageService = new SupabaseStorageService();
                $bucket = env('SUPABASE_STORAGE_BUCKET_DONATIONS', 'donations');
                $result = $storageService->upload($file, $bucket);
                
                if ($result['success']) {
                    $transactionIdUrl = $result['url'];
                    Log::info('Donation transaction uploaded to Supabase', ['url' => $transactionIdUrl]);
                } else {
                    Log::error('Supabase upload failed for donation', ['error' => $result['error']]);
                    session()->flash('error', 'Failed to upload transaction proof to cloud storage. Please try again or contact support if the problem persists.');
                    return [
                        'error_code' => MyConstant::FAILED_CODE,
                        'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                        'message' => 'Failed to upload transaction proof to cloud storage. Please try again or contact support if the problem persists.',
                    ];
                }
            }
            
            // Save to Database with Supabase fallback
            try {
                $donation = Donation::create([
                    'user_id' => Auth::id(),
                    'donor_name' => $request->donor_name,
                    'donor_email' => $request->donor_email,
                    'donor_phone' => $request->donor_phone,
                    'donation_date' => $request->donation_date,
                    'amount' => $request->amount,
                    'note' => $request->note,
                    'transaction_id' => $transactionIdUrl,
                    'status' => 'Pending',
                ]);
            } catch (QueryException $dbError) {
                Log::warning('Database insert failed, trying Supabase fallback', ['error' => $dbError->getMessage()]);
                
                // Supabase fallback
                try {
                    $supabaseService = new SupabaseService();
                    // Only use columns that exist in Supabase table
                    $donationData = [
                        'user_id' => Auth::id(),
                        'donor_name' => $request->donor_name,
                        'donor_email' => $request->donor_email,
                        'donor_phone' => $request->donor_phone,
                        'donation_date' => $request->donation_date,
                        'amount' => $request->amount,
                        'transaction_url' => $transactionIdUrl,
                        'created_at' => now()->toISOString(),
                        'updated_at' => now()->toISOString(),
                    ];
                    
                    $result = $supabaseService->insert('tdonations', $donationData);
                    
                    // Log the transaction_id separately since Supabase table doesn't have this column
                    if ($transactionIdUrl) {
                        Log::info('Donation saved to Supabase, transaction proof URL: ' . $transactionIdUrl);
                    }
                    if (!$result) {
                        throw new \Exception('Supabase insert failed');
                    }
                    Log::info('Donation saved via Supabase fallback');
                } catch (\Exception $supabaseError) {
                    Log::error('Both database and Supabase failed', ['error' => $supabaseError->getMessage()]);
                    session()->flash('error', 'Failed to save donation');
                    return [
                        'error_code' => MyConstant::FAILED_CODE,
                        'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                        'message' => 'Failed to save donation',
                    ];
                }
            }

            try {
                // Notification for admin
                Notification::create([
                    'type' => 'Donation',
                    'message' => 'A donation was received by ' . $request->donor_name,
                    'user_id' => null, // Admin sees all
                ]);

                // Notification for parishioner who made the donation
                if (Auth::check()) {
                    Notification::create([
                        'type' => 'Donation',
                        'message' => 'Your donation was received successfully. Thank you!',
                        'user_id' => Auth::id(),
                    ]);
                }
            } catch (QueryException $notifError) {
                Log::warning('Notification creation failed', ['error' => $notifError->getMessage()]);
                // Continue even if notification fails
            }

            session()->flash('success', 'Donation created successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Donation created successfully',
            ];
        } catch (QueryException $e) {
            Log::error('Donation store error', ['error' => $e->getMessage()]);
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error',
            ];
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->validator->donationValidator());

        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }

        try {
            $donations = Donation::find($id);
            $donations->update([
                'donor_name' => $request->donor_name,
                'donor_email' => $request->donor_email,
                'donor_phone' => $request->donor_phone,
                'amount' => $request->amount,
                'note' => $request->note,
                // 'transaction_id' => $request->transaction_id,
                'status' => $request->status,
            ]);

            if (!$donations) {
                return [
                    'error_code' => MyConstant::FAILED_CODE,
                    'status_code' => MyConstant::NOT_FOUND,
                    'message' => 'Donation not found',
                ];
            }

            $data = $validator->validated();
            $donations->update($data);

            session()->flash('success', 'Donation updated successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Donation updated successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error',
            ];
        }
    }

    public function destroy($id)
    {
        try {
            $donations = Donation::find($id);

            if (!$donations) {
                return [
                    'error_code' => MyConstant::FAILED_CODE,
                    'status_code' => MyConstant::NOT_FOUND,
                    'message' => 'Donation not found',
                ];
            }

            $donations->delete();

            // Notification for admin only (deletion action)
            Notification::create([
                'type' => 'Donation',
                'message' => 'A donation has been deleted by ' . Auth::user()->name,
                'user_id' => null,
            ]);

            session()->flash('success', 'Donation deleted successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Donation deleted successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error',
            ];
        }
    }
}