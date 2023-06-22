<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartAction;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

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
     * @var Model $collection
     */
    protected Model $collection;

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
        $this->collection = Schema::getModel($collection);
        $smartActionName = $this->collection->getSmartAction($name)['methodName'];
        $this->smartAction = $this->collection->{$smartActionName}();

        if ($type = $route->parameter('hook')) {
            return $type === 'load' ? $this->executeLoadHook() : $this->executeChangeHook();
        } else {
            return $this->executeAction();
        }
    }

    /**
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function executeAction(): JsonResponse
    {
        $this->can('smartAction', [$this->collection, Str::slug($this->smartAction->getKey())]);

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
     * @throws \Exception
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
