<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Prevent email notifications from being sent
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'Admin',   'Parishioner' //depende sa gusto mong i-test
        ]);

        // Ensure user is authenticated
        $this->assertAuthenticated();

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // If your app automatically assigns a role, set expected redirect
        $expectedRedirect = $user->role === 'Admin' ? '/admin/dashboard' : '/parishioner/dashboard';

        $response->assertRedirect($expectedRedirect);
    }
}
