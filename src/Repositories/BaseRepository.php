<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\Relationships;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\ArrayHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BaseRepository
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
abstract class BaseRepository
{
    use Relationships;
    use ArrayHelper;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param $message
     * @return void
     */
    public function throwException($message): void
    {
        throw new ForestException($message);
    }

    /**
     * @param $model
     * @param $data
     * @return void
     */
    protected function setAttributes($model, $data): void
    {
        $smartFields = ForestSchema::getSmartFields(strtolower(class_basename($model)));
        $attributes = $data['attributes'];
        $relationships = $data['relationships'] ?? [];
        foreach ($attributes as $key => $value) {
            if (isset($smartFields[$key])) {
                $model = $model
                    ->{$key}()
                    ->set
                    ->call($model, $value);
            } else {
                $model->$key = $value;
            }
        }

        foreach ($relationships as $key => $value) {
            $relation = $model->$key();
            $attributes = $value['data'];
            if ($relation instanceof BelongsTo && array_key_exists($relation->getOwnerKeyName(), $attributes)) {
                $related = $relation->getRelated()->firstWhere($relation->getOwnerKeyName(), $attributes[$relation->getOwnerKeyName()]);
                $model->$key()->associate($related);
            }
        }
    }
}
