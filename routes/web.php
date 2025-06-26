<?php

declare(strict_types = 1);

use App\Livewire\User\Profile;
use App\Livewire\Users\Index;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function (): void {
        Route::get('/', fn (): string => '');
        Route::view('/dashboard', 'dashboard');

        Route::get('/me', function () {
            return auth()->check()
                ? 'Logado como ' . auth()->user()->email
                : 'Não está logado';
        });
    });
}

Route::middleware(['auth'])->group(function (): void {
    Route::get('/users', Index::class)->name('users.index');
    Route::get('/user/profile', Profile::class)->name('user.profile');
});

require __DIR__ . '/auth.php';
