<?php

declare(strict_types = 1);

namespace App\Actions\Network;

use App\Data\Network\RegisterNetworkHistoryInput;
use App\Models\Network;
use App\Models\NetworkHistory;
use DB;
use QuantumTecnology\Actions\AsAction;
use QuantumTecnology\Actions\Contracts\ShouldQueue;

final class RegisterNetworkHistoryAction implements ShouldQueue
{
    use AsAction;

    public function execute(RegisterNetworkHistoryInput $input): NetworkHistory
    {
        return DB::transaction(function () use ($input) {
            $network = Network::whereId($input->networkId)->lockForUpdate()->sole();

            $network->clicks = (string) ((int) $network->clicks + 1);
            $network->save();

            /** @var NetworkHistory $history */
            $history = $network->histories()->create([
                'ip_address' => $input->ipAddress,
            ]);

            return $history;
        });
    }
}
