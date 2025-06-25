<?php

declare(strict_types = 1);

use App\Http\Controllers\Api\NetworkController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->middleware('api')->group(function (): void {
        Route::redirect('/twitter', 'https://x.com/bhcosta90');
        Route::redirect('/x', 'https://x.com/bhcosta90');
        Route::redirect('/linkedin', 'https://linkedin.com/in/bhcosta90');
        Route::redirect('/github', 'https://github.com.br/bhcosta90');
        Route::redirect('/facebook', 'https://www.facebook.com/bhcosta1990');
        Route::redirect('/fb', 'https://www.facebook.com/bhcosta1990');
        Route::redirect('/instagram', 'https://www.instagram.com/bhcosta90/');
        Route::redirect('/google', 'https://www.google.com.br/');
    });
}

Route::group([
    'prefix'     => '/{tenant}',
    'middleware' => [
        'api',
        InitializeTenancyByPath::class,
    ],
], function (): void {
    Route::get('{network}', [NetworkController::class, 'redirect']);
});
