<?php

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\DatasourceEloquent\EloquentDatasource;

return static function () {
    $defaultDB = config('database.default');
    app()->make(AgentFactory::class)->addDatasource(
        new EloquentDatasource(
            [
                'driver'   => config('database.connections.' . $defaultDB . '.driver'),
                'host'     => config('database.connections.' . $defaultDB . '.host'),
                'port'     => config('database.connections.' . $defaultDB . '.port'),
                'database' => config('database.connections.' . $defaultDB . '.database'),
                'username' => config('database.connections.' . $defaultDB . '.username'),
                'password' => config('database.connections.' . $defaultDB . '.password'),
                // OR
                // 'url' => config('database.connections.' . $defaultDB . '.url'),
            ]
        ),
    );
};
