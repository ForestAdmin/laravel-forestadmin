<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use App\Models\Book;
use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\HasIncludes;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\Relationships;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\ArrayHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class BaseRepository
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class BaseRepository
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
     * @var Builder
     */
    private Builder $query;

    /**
     * @param Model $model
     * @throws Exception
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->name = (class_basename($this->model));
        $this->table = $model->getConnection()->getTablePrefix() . $model->getTable();
        if (strpos($this->table, '.')) {
            [$this->database, $this->table] = explode('.', $this->table);
        }

        $this->includes = new Collection();
        $this->build();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function build(): void
    {
        $params = request()->query()['fields'] ?? [];
        $fields = $this->handleFields($this->model, explode(',', $params[Str::camel($this->name)]));
        $this->query = $this->model->query()->select($fields);

        if ($joins = $this->handleWith($this->model, $params)) {
            foreach ($joins as $key => $value) {
                if ($value['foreign_key']) {
                    $this->query->addSelect($value['foreign_key']);
                }
                $this->query->with($key . ':' . $value['fields']);
            }
        }
    }

    /**
     * @return array
     */
    public function all()
    {
        return JsonApi::render($this->query->paginate(), $this->name);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * @param Model      $model
     * @param array|null $queryFields
     * @return array|string[]
     * @throws Exception
     */
    protected function handleFields(Model $model, ?array $queryFields = []): array
    {
        $table = $model->getTable();
        $fields = [];
        if ($queryFields) {
            $connexion = $model->getConnection()->getDoctrineSchemaManager();
            $columns = $connexion->listTableColumns($table, $this->database);
            $columnsKeys = collect(array_keys($columns));
            foreach ($queryFields as $params) {
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
     * @return Collection
     * @throws Exception
     */
    protected function handleWith(Model $model, ?array $params = []): Collection
    {
        $relations = $this->getRelations($model);
        $relationsName = collect(array_keys($relations));

        foreach ($params as $key => $value) {
            if ($relationsName->contains($key)) {
                $relation = $model->$key();
                $fields = $this->handleFields($relation->getRelated(), explode(',', $value));

                switch (get_class($relation)) {
                    case BelongsTo::class:
                        $ownerKey = $relation->getRelated()->getTable() . '.' . $relation->getOwnerKeyName();
                        $this->addInclude(
                            $key,
                            $relation->getRelated()->getTable(),
                            $this->mergeArray($fields, $ownerKey),
                            $model->getTable() . '.' . $relation->getForeignKeyName(),
                        );
                        break;
                    case HasOne::class:
                        $foreignKey = $relation->getRelated()->getTable() . '.' . $relation->getForeignKeyName();
                        $this->addInclude(
                            $key,
                            $relation->getRelated()->getTable(),
                            $this->mergeArray($fields, $foreignKey),
                        );
                        break;
                    case MorphOne::class:
                        $foreignKey = $relation->getRelated()->getTable() . '.' . $relation->getForeignKeyName();
                        $morphType = $relation->getMorphType();
                        $this->addInclude(
                            $key,
                            $relation->getParent()->getTable(),
                            $this->mergeArray($fields, [$foreignKey, $morphType]),
                        );
                        break;
                }
            }
        }

        return $this->getIncludes();
    }
}
