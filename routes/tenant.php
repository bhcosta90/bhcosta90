<?php

declare(strict_types = 1);

use App\Http\Controllers\Api\NetworkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Livewire\User\Profile;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::group([
    'prefix'     => '/{tenant}',
    'middleware' => [
        InitializeTenancyByPath::class,
    ],
], function (): void {
    Route::middleware('web')->group(function (): void {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
        Route::get('/user/profile', Profile::class)->name('user.profile');
    });

    Route::middleware('api')->group(function (): void {
        Route::get('{network}', [NetworkController::class, 'redirect']);
    });
});
