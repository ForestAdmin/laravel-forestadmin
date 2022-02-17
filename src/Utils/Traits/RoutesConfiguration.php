<?php

namespace ForestAdmin\LaravelForestAdmin\Utils\Traits;

use ForestAdmin\LaravelForestAdmin\Http\Controllers\ChartsController;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\RelationshipsController;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\ResourcesController;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\ScopesController;

/**
 * Class RoutesConfiguration
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait RoutesConfiguration
{
    public array $routesList = [
        'forest.collection.index'         => ResourcesController::class,
        'forest.collection.count'         => ResourcesController::class,
        'forest.collection.show'          => ResourcesController::class,
        'forest.collection.store'         => ResourcesController::class,
        'forest.collection.update'        => ResourcesController::class,
        'forest.collection.destroy_bulk'  => ResourcesController::class,
        'forest.collection.destroy'       => ResourcesController::class,
        'forest.relationships.index'      => RelationshipsController::class,
        'forest.relationships.count'      => RelationshipsController::class,
        'forest.relationships.associate'  => RelationshipsController::class,
        'forest.relationships.update'     => RelationshipsController::class,
        'forest.relationships.dissociate' => RelationshipsController::class,
        'forest.stats.index'              => ChartsController::class,
        'forest.stats.live_query'         => ChartsController::class,
        'forest.scopes.index'             => ScopesController::class,
    ];

    /**
     * @param string $route
     * @return string
     */
    public function getController(string $route): string
    {
        return $this->routesList[$route];
    }
}
