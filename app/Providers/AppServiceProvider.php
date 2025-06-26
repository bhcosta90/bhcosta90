<?php

declare(strict_types = 1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->configureHorizon();
    }

    public function boot(): void
    {
        //
    }

    public function configureHorizon(): void
    {
        Gate::define('viewHorizon', function (User $user) {
            return in_array($user->email, [
                'bhcosta90@gmail.com',
                'mayarathc99@gmail.com',
            ], true);
        });

        Horizon::auth(function ($request) {
            return Gate::allows('viewHorizon', $request->user());
        });
    }
}
