<?php

use ForestAdmin\LaravelForestAdmin\Http\Controllers\ApiMapsController;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix'     => 'forest',
    ],
    function () {
        Route::get('/', [ApiMapsController::class, 'index']);
        Route::post('authentication', [AuthController::class, 'login'])->name('forest.auth.login');
        Route::get('authentication/callback', [AuthController::class, 'callback'])->name('forest.auth.callback');
        //Route::get('custom-route', fn() => view('welcome'));
    }
);
