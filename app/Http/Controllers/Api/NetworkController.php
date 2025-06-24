<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api;

use App\Actions\Network\RegisterNetworkHistoryAction;
use App\Data\Network\RegisterNetworkHistoryInput;
use App\Models\Network;
use Cache;

final class NetworkController
{
    public function redirect(string $name)
    {
        $network = Cache::remember(Network::CACHE_KEY . '_redirect_' . $name, now()->addDay(), fn () => Network::query()
            ->select(['id', 'endpoint'])
            ->whereName($name)
            ->firstOrFail());

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
