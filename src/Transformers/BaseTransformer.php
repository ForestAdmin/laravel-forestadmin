<?php

namespace ForestAdmin\LaravelForestAdmin\Transformers;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
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
        if (method_exists($model, 'handleSmartFields')) {
            $model->handleSmartFields()->handleSmartRelationships();
        }
        $availableRelations = ForestSchema::getSingleRelationships(strtolower(class_basename($model)));
        $relations = collect($model->getRelations())
            ->filter(fn ($item, $key) => in_array($key, $availableRelations) && ! is_null($item))
            ->all();

        $this->setDefaultIncludes(array_keys($relations));

        foreach ($relations as $key => $value) {
            $this->addMethod('include' . Str::ucfirst($key), fn() => $this->item($value, new ChildTransformer(), class_basename($value)));
        }

        return $model->attributesToArray();
    }
}
