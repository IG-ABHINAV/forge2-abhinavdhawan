<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'org_name' => 'Test Org',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'organization_id',
                         'organization',
                     ],
                 ]);

        $this->assertDatabaseHas('organizations', ['name' => 'Test Org']);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_user_can_login(): void
    {
        $org = Organization::create([
            'name' => 'Wayne Enterprises',
            'slug' => 'wayne-enterprises',
            'plan' => 'growth',
            'domain' => 'wayne.com',
        ]);

        $user = User::create([
            'name' => 'Bruce Wayne',
            'email' => 'bruce@wayne.com',
            'password' => bcrypt('password123'),
            'organization_id' => $org->id,
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'bruce@wayne.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type']);
    }

    public function test_invalid_credentials_rejected(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'wrong@email.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }
}
