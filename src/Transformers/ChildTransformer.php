<?php

namespace ForestAdmin\LaravelForestAdmin\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

/**
 * Class ChildTransformer
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChildTransformer extends TransformerAbstract
{
    /**
     * @param Model $model
     * @return array
     */
    public function transform(Model $model)
    {
        if (method_exists($model, 'handleSmartFields')) {
            $model->handleSmartFields()->handleSmartRelationships();
        }

        return $model->attributesToArray();
    }
}
