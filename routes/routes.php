<?php

use ForestAdmin\LaravelForestAdmin\Http\Controllers\ApiMapsController;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\AuthController;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\DispatchGateway;
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

        // CRUD
        Route::get('/{collection}', DispatchGateway::class)->name('forest.collection.index');
        Route::get('/{collection}/count', DispatchGateway::class)->name('forest.collection.count');
        Route::get('/{collection}/{id}', DispatchGateway::class)->name('forest.collection.show');

        // ASSOCIATIONS
        Route::get('/{collection}/{id}/relationships/{association_name}', DispatchGateway::class)->name('forest.relationships.index');
        Route::get('/{collection}/{id}/relationships/{association_name}/count', DispatchGateway::class)->name('forest.relationships.count');
    }
);
