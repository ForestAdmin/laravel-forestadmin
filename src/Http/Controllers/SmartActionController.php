<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Services\SmartActions\SmartAction;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Route;


/**
 * Class SmartActionController
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartActionController extends ForestController
{
    /**
     * @var Model $model
     */
    protected Model $model;

    /**
     * @var SmartAction
     */
    protected SmartAction $smartAction;

    /**
     * @param Route $route
     * @return JsonResponse
     * @throws \Exception
     */
    public function __invoke(Route $route)
    {
        [$collection, $name] = explode('_', $route->parameter('action'));
        $this->model = Schema::getModel($collection);
        $this->smartAction = $this->model->getSmartAction($name);

        return $this->executeAction();
    }

    /**
     * @return JsonResponse
     */
    public function executeAction(): JsonResponse
    {
        //$this->authorize('smartAction', $this->collection);

        return response()->json(
            call_user_func($this->smartAction->getExecute())
        );

    }
}
