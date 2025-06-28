<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->middleware('api')->group(function (): void {
        Route::redirect('/twitter', 'https://x.com/bhcosta90');
        Route::redirect('/x', 'https://x.com/bhcosta90');
        Route::redirect('/linkedin', 'https://linkedin.com/in/bhcosta90');
        Route::redirect('/github', 'https://github.com.br/bhcosta90');
        Route::redirect('/facebook', 'https://www.facebook.com/bhcosta1990');
        Route::redirect('/fb', 'https://www.facebook.com/bhcosta1990');
        Route::redirect('/instagram', 'https://www.instagram.com/bhcosta90/');
        Route::get('/cv', fn () => Response::make(Storage::drive('local')->get('bhcosta90.pdf'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="bhcosta90.pdf"',
        ]));
    });
}
