<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Network;
use App\Models\NetworkHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class NetworkHistoryFactory extends Factory
{
    protected $model = NetworkHistory::class;

    public function definition()
    {
        return [
            'ip_address' => $this->faker->ipv4(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'network_id' => Network::factory(),
        ];
    }
}
