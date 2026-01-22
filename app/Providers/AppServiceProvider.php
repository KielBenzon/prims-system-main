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
                if (Auth::check()) {
                    $user = Auth::user();
                    
                    if ($user->role === 'Admin') {
                        // Admin sees all notifications
                        $notifications = Notification::where('user_id', null)
                            ->orderBy('created_at', 'desc')
                            ->get();
                    } else {
                        // Parishioner sees only their own notifications
                        $notifications = Notification::where('user_id', $user->id)
                            ->whereIn('type', ['Request', 'Donation', 'Payment', 'Announcement'])
                            ->orderBy('created_at', 'desc')
                            ->get();
                    }
                } else {
                    $notifications = collect([]);
                }
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