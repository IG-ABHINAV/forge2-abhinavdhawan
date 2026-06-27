<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_other_organization_tickets(): void
    {
        $org1 = Organization::create(['name' => 'Org One', 'slug' => 'org-one', 'plan' => 'growth', 'domain' => 'one.com']);
        $org2 = Organization::create(['name' => 'Org Two', 'slug' => 'org-two', 'plan' => 'growth', 'domain' => 'two.com']);

        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@one.com',
            'password' => bcrypt('password123'),
            'organization_id' => $org1->id,
            'role' => 'admin',
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@two.com',
            'password' => bcrypt('password123'),
            'organization_id' => $org2->id,
            'role' => 'admin',
        ]);

        $ticket2 = Ticket::create([
            'title' => 'Org Two Ticket',
            'description' => 'Confidential info of org two.',
            'status' => 'open',
            'priority' => 'high',
            'organization_id' => $org2->id,
            'user_id' => $user2->id,
        ]);

        Sanctum::actingAs($user1);

        // Accessing org2 ticket should return 404
        $response = $this->getJson("/api/v1/tickets/{$ticket2->id}");
        $response->assertStatus(404);
    }
}
