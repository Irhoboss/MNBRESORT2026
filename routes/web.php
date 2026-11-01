<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ApiController;

Route::view('/', 'index');
Route::view('/privacy', 'legal', ['page' => 'privacy'])->name('privacy');
Route::view('/terms', 'legal', ['page' => 'terms'])->name('terms');
Route::view('/refund-policy', 'legal', ['page' => 'refund'])->name('refund-policy');
Route::view('/cookie-policy', 'legal', ['page' => 'cookies'])->name('cookie-policy');
Route::match(['GET', 'POST'], '/api', ApiController::class)->middleware('throttle:120,1')->name('api');
Route::match(['GET', 'POST'], '/api.php', ApiController::class)->middleware('throttle:120,1')->name('legacy-api-url');
