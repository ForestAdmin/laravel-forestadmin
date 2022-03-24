<?php

namespace ForestAdmin\LaravelForestAdmin\Transformers;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Facades\SmartFeatures;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

/**
 * Class BaseTransformer
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class BaseTransformer extends TransformerAbstract
{
    /**
     * @param          $name
     * @param callable $callable
     * @return void
     */
    protected function addMethod($name, callable $callable): void
    {
        $this->$name = $callable;
    }

    /**
     * @param $method
     * @param $arguments
     * @return false|mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array($this->$method, $arguments);
    }

    /**
     * @param Model $model
     * @return mixed
     */
    public function transform(Model $model)
    {
        $smartRelationships = ForestSchema::getSmartRelationships(class_basename($model));
        $model = SmartFeatures::handleSmartFields($model);
        $model = SmartFeatures::handleSmartRelationships($model);
        $relations = collect($model->getRelations())->filter()->all();
        $this->setDefaultIncludes(array_keys($relations));

        foreach ($relations as $key => $value) {
            $resourceKey = $key;
            if (isset($smartRelationships[$key])) {
                //--- force key name as an existing model for include smartRelationship ---//
                $resourceKey = Str::before($smartRelationships[$key]['reference'], '.');
            }
            $this->addMethod('include' . Str::ucfirst($key), fn() => $this->item($value, new ChildTransformer(), Str::ucfirst($resourceKey)));
        }

        return $model->attributesToArray();
    }
}
