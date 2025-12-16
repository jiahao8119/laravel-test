<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $payload = [
            'name' => 'yap',
            'email' => 'yap@example.com',
            'phone_number' => '0123456789',
            'password' => 'abcd12345',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'yap@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'yap@example.com',
            'phone_number' => '0123456789',
        ]);
    }

    public function test_lists_users()
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_duplicate_email()
    {
        User::factory()->create([
            'email' => 'dup@test.com',
        ]);

        $payload = [
            'name' => 'Another',
            'email' => 'dup@test.com',
            'phone_number' => '0199999999',
            'password' => 'password123',
            'status' => 'active',
        ];

        $this->postJson('/api/users', $payload)
            ->assertStatus(422);
    }

    public function test_soft_delete()
    {
        $user = User::factory()->create();

        $this->deleteJson("/api/users/{$user->id}")
            ->assertStatus(200);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }
}
