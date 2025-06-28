<?php

declare(strict_types = 1);

use App\Livewire\User\Profile;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function (): void {
        Route::get('/', fn (): string => '')->name('welcome');
    });
}

Route::middleware(['auth'])->group(function (): void {
    Route::get('/user/profile', Profile::class)->name('user.profile');
});

require __DIR__ . '/auth.php';
