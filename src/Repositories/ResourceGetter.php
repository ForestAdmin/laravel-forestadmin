<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

/**
 * Class ResourceGetter
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceGetter extends BaseRepository
{
    /**
     * @var array
     */
    protected array $params;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->params = request()->query();
        parent::__construct($model);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function all()
    {
        $pageParams = $this->params['page'] ?? [];
        $resources = $this->query()->paginate(
            $pageParams['size'] ?? null,
            '*',
            'page',
            $pageParams['number'] ?? null
        );
        if (!$resources) {
            $this->throwException('Collection not found');
        }

        return JsonApi::render($resources, $this->name);
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public function get($id): array
    {
        $resource = $this->query()->find($id);
        if (!$resource) {
            $this->throwException('Collection not found');
        }

        return JsonApi::render($this->query()->find($id), $this->name);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * @return Builder
     * @throws Exception
     */
    protected function query(): Builder
    {
        $params = $this->params['fields'] ?? [];
        $queryFields = $params[Str::camel($this->name)] ?? null;

        $fields = $this->handleFields($this->model, $queryFields);
        $query = $this->model->query()->select($fields);

        if ($joins = $this->handleWith($this->model, $params)) {
            foreach ($joins as $key => $value) {
                if ($value['foreign_key']) {
                    $query->addSelect($value['foreign_key']);
                }
                $query->with($key . ':' . $value['fields']);
            }
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
            $connexion = $model->getConnection()->getDoctrineSchemaManager();
            $columns = $connexion->listTableColumns($table, $this->database);
            $columnsKeys = collect(array_keys($columns));
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
                    case MorphOne::class:
                        $foreignKey = $relation->getRelated()->getTable() . '.' . $relation->getForeignKeyName();
                        $morphType = $relation->getMorphType();
                        $this->addInclude($key, $this->mergeArray($fields, [$foreignKey, $morphType]));
                        break;
                }
            }
        }

        return $this->getIncludes();
    }
}
