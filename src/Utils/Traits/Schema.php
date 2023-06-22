<?php

namespace ForestAdmin\LaravelForestAdmin\Utils\Traits;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Schema
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait Schema
{
    /**
     * @param string $collection
     * @return Model
     */
    public static function getModel(string $collection): Model
    {
        try {
            $model = app()->make(ForestSchema::getClass($collection));
        } catch (\Exception $e) {
            throw new ForestException("No model found for collection $collection");
        }

        return $model;
    }
}
