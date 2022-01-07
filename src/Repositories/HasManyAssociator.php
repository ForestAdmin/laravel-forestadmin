<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class HasManyAssociator
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasManyAssociator extends BaseRepository
{
    /**
     * @var string
     */
    protected string $relation;

    /**
     * @var Model
     */
    protected Model $parentInstance;

    /**
     * @param Model  $model
     * @param string $relation
     * @param        $parentId
     */
    public function __construct(Model $model, string $relation, $parentId)
    {
        parent::__construct($model);
        $this->relation = $relation;
        $this->parentInstance = $this->model->find($parentId);
    }

    /**
     * @param $ids
     * @return void
     */
    public function addRelation($ids): void
    {
        $relation = $this->parentInstance->{$this->relation}();
        switch (get_class($relation)) {
            case HasMany::class:
            case MorphMany::class:
                $records = $relation->getRelated()->findMany($ids);
                $relation->saveMany($records->all());
                break;
            case BelongsToMany::class:
                $relation->attach($ids);
                break;
        }
    }
}
