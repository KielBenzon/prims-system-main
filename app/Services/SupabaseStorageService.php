<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class SupabaseStorageService
{
    private $url;
    private $serviceKey;
    private $storageUrl;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->serviceKey = env('SUPABASE_SERVICE_ROLE_KEY');
        $this->storageUrl = env('SUPABASE_STORAGE_URL');
    }

    /**
     * Upload a file to Supabase Storage
     * 
     * @param UploadedFile $file
     * @param string $bucket
     * @param string|null $path
     * @return array ['success' => bool, 'url' => string|null, 'path' => string|null, 'error' => string|null]
     */
    public function upload(UploadedFile $file, string $bucket, ?string $path = null): array
    {
        try {
            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $path ? $path . '/' . $filename : $filename;

            // Get file contents
            $fileContents = file_get_contents($file->getRealPath());

            // Upload to Supabase
            $response = Http::withHeaders([
                'apikey' => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
                'Content-Type' => $file->getMimeType(),
            ])->withBody($fileContents, $file->getMimeType())
              ->post("{$this->storageUrl}/object/{$bucket}/{$filePath}");

            if ($response->successful()) {
                $publicUrl = $this->getPublicUrl($bucket, $filePath);
                
                Log::info('File uploaded to Supabase', [
                    'bucket' => $bucket,
                    'path' => $filePath,
                    'url' => $publicUrl
                ]);

                return [
                    'success' => true,
                    'url' => $publicUrl,
                    'path' => $filePath,
                    'error' => null
                ];
            }

            Log::error('Supabase upload failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'url' => null,
                'path' => null,
                'error' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Supabase upload exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'url' => null,
                'path' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get public URL for a file
     * 
     * @param string $bucket
     * @param string $path
     * @return string
     */
    public function getPublicUrl(string $bucket, string $path): string
    {
        return "{$this->storageUrl}/object/public/{$bucket}/{$path}";
    }

    /**
     * Delete a file from Supabase Storage
     * 
     * @param string $bucket
     * @param string $path
     * @return bool
     */
    public function delete(string $bucket, string $path): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
            ])->delete("{$this->storageUrl}/object/{$bucket}/{$path}");

            if ($response->successful()) {
                Log::info('File deleted from Supabase', [
                    'bucket' => $bucket,
                    'path' => $path
                ]);
                return ['success' => true];
            }

            Log::error('Supabase delete failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return ['success' => false, 'error' => $response->body()];

        } catch (\Exception $e) {
            Log::error('Supabase delete exception', [
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * List files in a bucket
     * 
     * @param string $bucket
     * @param string|null $path
     * @return array
     */
    public function list(string $bucket, ?string $path = null): array
    {
        try {
            $url = "{$this->storageUrl}/object/list/{$bucket}";
            if ($path) {
                $url .= "?prefix={$path}";
            }

            $response = Http::withHeaders([
                'apikey' => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
            ])->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Supabase list exception', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Check if file exists
     * 
     * @param string $bucket
     * @param string $path
     * @return bool
     */
    public function exists(string $bucket, string $path): bool
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
            ])->head("{$this->storageUrl}/object/{$bucket}/{$path}");

            return $response->successful();

        } catch (\Exception $e) {
            return false;
        }
    }
}
