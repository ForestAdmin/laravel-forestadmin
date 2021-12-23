<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * Class HasManyDissociator
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasManyDissociator extends BaseRepository
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
     * @param string $name
     * @param string $relation
     * @param        $parentId
     */
    public function __construct(Model $model, string $name, string $relation, $parentId)
    {
        parent::__construct($model, $name);
        $this->relation = $relation;
        $this->parentInstance = $this->model->find($parentId);
    }

    /**
     * @param      $ids
     * @param bool $delete
     * @return void
     */
    public function removeRelation($ids, bool $delete = false)
    {
        $relation = $this->parentInstance->{$this->relation}();
        $records = $relation->get()->whereIn($relation->getRelated()->getKeyName(), $ids);
        if ($records->count() === 0) {
            return $this->throwException('Record dissociate error: records not found');
        }

        if ($delete) {
            $remover = new ResourceRemover($relation->getRelated(), $this->relation);
            return $remover->destroyBulk($records->pluck('id'));
        }

        try {
            switch (get_class($relation)) {
                case HasMany::class:
                    DB::table($relation->getRelated()->getTable())
                        ->whereIn($relation->getRelated()->getKeyName(), $ids)
                        ->where($relation->getForeignKeyName(), $this->parentInstance->getKey())
                        ->update([$relation->getForeignKeyName() => null]);
                    break;
                case MorphMany::class:
                    DB::table($relation->getRelated()->getTable())
                        ->whereIn($relation->getRelated()->getKeyName(), $records->pluck('id'))
                        ->update(
                            [
                                $relation->getMorphType()      => null,
                                $relation->getForeignKeyName() => null,
                            ]
                        );
                    break;
                case BelongsToMany::class:
                    $relation->detach($records->pluck('id'));
                    break;
            }
        } catch (\Exception $e) {
            return $this->throwException('Record dissociate error: ' . $e->getMessage());
        }
    }
}
