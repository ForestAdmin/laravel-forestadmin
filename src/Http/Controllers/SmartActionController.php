<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Services\SmartActions\SmartAction;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

        if ($type = $route->parameter('hook')) {
            return $type === 'load' ? $this->executeLoadHook() : $this->executeChangeHook();
        } else {
            return $this->executeAction();
        }
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

    /**
     * @return JsonResponse
     */
    public function executeLoadHook(): JsonResponse
    {
        return response()->json(
            [
                'fields' => array_values(
                    $this->smartAction
                        ->getLoad()
                        ->call($this->smartAction)
                )
            ]
        );
    }

    /**
     * @return JsonResponse
     */
    public function executeChangeHook(): JsonResponse
    {
        return response()->json(
            [
                'fields' => array_values(
                    $this->smartAction
                    ->getChange(request()->input('data.attributes.changed_field'))
                    ->call($this->smartAction->mergeRequestFields(request()->input('data.attributes.fields')))
                )
            ]
        );
    }
}
