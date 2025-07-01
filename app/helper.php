<?php

declare(strict_types = 1);

if (!function_exists('tenantRoute')) {
    function tenantRoute($route, $params = []): string
    {
        return route($route, $params + ['tenant' => tenant('id')]);
    }
}
