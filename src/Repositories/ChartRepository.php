<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\DatabaseHelper;
use ForestAdmin\LaravelForestAdmin\Services\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Class ChartRepository
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
abstract class ChartRepository extends BaseRepository
{
    use DatabaseHelper;

    /**
     * @var array
     */
    protected array $params;

    /**
     * @var string
     */
    protected string $aggregate;

    /**
     * @var string
     */
    protected string $aggregateField;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        parent::__construct($model);
        $this->params = request()->except('type', 'collection', 'aggregate', 'aggregate_field');
        $this->aggregate = Str::lower(request()->input('aggregate'));
        $this->aggregateField = request()->input('aggregate_field', '*');
        $this->table = $model->getConnection()->getTablePrefix() . $model->getTable();
        if (strpos($this->table, '.')) {
            [$this->database, $this->table] = explode('.', $this->table);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function get(): array
    {
        $query = $this->query()->{$this->aggregate}($this->aggregateField);

        return $this->serialize($query);
    }

    /**
     * @param mixed $data
     * @return array
     */
    abstract public function serialize($data): array;

    /**
     * @return Builder
     * @throws Exception
     */
    protected function query(): Builder
    {
        return QueryBuilder::of($this->model, $this->params);
    }

    /**
     * @param string $dataField
     * @return array
     * @throws Exception
     */
    protected function handleGroupByField(string $dataField): array
    {
        if (Str::contains($dataField, ':')) {
            $parseField = explode(':', $dataField);
            $relationName = $parseField[0];
            [$relationTable, $keys, $field] = $this->fetchFieldsOnRelation($relationName, $parseField[1]);
            return [
                'relationTable' => $relationTable,
                'keys'          => $keys,
                'field'         => $field,
                'responseField' => $parseField[1],
            ];
        } else {
            $field = $this->handleField($this->model, $dataField);
            $responseField = $dataField;
            return compact('field', 'responseField');
        }
    }

    /**
     * @param string $relationName
     * @param string $dataField
     * @return array
     * @throws Exception
     */
    protected function fetchFieldsOnRelation(string $relationName, string $dataField): array
    {
        $relations = $this->getRelations($this->model);
        $relationsName = collect(array_keys($relations));

        if (!$relationsName->contains($relationName)) {
            throw new ForestException("Unknown relation $relationName");
        }

        $relation = $this->model->$relationName();
        $field = $this->handleField($relation->getRelated(), $dataField);

        switch (get_class($relation)) {
            case BelongsTo::class:
                $keys = [$this->table . '.' . $relation->getForeignKeyName(), $relation->getRelated()->getTable() . '.' . $relation->getOwnerKeyName()];
                break;
            case HasOne::class:
                $keys = [$relation->getRelated()->getTable() . '.' . $relation->getForeignKeyName(), $this->table . '.' . $relation->getLocalKeyName()];
                break;
            default:
                throw new ForestException("Unsupported relation to this chart");
        }

        return [$relation->getRelated()->getTable(), $keys, $field];
    }

    /**
     * @param Model  $model
     * @param string $dataField
     * @return string
     * @throws Exception
     */
    protected function handleField(Model $model, string $dataField): string
    {
        $table = $model->getTable();
        $field = null;
        $columnsKeys = collect(array_keys($this->getColumns($model)));
        if ($columnsKeys->contains($dataField)) {
            $field = $table . '.' . $dataField;
        }

        if (!$field) {
            throw new ForestException("The field $dataField doesn't exist in the table $table");
        }

        return $field;
    }
}
