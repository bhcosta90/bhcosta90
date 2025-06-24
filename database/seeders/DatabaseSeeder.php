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

            User::factory()->withTenant('john-doe')->create([
                'name'  => 'John Doe',
                'login' => 'john-doe',
                'email' => 'john-doe@gmail.com',
            ]);

            Network::factory()->create([
                'tenant_id' => 'john-doe',
                'name'      => 'facebook',
            ]);

            User::factory()->withTenant('mayarathc99')->create([
                'name'  => 'Mayara Thaine de Carvalho da Costa',
                'login' => 'mayarathc99',
                'email' => 'mayarathc99@gmail.com',
            ]);

            Network::factory()->create([
                'tenant_id' => 'mayarathc99',
                'name'      => 'linkedin',
                'endpoint'  => 'https://www.linkedin.com/in/mayara-thaine-de-carvalho-1b8064a4/',
            ]);
        });
    }
}
