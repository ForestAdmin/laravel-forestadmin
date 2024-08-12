<?php

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\DatasourceEloquent\EloquentDatasource;

return static function () {
    $defaultDB = config('database.default');
    $forestAgent =  app()->make(AgentFactory::class);

    $forestAgent->addDatasource(
        new EloquentDatasource(config('database.connections.' . $defaultDB)),
    );
};
