<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function (): void {
        Route::get('/', fn (): string => '')->name('welcome');
    });
}

require __DIR__ . '/auth.php';
