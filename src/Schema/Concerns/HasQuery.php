<?php

namespace ForestAdmin\LaravelForestAdmin\Schema\Concerns;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * Class HasQuery
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait HasQuery
{
    /**
     * @param $model
     * @return Builder
     * @throws Exception
     */
    protected function buildQuery($model): Builder
    {
        $query = $model->query();

        $name = Str::camel(class_basename($model));
        $fieldsParams = $this->params['fields'] ?? [];
        $queryFields = $fieldsParams[$name] ?? null;

        $fields = $this->handleFields($model, $queryFields);
        $query->select($fields);

        if ($includes = $this->handleWith($model, $fieldsParams)) {
            $this->appendIncludes($query, $includes);
        }

        if (array_key_exists('search', $this->params)) {
            $isExtended = array_key_exists('searchExtended', $this->params) && (int) $this->params['searchExtended'] === 1;
            $this->appendSearch($query, $this->params['search'], $isExtended);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param         $search
     * @param bool    $isExtended
     * @return void
     */
    protected function appendSearch(Builder $query, $search, bool $isExtended = false)
    {
        $model = $query->getModel();
        if ($isExtended) {
            $singleRelations = $this->getSingleRelations($model);
            foreach ($singleRelations as $key => $value) {
                $relatedModel = $model->$key()->getRelated();
                $fieldsToSearch = $this->getFieldsToSearch($relatedModel);
                $query->orWhereHas(
                    $key,
                    function ($query) use ($fieldsToSearch, $search, $relatedModel) {
                        $query->where(
                            function ($query) use ($fieldsToSearch, $search, $relatedModel) {
                                foreach ($fieldsToSearch as $field) {
                                    $this->handleSearchField($query, $relatedModel, $field, $search);
                                }
                            }
                        );
                    }
                );
            }
        } else {
            $fieldsToSearch = $this->getFieldsToSearch($model);
            $query->where(
                function ($query) use ($fieldsToSearch, $search, $model) {
                    foreach ($fieldsToSearch as $field) {
                        $this->handleSearchField($query, $model, $field, $search);
                    }
                }
            );
        }
    }

    /**
     * @param Builder $query
     * @param array   $includes
     * @return Builder
     */
    protected function appendIncludes(Builder $query, array $includes): Builder
    {
        foreach ($includes as $key => $value) {
            if ($value['foreign_key']) {
                $query->addSelect($value['foreign_key']);
            }
            $query->with($key . ':' . $value['fields']);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param Model   $model
     * @param array   $field
     * @param         $value
     * @return Builder
     */
    protected function handleSearchField(Builder $query, Model $model, array $field, $value)
    {
        $name = $model->getTable() . '.' . $field['field'];
        if ($field['type'] === 'Number') {
            if ($this->isNumber($value)) {
                $query->orWhere($name, (int) $value);
            }
        } elseif ($field['type'] === 'Enum' || $this->isUuid($value)) {
            $query->orWhere($name, $value);
        } else {
            $query->orWhereRaw("LOWER ($name) LIKE LOWER(?)", ['%' . $value . '%']);
        }

        return $query;
    }

    /**
     * @param Model $model
     * @return array
     */
    protected function getFieldsToSearch(Model $model): array
    {
        $fieldsToSearch = [];
        $fields = ForestSchema::getFields(class_basename($model));
        foreach ($fields as $field) {
            if (in_array($field['type'], ['String', 'Number', 'Enum'], true) && !$field['reference'] && !$field['is_virtual'] && $this->fieldInSearchFields($model, $field['field'])) {
                $fieldsToSearch[] = $field;
            }
        }

        return $fieldsToSearch;
    }

    /**
     * @param Model  $model
     * @param string $field
     * @return bool
     */
    protected function fieldInSearchFields(Model $model, string $field): bool
    {
        return method_exists($model, 'searchFields') === false
            || empty($model->searchFields())
            || in_array($field, $model->searchFields(), true);
    }

    /**
     * @param Model       $model
     * @param string|null $queryFields
     * @return array
     * @throws Exception
     */
    protected function handleFields(Model $model, ?string $queryFields = null): array
    {
        $table = $model->getTable();
        $fields = [];

        if (!empty($queryFields)) {
            $columnsKeys = collect(array_keys($this->getColumns($model)));
            foreach (explode(',', $queryFields) as $params) {
                if ($columnsKeys->contains($params)) {
                    $fields[] = $table . '.' . $params;
                }
            }
            if (!in_array($table . '.' . $model->getKeyName(), $fields, true)) {
                $fields[] = $table . '.' . $model->getKeyName();
            }
        } else {
            $fields = [$table . '.*'];
        }

        return $fields;
    }

    /**
     * @param Model      $model
     * @param array|null $params
     * @return array
     * @throws Exception
     */
    protected function handleWith(Model $model, ?array $params = []): array
    {
        $relations = $this->getRelations($model);
        $relationsName = collect(array_keys($relations));

        foreach ($params as $key => $value) {
            if ($relationsName->contains($key)) {
                $relation = $model->$key();
                $fields = $this->handleFields($relation->getRelated(), $value);

                switch (get_class($relation)) {
                    case BelongsTo::class:
                        $ownerKey = $relation->getRelated()->getTable() . '.' . $relation->getOwnerKeyName();
                        $this->addInclude($key, $this->mergeArray($fields, $ownerKey), $model->getTable() . '.' . $relation->getForeignKeyName());
                        break;
                    case HasOne::class:
                        $foreignKey = $relation->getRelated()->getTable() . '.' . $relation->getForeignKeyName();
                        $this->addInclude($key, $this->mergeArray($fields, $foreignKey));
                        break;
                }
            }
        }

        return $this->getIncludes();
    }

    /**
     * @param Model $model
     * @return array
     * @throws Exception
     */
    public function getColumns(Model $model): array
    {
        $connexion = $model->getConnection()->getDoctrineSchemaManager();
        $columns = $connexion->listTableColumns($model->getTable(), $this->database);

        return $columns;
    }

    /**
     * @param $value
     * @return bool
     */
    public function isNumber($value): bool
    {
        return (int) $value > 0;
    }

    /**
     * @param $value
     * @return bool
     */
    public function isUuid($value): bool
    {
        return Uuid::isValid($value);
    }
}
