<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class HasManyGetter
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasManyGetter extends ResourceGetter
{
    /**
     * @var string
     */
    protected string $relation;

    /**
     * @var string
     */
    protected string $relationName;

    /**
     * @var Model
     */
    protected Model $parentInstance;

    /**
     * @param Model  $model
     * @param string $relation
     * @param        $parentId
     * @throws \Exception
     */
    public function __construct(Model $model, string $relation, $parentId)
    {
        parent::__construct($model);
        $this->relation = $relation;
        $this->parentInstance = $this->model->find($parentId);
    }

    /**
     * @param bool $paginate
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function all(bool $paginate = true): LengthAwarePaginator
    {
        $smartRelationships = ForestSchema::getSmartRelationships(class_basename($this->model));
        if (isset($smartRelationships[$this->relation])) {
            return call_user_func($this->model->{$this->relation}()->get, $this->parentInstance->id);
        }

        $relatedModel = $this->parentInstance->{$this->relation}()->getRelated();
        $pageParams = $this->params['page'] ?? [];
        $relation = $this->parentInstance->{$this->relation}();
        $query = QueryBuilder::of($relatedModel, $this->params);

        switch (get_class($relation)) {
            case HasMany::class:
                $query->where($relation->getForeignKeyName(), $this->parentInstance->getKey());
                break;
            case BelongsToMany::class:
                $query->join($relation->getTable(), $relation->getTable() . '.' . $relation->getRelatedPivotKeyName(), '=', $relatedModel->getTable() . '.' . $relation->getRelatedKeyName());
                $query->where($relation->getTable() . '.' . $relation->getForeignPivotKeyName(), $this->parentInstance->getKey());
                break;
        }

        return $query->paginate($pageParams['size'] ?? null, '*', 'page', $pageParams['number'] ?? null);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->parentInstance->{$this->relation}()->count();
    }
}
