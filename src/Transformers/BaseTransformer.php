<?php

namespace ForestAdmin\LaravelForestAdmin\Transformers;

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
     * @var array
     */
    //protected array $fields;

    /**
     * @param array $fields
     */
    /*public function __construct(array $fields)
    {
        $this->fields = $fields;
    }*/

    /**
     * @param          $name
     * @param callable $callable
     * @return void
     */
    public function addMethod($name, callable $callable): void
    {
        $this->{$name} = $callable;
    }

    /**
     * @param $method
     * @param $arguments
     * @return false|mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array($this->{$method}, $arguments);
    }

    /**
     * @param Model $model
     * @return mixed
     */
    public function transform(Model $model)
    {
        $relations = $model->getRelations();
        $this->setDefaultIncludes(array_keys($relations));

        foreach ($relations as $key => $value) {
            $this->addMethod('include' . Str::ucfirst($key), fn() => $this->item($value, new ChildTransformer(), Str::ucfirst($key)));
        }

        return $model->toArray();
    }
}
