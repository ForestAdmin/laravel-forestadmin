<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SmartFeaturesHandler
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartFeaturesHandler
{
    /**
     * @param Model $model
     * @return Model
     */
    public function handleSmartFields(Model $model): Model
    {
        $smartFields = ForestSchema::getSmartFields(strtolower(class_basename($model)));
        foreach ($smartFields as $smartField) {
            $model->{$smartField['field']} = call_user_func($model->{$smartField['field']}()->get);
        }

        return $model;
    }

    /**
     * @param Model $model
     * @return Model
     */
    public function handleSmartRelationships(Model $model): Model
    {
        $smartRelationships = ForestSchema::getSmartRelationships(strtolower(class_basename($model)));
        foreach ($smartRelationships as $smartRelationship) {
            $model->setRelation($smartRelationship['field'], call_user_func($model->{$smartRelationship['field']}()->get));
        }

        return $model;
    }
}
