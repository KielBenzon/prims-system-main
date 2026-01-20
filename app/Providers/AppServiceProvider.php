<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Auth\SupabaseUserProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom user provider
        Auth::provider('supabase', function ($app, array $config) {
            return new SupabaseUserProvider($app['hash'], $config['model']);
        });
        
        View::composer('layouts.navbar', function ($view) {
            try {
                $notifications = Notification::orderBy('created_at', 'desc')->where('is_read', false)->get();
            } catch (\Exception $e) {
                // Fallback to empty collection if database fails
                $notifications = collect([]);
                Log::warning('Failed to load notifications: ' . $e->getMessage());
            }
            $view->with('notifications', $notifications);
            require_once app_path('Helpers/NotificationHelpers.php');
        });
    }
}