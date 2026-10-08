<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ApiController;

Route::view('/', 'index');
Route::match(['GET', 'POST'], '/api', ApiController::class)->name('api');
Route::match(['GET', 'POST'], '/api.php', ApiController::class)->name('legacy-api-url');
