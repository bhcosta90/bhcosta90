<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => '');
Route::get('/github', fn () => redirect('https://github.com/bhcosta90'));
Route::get('/linkedin', fn () => redirect('https://www.linkedin.com/in/bhcosta90'));
