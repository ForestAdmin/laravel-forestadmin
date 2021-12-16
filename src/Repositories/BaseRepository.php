<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\HasIncludes;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\Relationships;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\ArrayHelper;
use Illuminate\Database\Eloquent\Model;

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
    use HasIncludes;
    use ArrayHelper;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @var string|null
     */
    protected ?string $database = null;

    /**
     * @param Model  $model
     * @param string $name
     */
    public function __construct(Model $model, string $name)
    {
        $this->model = $model;
        $this->name = $name;
        $this->table = $model->getConnection()->getTablePrefix() . $model->getTable();
        if (strpos($this->table, '.')) {
            [$this->database, $this->table] = explode('.', $this->table);
        }
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
        $attributes = $data['attributes'];
        $relationships = $data['relationships'] ?? [];
        foreach ($attributes as $key => $value) {
            $model->$key = $value;
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
