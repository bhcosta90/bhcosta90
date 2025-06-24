<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Network;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class NetworkFactory extends Factory
{
    protected $model = Network::class;

    public function definition(): array
    {
        return [
            'name'       => mb_strtolower($this->faker->colorName()),
            'endpoint'   => $this->faker->url(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
