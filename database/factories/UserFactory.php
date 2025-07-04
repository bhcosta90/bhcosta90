<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class UserFactory extends Factory
{
    private static ?string $password = null;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'login'             => mb_strtolower(fake()->unique()->colorName()),
            'email_verified_at' => now(),
            'password'          => self::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function withTenant(
        ?string $domain = null,
        ?int $totalDays = null,
        ?string $date = null
    ): self {
        return $this->afterCreating(function (User $user) use ($domain, $totalDays, $date): void {
            $tenant = Tenant::factory()->create(array_filter([
                'id'              => $domain ?? fake()->unique()->domainName(),
                'total_redirects' => $totalDays,
                'date_expired'    => $date,
            ], fn ($value): bool => !is_null($value)));

            $tenant->users()->attach($user);
        });
    }
}
