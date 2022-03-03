<?php

use ForestAdmin\LaravelForestAdmin\Http\Controllers\ApiMapsController;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\AuthController;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\DispatchGateway;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\SmartActionController;
use ForestAdmin\LaravelForestAdmin\Http\Middleware\ForestAuthorization;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix'     => 'forest',
    ],
    function () {
        Route::get('/', [ApiMapsController::class, 'index']);
        Route::post('authentication', [AuthController::class, 'login'])->name('forest.auth.login');
        Route::get('authentication/callback', [AuthController::class, 'callback'])->name('forest.auth.callback');

        Route::group(
            [
                'middleware' => [ForestAuthorization::class]
            ],
            function () {
                // STATS
                Route::post('/stats/{collection}', DispatchGateway::class)->name('forest.stats.index');
                Route::post('/stats', DispatchGateway::class)->name('forest.stats.live_query');

                // SCOPES
                Route::post('/scope-cache-invalidation', DispatchGateway::class)->name('forest.scopes.index');

                // CRUD
                Route::get('/{collection}', DispatchGateway::class)->name('forest.collection.index');
                Route::get('/{collection}/count', DispatchGateway::class)->name('forest.collection.count');
                Route::get('/{collection}/{id}', DispatchGateway::class)->name('forest.collection.show');
                Route::post('/{collection}', DispatchGateway::class)->name('forest.collection.store');
                Route::put('/{collection}/{id}', DispatchGateway::class)->name('forest.collection.update');
                Route::delete('/{collection}', DispatchGateway::class)->name('forest.collection.destroy_bulk');
                Route::delete('/{collection}/{id}', DispatchGateway::class)->name('forest.collection.destroy');

                // ASSOCIATIONS
                Route::get('/{collection}/{id}/relationships/{association_name}', DispatchGateway::class)->name('forest.relationships.index');
                Route::post('/{collection}/{id}/relationships/{association_name}', DispatchGateway::class)->name('forest.relationships.associate');
                Route::put('/{collection}/{id}/relationships/{association_name}', DispatchGateway::class)->name('forest.relationships.update');
                Route::delete('/{collection}/{id}/relationships/{association_name}', DispatchGateway::class)->name('forest.relationships.dissociate');
                Route::get('/{collection}/{id}/relationships/{association_name}/count', DispatchGateway::class)->name('forest.relationships.count');

                // SMART ACTIONS
                Route::post('/smart-actions/{action}', SmartActionController::class);
                Route::post('/smart-actions/{action}/hooks/load', SmartActionController::class);
                Route::post('/smart-actions/{action}/hooks/change', SmartActionController::class);
            }
        );
    }
);
