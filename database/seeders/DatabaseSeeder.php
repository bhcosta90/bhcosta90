<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'Bruno Henrique da Costa',
            'email'    => 'bhcosta90@gmail.com',
            'password' => '\$2y\$12\$9sFFNwBsNvHArqVBHnkS6usyaeiG7XKQMb5n.MzPl3NPR4f.LoKmq',
        ]);
    }
}
