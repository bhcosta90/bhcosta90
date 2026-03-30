<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'Bruno Henrique da Costa',
            'email'    => 'bhcosta90@gmail.com',
            'password' => '$2y$12$oRg0.90otEE9Wm823i/uCeGgLGPvnKHvRunEQtR6/Ykr6T1hwWsam',
        ]);
    }
}
