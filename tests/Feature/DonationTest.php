<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parishioner_can_submit_donation()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'Parishioner',
        ]);

        $response = $this->actingAs($user)->post('/donations/create', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@test.com',
            'amount' => 500,
            'proof' => UploadedFile::fake()->image('gcash.png'),
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('donations', [
            'email' => 'juan@test.com',
            'status' => 'Pending',
        ]);
    }
}
