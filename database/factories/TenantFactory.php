<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->name(),
            'total_redirects' => fake()->numberBetween(7, 14),
            'date_expired'    => fake()->dateTimeBetween('7 days', '14 days')->format('Y-m-d'),
            'created_at'      => Carbon::now(),
            'updated_at'      => Carbon::now(),
        ];
    }
}
