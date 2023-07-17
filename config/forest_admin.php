<?php

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\DatasourceEloquent\EloquentDatasource;

return static function () {
    app()->make(AgentFactory::class)->addDatasource(
        new EloquentDatasource(
            [
                'driver'   => env('DB_CONNECTION'),
                'host'     => env('DB_HOST'),
                'port'     => env('DB_PORT'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                // OR
                // 'url' => env('DATABASE_URL'),
            ]
        ),
    );
};
