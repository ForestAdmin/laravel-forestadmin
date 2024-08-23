<?php

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\DatasourceEloquent\EloquentDatasource;
use ForestAdmin\LaravelForestAdmin\Providers\AgentProvider;

return static function () {
    $defaultDB = config('database.default');
    $forestAgent =  AgentProvider::getAgentInstance();

    $forestAgent->addDatasource(
        new EloquentDatasource(config('database.connections.' . $defaultDB)),
    );

    $forestAgent->build();
};
