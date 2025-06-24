<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Network;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use DB;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            User::factory(24)
                ->withTenant()
                ->create();

            User::factory()->withTenant('test-user')->create([
                'name'  => 'Bruno Costa',
                'login' => 'bhcosta90',
                'email' => 'bhcosta90@gmail.com',
            ]);

            Network::factory()->create([
                'tenant_id' => 'test-user',
                'name'      => 'testing',
            ]);

            User::factory()->withTenant('test-user-2')->create([
                'name'  => 'Test User 2',
                'login' => 'test-user-2',
                'email' => 'test2@example.com',
            ]);

            Network::factory()->create([
                'tenant_id' => 'test-user-2',
                'name'      => 'testing',
            ]);
        });
    }
}
