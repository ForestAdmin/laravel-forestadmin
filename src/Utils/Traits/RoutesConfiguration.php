<?php

namespace ForestAdmin\LaravelForestAdmin\Utils\Traits;

use ForestAdmin\LaravelForestAdmin\Http\Controllers\ResourcesController;

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
        'forest.collection.index'        => ResourcesController::class,
        'forest.collection.count'        => ResourcesController::class,
        'forest.collection.show'         => ResourcesController::class,
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
