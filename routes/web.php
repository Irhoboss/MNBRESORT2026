<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\GoogleAuthController;

Route::view('/', 'index');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::view('/privacy', 'legal', ['page' => 'privacy'])->name('privacy');
Route::view('/terms', 'legal', ['page' => 'terms'])->name('terms');
Route::view('/refund-policy', 'legal', ['page' => 'refund'])->name('refund-policy');
Route::view('/cookie-policy', 'legal', ['page' => 'cookies'])->name('cookie-policy');
Route::match(['GET', 'POST'], '/api', ApiController::class)->middleware('throttle:120,1')->name('api');
Route::match(['GET', 'POST'], '/api.php', ApiController::class)->middleware('throttle:120,1')->name('legacy-api-url');
