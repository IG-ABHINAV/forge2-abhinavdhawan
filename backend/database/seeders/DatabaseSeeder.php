<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\SlaPolicy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $orgNames = ['Stark Industries', 'Wayne Enterprises', 'Acme Corp'];

        foreach ($orgNames as $name) {
            $org = Organization::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'plan' => 'growth',
                'domain' => Str::slug($name) . '.pulsedesk.com',
            ]);

            // Seed SLA Policy
            SlaPolicy::create([
                'name' => 'Standard Policy',
                'priority' => 'high',
                'response_hours' => 4,
                'resolution_hours' => 24,
                'organization_id' => $org->id,
            ]);

            // Seed Users
            $admin = User::create([
                'name' => "Admin User ({$name})",
                'email' => strtolower(Str::slug($name)) . '-admin@pulsedesk.com',
                'password' => Hash::make('password123'),
                'organization_id' => $org->id,
                'role' => 'admin',
            ]);

            $agent = User::create([
                'name' => "Agent User ({$name})",
                'email' => strtolower(Str::slug($name)) . '-agent@pulsedesk.com',
                'password' => Hash::make('password123'),
                'organization_id' => $org->id,
                'role' => 'agent',
            ]);

            if ($name === 'Acme Corp') {
                User::create([
                    'name' => 'Acme Customer',
                    'email' => 'customer@acme.test',
                    'password' => Hash::make('password'),
                    'organization_id' => $org->id,
                    'role' => 'customer',
                ]);

                $admin->forceFill(['email' => 'admin@acme.test', 'password' => Hash::make('password')])->save();
                $agent->forceFill(['email' => 'agent@acme.test', 'password' => Hash::make('password')])->save();
            }

            // Seed Tickets
            for ($i = 1; $i <= 15; $i++) {
                Ticket::create([
                    'title' => "Sample Ticket #{$i} for {$name}",
                    'description' => "This is the description for sample support ticket number {$i}.",
                    'status' => $i % 3 == 0 ? 'in_progress' : ($i % 5 == 0 ? 'resolved' : 'open'),
                    'priority' => $i % 4 == 0 ? 'urgent' : ($i % 3 == 0 ? 'high' : 'medium'),
                    'organization_id' => $org->id,
                    'user_id' => $admin->id,
                    'assigned_to' => $agent->id,
                    'sla_breached' => $i % 7 == 0,
                ]);
            }
        }
    }
}
