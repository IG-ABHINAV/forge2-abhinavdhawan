<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_ticket_within_org(): void
    {
        $org = Organization::create(['name' => 'Stark Industries', 'slug' => 'stark', 'plan' => 'growth', 'domain' => 'stark.com']);
        $user = User::create([
            'name' => 'Tony Stark',
            'email' => 'tony@stark.com',
            'password' => bcrypt('password123'),
            'organization_id' => $org->id,
            'role' => 'admin',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tickets', [
            'title' => 'New Ticket',
            'description' => 'Fix the Arc Reactor.',
            'priority' => 'urgent',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tickets', ['title' => 'New Ticket', 'organization_id' => $org->id]);
    }

    public function test_can_list_own_org_tickets(): void
    {
        $org = Organization::create(['name' => 'Stark Industries', 'slug' => 'stark', 'plan' => 'growth', 'domain' => 'stark.com']);
        $user = User::create([
            'name' => 'Tony Stark',
            'email' => 'tony@stark.com',
            'password' => bcrypt('password123'),
            'organization_id' => $org->id,
            'role' => 'admin',
        ]);

        Ticket::create([
            'title' => 'First Ticket',
            'description' => 'First desc',
            'status' => 'open',
            'priority' => 'medium',
            'organization_id' => $org->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tickets');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json()['data']);
    }

    public function test_cannot_access_other_org_ticket(): void
    {
        $org1 = Organization::create(['name' => 'Org One', 'slug' => 'one', 'plan' => 'growth', 'domain' => 'one.com']);
        $org2 = Organization::create(['name' => 'Org Two', 'slug' => 'two', 'plan' => 'growth', 'domain' => 'two.com']);

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

        $ticket = Ticket::create([
            'title' => 'Org Two Private Ticket',
            'description' => 'Private',
            'status' => 'open',
            'priority' => 'low',
            'organization_id' => $org2->id,
            'user_id' => $user2->id,
        ]);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/v1/tickets/{$ticket->id}");
        $response->assertStatus(404);
    }
}
