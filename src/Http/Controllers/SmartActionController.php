<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartAction;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

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
     * @var SmartAction
     */
    protected SmartAction $smartAction;

    /**
     * @return JsonResponse
     */
    public function load(): JsonResponse
    {
        $this->setSmartAction();

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
    public function change(): JsonResponse
    {
        $this->setSmartAction();

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

    private function setSmartAction()
    {
        [$collection, $name] = explode('.', Route::getCurrentRoute()->wheres['id']);
        /** @var Model $collection */
        $collection = Schema::getModel($collection);
        $smartActionName = $collection->getSmartAction($name)['methodName'];
        $this->smartAction = $collection->{$smartActionName}();
    }
}
