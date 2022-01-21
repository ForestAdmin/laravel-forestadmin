<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\Relationships;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasFilters;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasIncludes;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasSearch;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\ArrayHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Class QueryBuilder
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class QueryBuilder
{
    use ArrayHelper;
    use HasFilters;
    use HasIncludes;
    use HasSearch;
    use Relationships;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var array
     */
    protected array $params;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @var string|null
     */
    protected ?string $database = null;

    /**
     * @param Model      $model
     * @param array|null $params
     */
    public function __construct(Model $model, ?array $params = [])
    {
        $this->model = $model;
        $this->params = $params;
        $this->table = $model->getConnection()->getTablePrefix() . $model->getTable();
        if (strpos($this->table, '.')) {
            [$this->database, $this->table] = explode('.', $this->table);
        }
    }

    /**
     * @param            $model
     * @param array|null $params
     * @return Builder
     * @throws Exception
     */
    public static function of($model, ?array $params = []): Builder
    {
        return (new static($model, $params))->query();
    }

    /**
     * @return Builder
     * @throws Exception
     */
    public function query(): Builder
    {
        $query = $this->model->query();
        $name = Str::camel(class_basename($this->model));
        $fieldsParams = $this->params['fields'] ?? [];
        $queryFields = $fieldsParams[$name] ?? null;

        $fields = $this->handleFields($this->model, $queryFields);
        $query->select($fields);

        if ($includes = $this->handleWith($this->model, $fieldsParams)) {
            $this->appendIncludes($query, $includes);
        }

        if (array_key_exists('search', $this->params)) {
            $isExtended = array_key_exists('searchExtended', $this->params) && (int) $this->params['searchExtended'] === 1;
            $this->appendSearch($query, $this->params['search'], $isExtended);
        }

        if (array_key_exists('filters', $this->params)) {
            $this->appendFilters($query, $this->params['filters']);
        }

        return $query;
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
}
