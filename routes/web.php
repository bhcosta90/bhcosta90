<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => '');
Route::get('/github', fn () => redirect('https://github.com/bhcosta90'));
