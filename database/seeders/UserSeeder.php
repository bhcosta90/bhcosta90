<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Network;
use App\Models\User;
use DB;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $total = 25;

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
            --$total;
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
            --$total;
        }

        User::factory($total)
            ->withTenant()
            ->create();
    }
}
