<?php

namespace App\Services;

use App\Constant\MyConstant;
use App\Jobs\BurialJob;
use App\Models\CertificateDetail;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Request as RequestModel;
use App\Services\useValidator;
use App\Services\SupabaseService;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Log;
use Exception;

class RequestService
{
    private $validator;

    public function __construct(useValidator $validator)
    {
        $this->validator = $validator;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validator->requestValidator());

        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }

        try {
            // Create the request with database error handling
            $createdRequest = null;
            try {
                $createdRequest = RequestModel::create([
                    'requested_by' => Auth::user()->id,
                    'document_type' => $request->document_type,
                    'status' => 'Pending',
                    'is_paid' => 'Unpaid',
                    'is_deleted' => '0',
                ]);
            } catch (Exception $e) {
                Log::error('Database error creating request: ' . $e->getMessage());
                
                // Try Supabase fallback
                try {
                    $supabaseService = new SupabaseService();
                    $requestData = [
                        'requested_by' => Auth::user()->id,
                        'document_type' => $request->document_type,
                        'status' => 'Pending',
                        'is_paid' => 'Unpaid',
                        'is_deleted' => false,
                    ];
                    
                    $result = $supabaseService->insert('trequests', $requestData);
                    if ($result) {
                        // Create a temporary RequestModel object
                        $createdRequest = new RequestModel();
                        $createdRequest->id = $result[0]['id'] ?? null;
                        $createdRequest->requested_by = $requestData['requested_by'];
                        $createdRequest->document_type = $requestData['document_type'];
                        $createdRequest->exists = true;
                    }
                } catch (Exception $supabaseException) {
                    Log::error('Supabase fallback also failed: ' . $supabaseException->getMessage());
                    return [
                        'error_code' => MyConstant::FAILED_CODE,
                        'status_code' => 500,
                        'message' => 'Internal server error: ' . $e->getMessage(),
                    ];
                }
            }
            
            if (!$createdRequest) {
                return [
                    'error_code' => MyConstant::FAILED_CODE,
                    'status_code' => 500,
                    'message' => 'Failed to create request due to database connection issues.',
                ];
            }

            if ($request->document_type == 'Baptismal Certificate') {
                try {
                    // Combine first, middle, and last names into a full name
                    $fullName = trim(($request->first_name_child ?? '') . ' ' . ($request->middle_name_child ?? '') . ' ' . ($request->last_name_child ?? ''));
                    
                    CertificateDetail::create([
                        'request_id' => $createdRequest->id,  // Link to the main request
                        'certificate_type' => $request->document_type,
                        'name_of_child' => $fullName,
                        'date_of_birth' => $request->date_of_birth ?? null,
                        'place_of_birth' => $request->place_of_birth ?? null,
                        'name_of_father' => $request->name_of_father ?? null,
                        'name_of_mother' => $request->name_of_mother ?? null,
                        'baptism_schedule' => $request->baptism_schedule ?? null,
                    ]);
                } catch (Exception $e) {
                    Log::warning('Failed to create certificate details, trying Supabase fallback: ' . $e->getMessage());
                    // Try Supabase fallback for certificate details
                    try {
                        $supabaseService = new SupabaseService();
                        
                        // Combine first, middle, and last names into a full name
                        $fullName = trim(($request->first_name_child ?? '') . ' ' . ($request->middle_name_child ?? '') . ' ' . ($request->last_name_child ?? ''));
                        
                        $certificateData = [
                            'request_id' => $createdRequest->id,
                            'certificate_type' => $request->document_type,
                            'name_of_child' => $fullName,
                            'date_of_birth' => $request->date_of_birth ?? null,
                            'place_of_birth' => $request->place_of_birth ?? null,
                            'name_of_father' => $request->name_of_father ?? null,
                            'name_of_mother' => $request->name_of_mother ?? null,
                            'baptism_schedule' => $request->baptism_schedule ?? null,
                        ];
                        $supabaseService->insert('tcertificate_details', $certificateData);
                        Log::info('Certificate details saved via Supabase fallback');
                    } catch (Exception $supabaseException) {
                        Log::error('Supabase certificate details fallback also failed: ' . $supabaseException->getMessage());
                    }
                }
            } elseif ($request->document_type == 'Marriage Certificate') {
                try {
                    // Combine bride name fields
                    $brideName = trim(($request->bride_first_name ?? '') . ' ' . ($request->bride_middle_name ?? '') . ' ' . ($request->bride_last_name ?? ''));
                    // Combine groom name fields
                    $groomName = trim(($request->groom_first_name ?? '') . ' ' . ($request->groom_middle_name ?? '') . ' ' . ($request->groom_last_name ?? ''));
                    
                    CertificateDetail::create([
                    'request_id' => $createdRequest->id,  // Link to the main request
                    'certificate_type' => $request->document_type,
                    'bride_name' => $brideName,
                    'age_bride' => $request->age_bride ?? null,
                    'birthdate_bride' => $request->birthdate_bride ?? null,
                    'birthplace_bride' => $request->birthplace_bride ?? null,
                    'citizenship_bride' => $request->citizenship_bride ?? null,
                    'religion_bride' => $request->religion_bride ?? null,
                    'residence_bride' => $request->residence_bride ?? null,
                    'civil_status_bride' => $request->civil_status_bride ?? null,
                    'name_of_father_bride' => $request->name_of_father_bride ?? null,
                    'name_of_mother_bride' => $request->name_of_mother_bride ?? null,
                    'name_of_groom' => $groomName,
                    'age_groom' => $request->age_groom ?? null,
                    'birthdate_groom' => $request->birthdate_groom ?? null,
                    'birthplace_groom' => $request->birthplace_groom ?? null,
                    'citizenship_groom' => $request->citizenship_groom ?? null,
                    'religion_groom' => $request->religion_groom ?? null,
                    'residence_groom' => $request->residence_groom ?? null,
                    'civil_status_groom' => $request->civil_status_groom ?? null,
                    'name_of_father_groom' => $request->name_of_father_groom ?? null,
                    'name_of_mother_groom' => $request->name_of_mother_groom ?? null,
                ]);
                } catch (Exception $e) {
                    Log::warning('Failed to create marriage certificate details: ' . $e->getMessage());
                }
            } elseif ($request->document_type == 'Death Certificate') {

                $fileUrl = null;

                if ($request->hasFile('file_death')) {
                    $file = $request->file('file_death');
                    
                    // Validate file
                    $fileValidator = Validator::make(['file_death' => $file], [
                        'file_death' => 'file|mimes:pdf,jpg,jpeg,png|max:10240'
                    ]);
                    
                    if ($fileValidator->fails()) {
                        return [
                            'error_code' => MyConstant::FAILED_CODE,
                            'status_code' => MyConstant::BAD_REQUEST,
                            'message' => 'Invalid file format or size for death certificate',
                        ];
                    }
                    
                    // Upload to Supabase
                    $storageService = new SupabaseStorageService();
                    $bucket = env('SUPABASE_STORAGE_BUCKET_REQUESTS', 'requests');
                    $result = $storageService->upload($file, $bucket, 'death_certificates');
                    
                    if ($result['success']) {
                        $fileUrl = $result['url'];
                        Log::info('Death certificate uploaded to Supabase', ['url' => $fileUrl]);
                    } else {
                        Log::error('Supabase upload failed for death certificate', ['error' => $result['error']]);
                        return [
                            'error_code' => MyConstant::FAILED_CODE,
                            'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                            'message' => 'Failed to upload death certificate',
                        ];
                    }
                }

                try {
                    CertificateDetail::create([
                        'request_id' => $createdRequest->id,
                        'certificate_type' => $request->document_type,
                        'first_name_death' => $request->first_name_death ?? null,
                        'middle_name_death' => $request->middle_name_death ?? null,
                        'last_name_death' => $request->last_name_death ?? null,
                        'date_of_birth_death' => $request->date_of_birth_death ?? null,
                        'date_of_death' => $request->date_of_death ?? null,
                        'file_death' => $fileUrl,
                    ]);
                } catch (Exception $e) {
                    Log::warning('Failed to create death certificate details: ' . $e->getMessage());
                }

            } elseif ($request->document_type == 'Confirmation Certificate') {
                try {
                    CertificateDetail::create([
                        'request_id' => $createdRequest->id,  // Link to the main request
                        'certificate_type' => $request->document_type,
                        'confirmation_first_name' => $request->confirmation_first_name ?? null,
                        'confirmation_middle_name' => $request->confirmation_middle_name ?? null,
                        'confirmation_last_name' => $request->confirmation_last_name ?? null,
                        'confirmation_place_of_birth' => $request->confirmation_place_of_birth ?? null,
                        'confirmation_date_of_baptism' => $request->confirmation_date_of_baptism ?? null,
                        'confirmation_fathers_name' => $request->confirmation_fathers_name ?? null,
                        'confirmation_mothers_name' => $request->confirmation_mothers_name ?? null,
                        'confirmation_date_of_confirmation' => $request->confirmation_date_of_confirmation ?? null,
                        'confirmation_sponsors_name' => $request->confirmation_sponsors_name ?? null,
                    ]);
                } catch (Exception $e) {
                    Log::warning('Failed to create confirmation certificate details: ' . $e->getMessage());
                }
            }

            try {
                Notification::create([
                    'type' => 'Request',
                    'message' => 'A new request has been created by ' . Auth::user()->name,
                    'is_read' => '0',
                ]);
            } catch (Exception $e) {
                Log::warning('Failed to create notification: ' . $e->getMessage());
            }

            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Request created successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ];
        }
    }

    public function update(Request $request, $id)
{
    // Validate required fields
    $validator = Validator::make($request->all(), [
        'document_type' => 'required|string',
        'status' => 'sometimes|nullable|string',
        'amount' => 'sometimes|nullable|numeric',
    ]);

    if ($validator->fails()) {
        return [
            'error_code' => MyConstant::FAILED_CODE,
            'status_code' => MyConstant::BAD_REQUEST,
            'message' => $validator->errors()->first(),
        ];
    }

    try {
        $approval = RequestModel::findOrFail($id);

        // Update main request
        $approval->document_type = $request->document_type;
        $approval->requested_by = Auth::id();
        $approval->status = $request->status ?? $approval->status;
        $approval->is_paid = $request->is_paid ?? $approval->is_paid;
        $approval->notes = $request->notes ?? $approval->notes;
        $approval->save();

        // Update certificate details depending on type
        $details = CertificateDetail::find($id);
        if (!$details) {
            $details = new CertificateDetail();
            $details->id = $id;
        }

        switch ($request->document_type) {
            case 'Baptismal Certificate':
                $details->update($request->only([
                    'name_of_child', 'date_of_birth', 'place_of_birth',
                    'baptism_schedule', 'name_of_father', 'name_of_mother'
                ]));
                break;

            case 'Marriage Certificate':
                $details->update($request->only([
                    'bride_name','age_bride','birthdate_bride','birthplace_bride',
                    'citizenship_bride','religion_bride','residence_bride','civil_status_bride',
                    'name_of_father_bride','name_of_mother_bride','name_of_groom',
                    'age_groom','birthdate_groom','birthplace_groom','citizenship_groom',
                    'religion_groom','residence_groom','civil_status_groom',
                    'name_of_father_groom','name_of_mother_groom'
                ]));
                break;

            case 'Death Certificate':
                if ($request->hasFile('file_death')) {
                    $file = $request->file('file_death');
                    
                    // Validate file
                    $fileValidator = Validator::make(['file_death' => $file], [
                        'file_death' => 'file|mimes:pdf,jpg,jpeg,png|max:10240'
                    ]);
                    
                    if (!$fileValidator->fails()) {
                        $storageService = new SupabaseStorageService();
                        $bucket = env('SUPABASE_STORAGE_BUCKET_REQUESTS', 'requests');
                        $result = $storageService->upload($file, $bucket, 'death_certificates');
                        
                        if ($result['success']) {
                            $details->file_death = $result['url'];
                            Log::info('Death certificate updated in Supabase', ['url' => $result['url']]);
                        } else {
                            Log::error('Failed to update death certificate', ['error' => $result['error']]);
                        }
                    }
                }
                $details->update($request->only(['first_name_death','middle_name_death','last_name_death']));
                break;

            case 'Confirmation Certificate':
                $details->update($request->only([
                    'confirmation_first_name','confirmation_middle_name','confirmation_last_name',
                    'confirmation_place_of_birth','confirmation_date_of_baptism',
                    'confirmation_fathers_name','confirmation_mothers_name',
                    'confirmation_date_of_confirmation','confirmation_sponsors_name'
                ]));
                break;
        }

        // Handle payment
        if ($request->amount) {
            $payment = Payment::updateOrCreate(
                ['request_id' => $id],
                [
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $request->status === 'Approved' ? 'Paid' : 'Pending',
                    'payment_date' => now('Asia/Manila'),
                    'transaction_id' => $request->transaction_id
                ]
            );
        }

        // Reset payment if declined
        if ($request->status === 'Declined' && $approval->payment) {
            $approval->payment->delete();
            $approval->is_paid = 0;
            $approval->save();
        }

        session()->flash('success', 'Request updated successfully');
        return [
            'error_code' => MyConstant::SUCCESS_CODE,
            'status_code' => MyConstant::OK,
            'message' => 'Request updated successfully',
        ];

    } catch (\Exception $e) {
        session()->flash('error', 'Internal server error');
        return [
            'error_code' => MyConstant::INTERNAL_SERVER_ERROR,
            'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
            'message' => 'Internal server error: ' . $e->getMessage(),
        ];
    }
}


    public function approve_request(Request $request, $id)
    {
        $request->merge([
            'status' => trim($request->status)
        ]);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Approved,Declined,Received,Completed,',
            'notes'  => 'nullable|string',
        ]);


        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }
        

        // 🔐 BACKEND GUARD (DITO ITO)
        $approval = RequestModel::findOrFail($id);         

    // ✅ SAFE UPDATE
    $approval->update([
        'approved_by' => Auth::id(),
        'status' => $request->status,
        'notes' => $request->notes,
    ]);

    try {
        RequestModel::where('id', $id)->update([
            'approved_by' => Auth::id(),
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // EMAIL kapag Declined
        if ($request->status === 'Declined') {
            $user = User::find($request->requested_by);

            if ($user) {
                $phpMailer = new PHPMailer(true);
                $phpMailer->isSMTP();
                $phpMailer->Host = 'smtp.gmail.com';
                $phpMailer->SMTPAuth = true;
                $phpMailer->Username = 'stmichaelthearcanghel@gmail.com';
                $phpMailer->Password = 'hnzz zkkw zedc fxad';
                $phpMailer->SMTPSecure = 'tls';
                $phpMailer->Port = 587;

                $phpMailer->setFrom('stmichaelthearcanghel@gmail.com', 'St. Michael the Arcanghel');
                $phpMailer->addAddress($user->email, $user->name);
                $phpMailer->Subject = 'Request Declined';
                $phpMailer->Body = 'Your request has been declined. Notes: ' . $request->notes;
                $phpMailer->send();
            }
        }

        // EMAIL kapag Approved
if ($request->status === 'Approved') {
    $user = User::find($approval->requested_by);

    if ($user) {
        $phpMailer = new PHPMailer(true);
        $phpMailer->isSMTP();
        $phpMailer->Host = 'smtp.gmail.com';
        $phpMailer->SMTPAuth = true;
        $phpMailer->Username = 'stmichaelthearcanghel@gmail.com';
        $phpMailer->Password = 'hnzz zkkw zedc fxad';
        $phpMailer->SMTPSecure = 'tls';
        $phpMailer->Port = 587;

        $phpMailer->setFrom('stmichaelthearcanghel@gmail.com', 'St. Michael the Archangel');
        $phpMailer->addAddress($user->email, $user->name);
        $phpMailer->Subject = 'Request Approved';
        $phpMailer->Body = 
            "Good day {$user->name},\n\n" .
            "Your request for {$approval->document_type} has been APPROVED.\n\n" .
            "Please proceed with the payment to continue the process.\n\n" .
            "Thank you.\nSt. Michael the Archangel Parish";

        $phpMailer->send();
    }
}


        // Notifications
        Notification::create([
            'type' => 'Request',
            'message' => 'Request ' . strtolower($request->status) . ' by ' . Auth::user()->name,
            'is_read' => '0',
        ]);

        return [
            'error_code' => MyConstant::SUCCESS_CODE,
            'status_code' => MyConstant::OK,
            'message' => 'Request updated successfully',
        ];

    } catch (\Exception $e) {
        return [
            'error_code' => MyConstant::INTERNAL_SERVER_ERROR,
            'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
            'message' => $e->getMessage(),
        ];
    }
}


    public function destroy($id)
    {
        try {
            RequestModel::where('id', $id)->delete();

            session()->flash('success', 'Request deleted successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Request deleted successfully',
            ];

        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ];
        }
    }


    // Dashboard Request Baptismal
    public function requestBaptismal(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validator->requestValidator());

        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }

        try {
            $content = $request->input('content');
            preg_match('/<img src="data:image\/(.*?);base64,(.*?)"/', $content, $matches);

            if (isset($matches[2])) {
                $imageData = base64_decode($matches[2]);
                $imageName = 'baptismal_certificate_' . time() . '.png';
                $imagePath = public_path('assets/documents/Baptismal_Certificate/' . $imageName);

                file_put_contents($imagePath, $imageData);
            } else {
                $imageName = null;
            }

            RequestModel::create([
                'requested_by' => Auth::user()->id,
                'document_type' => $request->document_type,
                'status' => 'Pending',
                'is_paid' => 'Unpaid',
                'file' => $imageName,
            ]);

            if ($request->document_type == 'Baptismal Certificate') {
                CertificateDetail::create([
                    'certificate_type' => $request->document_type,
                    'name_of_child' => $request->name_of_child,
                    'date_of_birth' => $request->date_of_birth,
                    'place_of_birth' => $request->place_of_birth,
                    'date_of_baptism' => now('Asia/Manila'),
                    'name_of_father' => $request->name_of_father,
                    'name_of_mother' => $request->name_of_mother,
                ]);
            }

            Notification::create([
                'type' => 'Request',
                'message' => 'A new baptismal certificate request has been created by ' . Auth::user()->name,
                'is_read' => '1',
            ]);

            session()->flash('success', 'Request created successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Request created successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ];
        }
    }

    // public function updatePayment(Request $request, $id, $amount)
    // {
    //     try {
    //         RequestModel::where('id', $id)->update([
    //             'is_paid' => 'Paid',
    //         ]);

    //         // Payment creation for all payment methods
    //             Payment::create([
    //                 'request_id' => $id,
    //                 'name' => Auth::user()->name,
    //                 'payment_status' => 'Paid',
    //                 'payment_method' => 'Gcash',
    //                 'amount' => $amount,
    //                 'payment_date' => now('Asia/Manila'),
    //                 'transaction_id' => $request->transaction_id,
    //             ]);
    //         if ($request->status == 'Approved') {
    //             Notification::create([
    //                 'type' => 'Request',
    //                 'message' => 'A request has been approved by ' . Auth::user()->name,
    //                 'is_read' => '0',
    //             ]);
    //         }
    //         session()->flash('success', 'Request payment updated successfully');
    //         return [
    //             'error_code' => MyConstant::SUCCESS_CODE,
    //             'status_code' => MyConstant::OK,
    //             'message' => 'Request payment updated successfully',
    //         ];
    //     } catch (QueryException $e) {
    //         session()->flash('error', 'Internal server error');
    //         return [
    //             'error_code' => MyConstant::INTERNAL_SERVER_ERROR,
    //             'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
    //             'message' => 'Internal server error: ' . $e->getMessage(),
    //         ];
    //     }
    // }

    public function updatePayment(Request $request, $id, $amount, $transactionUrl)
{
    try {
        // Try database first
        $requestModel = RequestModel::findOrFail($id);

        $requestModel->update([
            'is_paid' => 'Paid',
        ]);

        Payment::create([
            'request_id' => $id,
            'payment_status' => 'Pending',
            'payment_method' => 'Gcash',
            'amount' => $amount,
            'payment_date' => now('Asia/Manila'),
            'transaction_id' => $transactionUrl,
        ]);

        Notification::create([
            'type' => 'Payment',
            'message' => 'Payment uploaded and waiting for admin verification.',
            'is_read' => '0',
        ]);

        session()->flash('success', 'Payment submitted. Waiting for admin verification.');

        return [
            'error_code' => MyConstant::SUCCESS_CODE,
            'status_code' => MyConstant::OK,
            'message' => 'Payment uploaded successfully',
        ];

    } catch (\Exception $e) {
        // Fallback to Supabase REST API
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');

            // Update request to mark as paid
            $updateResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->patch($supabaseUrl . '/rest/v1/trequests?id=eq.' . $id, [
                'is_paid' => 'Paid',
                'updated_at' => now('Asia/Manila')->toIso8601String()
            ]);

            if (!$updateResponse->successful()) {
                throw new \Exception('Failed to update request: ' . $updateResponse->body());
            }

            // Create payment record
            $paymentResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->post($supabaseUrl . '/rest/v1/tpayments', [
                'request_id' => $id,
                'payment_status' => 'Pending',
                'payment_method' => 'Gcash',
                'amount' => $amount,
                'payment_date' => now('Asia/Manila')->toDateString(),
                'transaction_id' => $transactionUrl,
                'created_at' => now('Asia/Manila')->toIso8601String(),
                'updated_at' => now('Asia/Manila')->toIso8601String()
            ]);

            if (!$paymentResponse->successful()) {
                throw new \Exception('Failed to create payment: ' . $paymentResponse->body());
            }

            // Create notification
            \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json'
            ])->post($supabaseUrl . '/rest/v1/tnotifications', [
                'type' => 'Payment',
                'message' => 'Payment uploaded and waiting for admin verification.',
                'is_read' => '0',
                'created_at' => now('Asia/Manila')->toIso8601String(),
                'updated_at' => now('Asia/Manila')->toIso8601String()
            ]);

            session()->flash('success', 'Payment submitted. Waiting for admin verification.');

            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Payment uploaded successfully via Supabase API',
            ];

        } catch (\Exception $apiException) {
            session()->flash('error', 'Failed to process payment: ' . $apiException->getMessage());
            return [
                'error_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => $apiException->getMessage(),
            ];
        }
    }
}

public function verifyPayment(Request $request, $id)
{
    $payment = Payment::findOrFail($id);

    // Mark payment as verified
    $payment->payment_status = 'Verified';
    $payment->save();

    // Update request to Completed
    $requestRecord = $payment->request;
    $requestRecord->status = 'Completed';
    $requestRecord->save();

    // 📧 EMAIL KAPAG COMPLETED
    $user = User::find($requestRecord->requested_by);

    if ($user) {
        $phpMailer = new PHPMailer(true);
        $phpMailer->isSMTP();
        $phpMailer->Host = 'smtp.gmail.com';
        $phpMailer->SMTPAuth = true;
        $phpMailer->Username = 'stmichaelthearcanghel@gmail.com';
        $phpMailer->Password = 'hnzz zkkw zedc fxad';
        $phpMailer->SMTPSecure = 'tls';
        $phpMailer->Port = 587;

        $phpMailer->setFrom('stmichaelthearcanghel@gmail.com', 'St. Michael the Archangel');
        $phpMailer->addAddress($user->email, $user->name);
        $phpMailer->Subject = 'Request Completed';
        $phpMailer->Body =
            "Good day {$user->name},\n\n" .
            "Your request for {$requestRecord->document_type} has been COMPLETED.\n\n" .
            "You may now claim your document on your scheduled pickup date.\n\n" .
            "Thank you.\nSt. Michael the Archangel Parish";

        $phpMailer->send();
    }

    return redirect()->back()
        ->with('success', 'Payment verified and request completed!');
}



};