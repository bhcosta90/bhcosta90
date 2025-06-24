<?php

declare(strict_types = 1);

use App\Actions\Network\RegisterNetworkHistoryAction;
use App\Data\Network\RegisterNetworkHistoryInput;
use App\Models\Network;
use App\Models\NetworkHistory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

test('it registers a network history record', function (): void {
    createTenant();

    $network = Network::factory()->create();

    app(RegisterNetworkHistoryAction::class)->execute(new RegisterNetworkHistoryInput(
        networkId: $network->id,
        ipAddress: '127.0.0.1'
    ));

    assertDatabaseCount(NetworkHistory::class, 1);
    assertDatabaseHas(NetworkHistory::class, [
        'network_id' => $network->id,
        'ip_address' => '127.0.0.1',
    ]);
});
