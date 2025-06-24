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

            if (!User::whereLogin($login = 'john-doe')->exists()) {
                User::factory()->withTenant($login)->create([
                    'name'  => 'John Doe',
                    'login' => $login,
                    'email' => 'john-doe@gmail.com',
                ]);

                Network::factory()->create([
                    'tenant_id' => $login,
                    'name'      => 'facebook',
                ]);
            }

            if (!User::whereLogin($login = 'mayarathc99')->exists()) {
                User::factory()->withTenant($login)->create([
                    'name'  => 'Mayara Thaine de Carvalho da Costa',
                    'login' => $login,
                    'email' => 'mayarathc99@gmail.com',
                ]);

                Network::factory()->create([
                    'tenant_id' => $login,
                    'name'      => 'linkedin',
                    'endpoint'  => 'https://www.linkedin.com/in/mayara-thaine-de-carvalho-1b8064a4/',
                ]);
            }
        });
    }
}
