<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function collect;
use function request;

/**
 * Class Pie
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Pie extends ChartRepository
{
    /**
     * @param Model $model
     * @throws Exception
     */
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function get(): array
    {
        $groupBy = $this->handleGroupByField(request()->input('group_by_field'));
        if ($this->aggregate === 'count') {
            $this->aggregateField = $groupBy['field'];
        } else {
            $this->aggregateField = $this->table . '.' . $this->aggregateField;
        }

        $query = $this->query()->select(DB::raw($this->aggregate . '(' . $this->aggregateField . ')'), $groupBy['field']);

        if (array_key_exists('relationTable', $groupBy)) {
            $query = $query->join($groupBy['relationTable'], $groupBy['keys'][0], '=', $groupBy['keys'][1]);
        }

        $query = $query->groupBy($groupBy['field'])
            ->get()
            ->mapWithKeys(fn($item, $key) => [Arr::get($item, $groupBy['responseField']) => $item->{$this->aggregate}])
            ->all();

        return $this->serialize($query);
    }

    /**
     * @param $data
     * @return array
     */
    public function serialize($data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = compact('key', 'value');
        }

        return $result;
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
