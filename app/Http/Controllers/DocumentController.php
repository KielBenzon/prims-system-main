<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    use SoftDeletes;
    public function index()
    {
        $search = request('search');
        
        // Try to get documents from local DB, fallback to Supabase
        try {
            $documents = Document::query()
                ->when($search, fn($query, $search) => $query->where('document_type', 'like', "%{$search}%"))
                ->orderByDesc('created_at')
                ->paginate(10);
        } catch (\Exception $e) {
            Log::warning('Local documents query failed, using Supabase API: ' . $e->getMessage());
            $documents = $this->getDocumentsFromSupabase($search, false);
        }

        // Try to get deleted documents
        try {
            $deletedDocuments = Document::onlyTrashed()
                ->when($search, fn($query, $search) => $query->where('document_type', 'like', "%{$search}%"))
                ->orderByDesc('created_at')
                ->paginate(10);
        } catch (\Exception $e) {
            Log::warning('Local deleted documents query failed, using Supabase API: ' . $e->getMessage());
            $deletedDocuments = $this->getDocumentsFromSupabase($search, true);
        }

        // Get counts
        try {
            $counts = Document::selectRaw('document_type, COUNT(*) as count')
                ->groupBy('document_type')
                ->get();
        } catch (\Exception $e) {
            Log::warning('Local counts query failed, using Supabase API: ' . $e->getMessage());
            $counts = $this->getDocumentCountsFromSupabase();
        }

        $baptismal = $counts->firstWhere('document_type', 'Baptismal Certificate')->count ?? 0;
        $marriage = $counts->firstWhere('document_type', 'Marriage Certificate')->count ?? 0;
        $death = $counts->firstWhere('document_type', 'Death Certificate')->count ?? 0;
        $confirmation = $counts->firstWhere('document_type', 'Confirmation Certificate')->count ?? 0;

        return view('admin.documents', compact('documents', 'deletedDocuments', 'counts', 'baptismal', 'marriage', 'death', 'confirmation'));
    }

    private function getDocumentsFromSupabase($search = null, $onlyTrashed = false)
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
        
        $query = "{$supabaseUrl}/rest/v1/tdocuments?select=*&order=created_at.desc";
        
        if ($onlyTrashed) {
            $query .= "&deleted_at=not.is.null";
        } else {
            $query .= "&deleted_at=is.null";
        }
        
        if ($search) {
            $query .= "&document_type=ilike.*{$search}*";
        }
        
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
        ])->get($query);
        
        if ($response->successful()) {
            $data = collect($response->json())->map(function($item) {
                return (object) $item;
            });
            
            // Create a simple paginator
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $data,
                $data->count(),
                10,
                1,
                ['path' => request()->url()]
            );
        }
        
        return new \Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 10, 1);
    }

    private function getDocumentCountsFromSupabase()
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
        
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
        ])->get("{$supabaseUrl}/rest/v1/tdocuments?select=document_type&deleted_at=is.null");
        
        if ($response->successful()) {
            $documents = collect($response->json());
            return $documents->groupBy('document_type')->map(function($items, $type) {
                return (object) ['document_type' => $type, 'count' => $items->count()];
            })->values();
        }
        
        return collect([]);
    }

    public function store(Request $request)
    {
        try {
            // Basic validation
            $request->validate([
                'full_name' => 'required|string',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,txt,rtf,bmp,webp|max:10240',
                'uploaded_by' => 'required|string',
            ], [
                'full_name.required' => 'Full name is required.',
                'file.required' => 'Document file is required.',
                'file.file' => 'Please select a valid file.',
                'file.mimes' => 'Document must be in PDF, JPG, JPEG, PNG, DOC, DOCX, TXT, RTF, BMP, or WEBP format.',
                'file.max' => 'Document file must be under 10MB in size.',
                'uploaded_by.required' => 'Uploader information is required.',
            ]);

            if (!$request->hasFile('file')) {
                return redirect()->back()->with('error', 'File is required.');
            }

            $file = $request->file('file');
            $fileUrl = null;
            $documentType = null;

            // Use DocumentService for AI classification
            $documentService = app(DocumentService::class);
            $ocrText = $documentService->extractTextFromFile($file);
            
            // Quality check
            if (strlen(trim($ocrText)) < 30) {
                return redirect()->back()->with('error', 'Failed to process document. Image may be too blurry or text is insufficient.');
            }

            // Check for inappropriate content
            if ($documentService->checkForInappropriateContent($ocrText)) {
                return redirect()->back()->with('error', 'Inappropriate content detected in document.');
            }

            // AI-powered document type detection
            $documentType = $documentService->determineDocumentType($ocrText);
            
            if ($documentType === 'Verification Certificate') {
                return redirect()->back()->with('error', 'Unable to identify document type from the uploaded file.');
            }

            Log::info('AI detected document type', ['type' => $documentType, 'text_length' => strlen($ocrText)]);

            // Upload to Supabase Storage
            $storageService = new SupabaseStorageService();
            $bucket = env('SUPABASE_STORAGE_BUCKET_DOCUMENTS', 'documents');
            $result = $storageService->upload($file, $bucket);
            
            if ($result['success']) {
                $fileUrl = $result['url'];
                Log::info('File uploaded to Supabase', ['url' => $fileUrl]);
            } else {
                Log::error('Supabase upload failed', ['error' => $result['error']]);
                return redirect()->back()->with('error', 'Failed to upload file to storage.');
            }

            // Try local database first
            try {
                Document::create([
                    'document_type' => $documentType,
                    'full_name' => $request->full_name,
                    'file' => $fileUrl,
                    'uploaded_by' => $request->uploaded_by,
                ]);
                Log::info('Document saved to local database');
            } catch (\Exception $dbError) {
                // Fallback to Supabase REST API
                Log::warning('Local DB insert failed, using Supabase API: ' . $dbError->getMessage());
                
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->post("{$supabaseUrl}/rest/v1/tdocuments", [
                    'document_type' => $documentType,
                    'full_name' => $request->full_name,
                    'file' => $fileUrl,
                    'uploaded_by' => $request->uploaded_by,
                ]);
                
                if (!$response->successful()) {
                    Log::error('Supabase API insert failed', ['response' => $response->body()]);
                    return redirect()->back()->with('error', 'Failed to store document in database.');
                }
                
                Log::info('Document saved to Supabase via REST API');
            }

            return redirect()->back()->with('success', "Document classified as '{$documentType}' and stored successfully.");
        } catch (\Exception $e) {
            Log::error('Document store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to store document: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Try to find document locally first
            $document = null;
            try {
                $document = Document::findOrFail($id);
            } catch (\Exception $e) {
                Log::warning('Local document lookup failed: ' . $e->getMessage());
            }

            $updateData = [];
            if ($request->filled('document_type')) $updateData['document_type'] = $request->document_type;
            if ($request->filled('full_name')) $updateData['full_name'] = $request->full_name;
            if ($request->filled('uploaded_by')) $updateData['uploaded_by'] = $request->uploaded_by;
            
            if ($request->hasFile('file')) {
                $request->validate([
                    'file' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,txt,rtf,bmp,webp|max:10240'
                ], [
                    'file.file' => 'Please select a valid file.',
                    'file.mimes' => 'Document must be in PDF, JPG, JPEG, PNG, DOC, DOCX, TXT, RTF, BMP, or WEBP format.',
                    'file.max' => 'Document file must be under 10MB in size.',
                ]);
                
                // Delete old file from Supabase Storage if it exists
                $oldFileUrl = $document->file ?? null;
                if ($oldFileUrl && str_contains($oldFileUrl, 'supabase.co/storage')) {
                    $storageService = new SupabaseStorageService();
                    $bucket = env('SUPABASE_STORAGE_BUCKET_DOCUMENTS', 'documents');
                    
                    preg_match('/\/documents\/(.+)$/', $oldFileUrl, $matches);
                    $oldFilePath = $matches[1] ?? null;
                    
                    if ($oldFilePath) {
                        $deleteResult = $storageService->delete($bucket, $oldFilePath);
                        if ($deleteResult['success']) {
                            Log::info('Old file deleted from Supabase Storage', ['path' => $oldFilePath]);
                        }
                    }
                }
                
                // Upload new file
                $storageService = new SupabaseStorageService();
                $bucket = env('SUPABASE_STORAGE_BUCKET_DOCUMENTS', 'documents');
                $result = $storageService->upload($request->file('file'), $bucket);
                
                if ($result['success']) {
                    $updateData['file'] = $result['url'];
                    Log::info('File updated in Supabase', ['url' => $result['url']]);
                } else {
                    Log::error('Supabase upload failed', ['error' => $result['error']]);
                    return redirect()->back()->with('error', 'Failed to upload file.');
                }
            }

            // Try local update first
            if ($document) {
                try {
                    $document->update($updateData);
                    Log::info('Document updated in local database');
                    return redirect()->back()->with('success', 'Document updated successfully.');
                } catch (\Exception $dbError) {
                    Log::warning('Local DB update failed, trying Supabase API: ' . $dbError->getMessage());
                }
            }
            
            // Fallback to Supabase REST API
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->patch("{$supabaseUrl}/rest/v1/tdocuments?id=eq.{$id}", $updateData);
            
            if (!$response->successful()) {
                Log::error('Supabase API update failed', ['response' => $response->body()]);
                return redirect()->back()->with('error', 'Failed to update document.');
            }
            
            Log::info('Document updated in Supabase via REST API');
            return redirect()->back()->with('success', 'Document updated successfully.');
        } catch (\Exception $e) {
            Log::error('Document update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update document.');
        }
    }

    public function destroy($id)
    {
        try {
            $document = null;
            $fileUrl = null;
            
            // Get document info first (need file URL to delete from storage)
            try {
                $document = Document::findOrFail($id);
                $fileUrl = $document->file;
            } catch (\Exception $e) {
                // Try to get from Supabase
                Log::warning('Local document lookup failed, checking Supabase: ' . $e->getMessage());
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                ])->get("{$supabaseUrl}/rest/v1/tdocuments?id=eq.{$id}&deleted_at=is.null");
                
                if ($response->successful() && !empty($response->json())) {
                    $fileUrl = $response->json()[0]['file'] ?? null;
                }
            }

            // Delete file from Supabase Storage if it exists
            if ($fileUrl && str_contains($fileUrl, 'supabase.co/storage')) {
                $storageService = new SupabaseStorageService();
                $bucket = env('SUPABASE_STORAGE_BUCKET_DOCUMENTS', 'documents');
                
                // Extract file path from URL
                preg_match('/\/documents\/(.+)$/', $fileUrl, $matches);
                $filePath = $matches[1] ?? null;
                
                if ($filePath) {
                    $deleteResult = $storageService->delete($bucket, $filePath);
                    if ($deleteResult['success']) {
                        Log::info('File deleted from Supabase Storage', ['path' => $filePath]);
                    } else {
                        Log::warning('Failed to delete file from Supabase Storage', ['path' => $filePath, 'error' => $deleteResult['error']]);
                    }
                }
            }
            
            // Try local soft delete
            if ($document) {
                try {
                    $document->delete();
                    Log::info('Document soft deleted in local database');
                    return redirect()->back()->with('success', 'Document and file deleted successfully.');
                } catch (\Exception $dbError) {
                    Log::warning('Local DB delete failed, using Supabase API: ' . $dbError->getMessage());
                }
            }
            
            // Fallback to Supabase REST API (soft delete by setting deleted_at)
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->patch("{$supabaseUrl}/rest/v1/tdocuments?id=eq.{$id}", [
                'deleted_at' => now()->toIso8601String()
            ]);
            
            if (!$response->successful()) {
                Log::error('Supabase API delete failed', ['response' => $response->body()]);
                return redirect()->back()->with('error', 'Failed to delete document.');
            }
            
            Log::info('Document soft deleted in Supabase via REST API');
            return redirect()->back()->with('success', 'Document and file deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Document destroy error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete document.');
        }
    }

    public function restore($id)
    {
        try {
            // Try local restore first
            try {
                $document = Document::withTrashed()->findOrFail($id);
                $document->restore();
                Log::info('Document restored in local database');
                return redirect()->back()->with('success', 'Document restored successfully.');
            } catch (\Exception $dbError) {
                Log::warning('Local DB restore failed, using Supabase API: ' . $dbError->getMessage());
            }
            
            // Fallback to Supabase REST API (restore by setting deleted_at to null)
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->patch("{$supabaseUrl}/rest/v1/tdocuments?id=eq.{$id}", [
                'deleted_at' => null
            ]);
            
            if (!$response->successful()) {
                Log::error('Supabase API restore failed', ['response' => $response->body()]);
                return redirect()->back()->with('error', 'Failed to restore document.');
            }
            
            Log::info('Document restored in Supabase via REST API');
            return redirect()->back()->with('success', 'Document restored successfully.');
        } catch (\Exception $e) {
            Log::error('Document restore error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to restore document.');
        }
    }


}
