<?php

declare(strict_types = 1);

use App\Actions\Network\RegisterNetworkHistoryAction;
use App\Models\Network;

use App\Models\NetworkHistory;

use function Pest\Laravel\assertDatabaseCount;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

use QuantumTecnology\Actions\Job\ActionJob;

test('redirects to the network endpoint for a valid network name', function (): void {
    $network = Network::factory()->create();

    get('/' . $this->tenant->tenant->id . '/' . $network->name)
        ->assertRedirect($network->endpoint);

    assertDatabaseCount(NetworkHistory::class, 1);
    assertDatabaseHas(NetworkHistory::class, [
        'network_id' => $network->id,
        'ip_address' => request()->ip(),
    ]);
})->skip();

test('dispatches RegisterNetworkHistoryAction job on redirect', function (): void {
    Illuminate\Support\Facades\Bus::fake([ActionJob::class]);

    $network = Network::factory()->create();

    get('/' . $this->tenant->tenant->id . '/' . $network->name)
        ->assertRedirect($network->endpoint);

    Bus::assertDispatched(ActionJob::class, fn ($data): bool => RegisterNetworkHistoryAction::class === $data->action::class
        && $data->arguments['input']->networkId === $network->id
        && $data->arguments['input']->ipAddress === request()->ip());
})->skip();

test('returns 404 for an invalid network name', function (): void {
    get('/' . $this->tenant->tenant->id . '/invalid-network')
        ->assertNotFound();
})->skip();

test('returns 500 for an invalid tenant id', function (): void {
    $network = Network::factory()->create();

    get('/invalid-tenant-id/' . $network->name)
        ->assertServerError();
})->skip();
