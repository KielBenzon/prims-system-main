<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class Controller
{
    /**
     * Execute a database query with Supabase fallback
     */
    protected function executeWithFallback($queryCallback, $fallbackData = [])
    {
        try {
            return $queryCallback();
        } catch (Exception $e) {
            Log::warning('Database query failed, returning fallback data: ' . $e->getMessage());
            return $fallbackData;
        }
    }

    /**
     * Get paginated data with Supabase fallback
     */
    protected function getPaginatedWithFallback($queryCallback, $perPage = 10)
    {
        try {
            return $queryCallback();
        } catch (Exception $e) {
            Log::warning('Paginated query failed: ' . $e->getMessage());
            
            // Return a simple paginator with empty results
            $items = collect([]);
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                0,
                $perPage,
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }
    }

    /**
     * Get Supabase data for a table with pagination and filtering
     */
    protected function getSupabaseData($table, $filters = [], $page = 1, $perPage = 10)
    {
        try {
            $supabaseService = new SupabaseService();
            
            // Build query string with filters
            $queryParams = [];
            foreach ($filters as $key => $value) {
                $queryParams[] = $key . '=' . urlencode($value);
            }
            
            $queryString = !empty($queryParams) ? '?' . implode('&', $queryParams) : '';
            $allData = $supabaseService->query($table . $queryString);
            
            // Simple pagination simulation
            $offset = ($page - 1) * $perPage;
            $items = collect($allData)->slice($offset, $perPage);
            $total = count($allData);
            
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } catch (Exception $e) {
            Log::error('Supabase fallback also failed: ' . $e->getMessage());
            return collect([]); // Return empty collection instead of recursion
        }
    }
}
