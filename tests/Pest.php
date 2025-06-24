<?php

declare(strict_types = 1);

use App\Models\Tenant;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

pest()->extend(Tests\TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

function createTenant(): object
{
    tenancy()->initialize($tenant = Tenant::factory()->create());

    $domain = $tenant->domains()->create([
        'domain' => fake()->colorName,
    ]);

    return (object) [
        'tenant' => $tenant,
        'domain' => $domain,
    ];
}
