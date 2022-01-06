<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class BelongsToUpdator
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class BelongsToUpdator extends BaseRepository
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
     * @param $id
     * @return void
     */
    public function updateRelation($id)
    {
        $relation = $this->parentInstance->{$this->relation}();
        $record = $relation->getRelated()->find($id);

        if (!$record) {
            return $this->throwException('Record not found');
        }

        try {
            switch (get_class($relation)) {
                case BelongsTo::class:
                    $relation->associate($record);
                    $this->parentInstance->save();
                    break;
                case HasOne::class:
                    $relation->save($record);
                    break;
            }
        } catch (\Exception $e) {
            return $this->throwException('The record can not be updated');
        }
    }
}
