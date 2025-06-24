<?php

declare(strict_types = 1);

namespace App\Data\Network;

final class RegisterNetworkHistoryInput
{
    public function __construct(
        public string $networkId,
        public string $ipAddress,
    ) {
    }
}
