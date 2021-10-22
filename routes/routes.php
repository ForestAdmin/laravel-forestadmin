<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'middleware' => 'forestCors',
        'prefix'     => config('forest.route_prefix'),
    ], function () {
        //
    }
);
