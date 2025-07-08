<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Network;
use Cache;
use Costa\Services\Service;

final class NetworkService extends Service
{
    public function byName(string $name): Network
    {
        return Cache::remember(
            Network::CACHE_KEY . '_redirect_' . $name,
            app()->isLocal() ? now()->addMinutes() : now()->addDay(),
            fn () => $this
                ->model()
                ->query()
                ->byName($name)
                ->sole(['id', 'endpoint']),
        );
    }

    protected function model(): Network
    {
        return new Network();
    }
}
