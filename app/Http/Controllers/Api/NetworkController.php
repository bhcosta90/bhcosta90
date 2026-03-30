<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api;

use App\Actions\Network\RegisterNetworkHistoryAction;
use App\Data\Network\RegisterNetworkHistoryInput;
use App\Services\NetworkService;

final class NetworkController
{
    public function redirect(NetworkService $networkService, string $name)
    {
        $network = $networkService->byName($name);

        RegisterNetworkHistoryAction::run(new RegisterNetworkHistoryInput(
            networkId: $network->id,
            ipAddress: request()->ip(),
        ));

        if (app()->isLocal()) {
            return $network->endpoint;
        }

        return redirect($network->endpoint);
    }
}
