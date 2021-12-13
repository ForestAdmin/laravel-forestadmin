<?php

namespace ForestAdmin\LaravelForestAdmin\Schema;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\CustomFields;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\DataTypes;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\Relationships;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Model
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestModel
{
    use Relationships;
    use DataTypes;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $oldName;

    /**
     * @var string|null
     */
    protected ?string $icon = null;

    /**
     * @var bool
     */
    protected bool $isReadOnly = false;

    /**
     * @var bool
     */
    protected bool $isSearchable = true;

    /**
     * @var bool
     */
    protected bool $isVirtual = false;

    /**
     * @var bool
     */
    protected bool $onlyForRelationships = false;

    /**
     * @var string
     */
    protected string $paginationType = 'page';

    /**
     * @var LaravelModel
     */
    protected LaravelModel $model;

    /**
     * @var string|null
     */
    protected ?string $database = null;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @var array
     */
    protected array $fields = [];

    /**
     * @param LaravelModel $laravelModel
     */
    public function __construct(LaravelModel $laravelModel)
    {
        $this->model = $laravelModel;
        $this->table = $laravelModel->getConnection()->getTablePrefix() . $laravelModel->getTable();
        if (strpos($this->table, '.')) {
            [$this->database, $this->table] = explode('.', $this->table);
        }

        $this->name = class_basename($this->model);
        $this->oldName = class_basename($this->model);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function serialize(): array
    {
        return [
            'name'                   => Str::camel($this->getName()),
            'old_name'               => Str::camel($this->getOldName()),
            'icon'                   => $this->getIcon(),
            'is_read_only'           => $this->isReadOnly(),
            'is_virtual'             => $this->isVirtual(),
            'only_for_relationships' => $this->isOnlyForRelationships(),
            'pagination_type'        => $this->getPaginationType(),
            'fields'                 => $this->getFields(),
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getFields(): array
    {
        $fields = $this->fetchFieldsFromTable();

        foreach ($this->fields as $field) {
            $values = $fields->firstWhere('field', $field['field']) ?: $this->fieldDefaultValues($field['field']);
            if (array_key_exists('enums', $field)) {
                $values['type'] = 'Enum';
            }
            $fields->put($field['field'], array_merge($values, $field));
        }

        return $fields->values()->toArray();
    }

    /**
     * @param array $fields
     * @return ForestModel
     */
    public function setFields(array $fields): ForestModel
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ForestModel
     */
    public function setName(string $name): ForestModel
    {
        $this->name = Str::camel($name);
        return $this;
    }

    /**
     * @return string
     */
    public function getOldName(): string
    {
        return $this->oldName;
    }

    /**
     * @param string $oldName
     * @return ForestModel
     */
    public function setOldName(string $oldName): ForestModel
    {
        $this->oldName = Str::camel($oldName);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     * @return ForestModel
     */
    public function setIcon(?string $icon): ForestModel
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    /**
     * @param bool $isReadOnly
     * @return ForestModel
     */
    public function setIsReadOnly(bool $isReadOnly): ForestModel
    {
        $this->isReadOnly = $isReadOnly;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->isSearchable;
    }

    /**
     * @param bool $isSearchable
     * @return ForestModel
     */
    public function setIsSearchable(bool $isSearchable): ForestModel
    {
        $this->isSearchable = $isSearchable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVirtual(): bool
    {
        return $this->isVirtual;
    }

    /**
     * @param bool $isVirtual
     * @return ForestModel
     */
    public function setIsVirtual(bool $isVirtual): ForestModel
    {
        $this->isVirtual = $isVirtual;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOnlyForRelationships(): bool
    {
        return $this->onlyForRelationships;
    }

    /**
     * @param bool $onlyForRelationships
     * @return ForestModel
     */
    public function setOnlyForRelationships(bool $onlyForRelationships): ForestModel
    {
        $this->onlyForRelationships = $onlyForRelationships;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaginationType(): string
    {
        return $this->paginationType;
    }

    /**
     * @param string $paginationType
     * @return ForestModel
     */
    public function setPaginationType(string $paginationType): ForestModel
    {
        $this->paginationType = $paginationType;
        return $this;
    }

    /**
     * @return LaravelModel
     */
    public function getModel(): LaravelModel
    {
        return $this->model;
    }

    /**
     * @return string|null
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string|null $database
     * @return ForestModel
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return ForestModel
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return Collection
     * @throws Exception
     */
    protected function fetchFieldsFromTable(): Collection
    {
        $fields = new Collection();
        $connexion = $this->model->getConnection()->getDoctrineSchemaManager();
        $columns = $connexion->listTableColumns($this->table, $this->database);

        if ($columns) {
            foreach ($columns as $column) {
                $field = $this->fieldDefaultValues();
                $field['field'] = $column->getName();
                $field['type'] = $this->getType($column->getType()->getName());
                $field['is_required'] = $column->getNotnull() && $this->model->getKeyName() !== $column->getName();
                $field['default_value'] = $column->getDefault();
                $fields->put($column->getName(), $field);
            }
        }

        $fields = $fields->reject(fn ($item) => $item['type'] === 'unknown');

        return $this->mergeFieldsWithRelations($fields, $this->getRelations($this->model));
    }

    /**
     * @param Collection $fields
     * @param array      $relations
     * @return Collection
     */
    protected function mergeFieldsWithRelations(Collection $fields, array $relations): Collection
    {
        foreach ($relations as $name => $type) {
            $relation = $this->model->$name();
            $related = Str::camel(class_basename($relation->getRelated()));

            switch ($type) {
                case BelongsTo::class:
                    $field = $fields->firstWhere('field', $relation->getForeignKeyName());
                    $field = array_merge(
                        $field,
                        [
                            'field'      => $relation->getRelationName(),
                            'reference'  => $related . '.' . $relation->getOwnerKeyName(),
                            'inverse_of' => $relation->getForeignKeyName(),
                        ]
                    );
                    $name = $relation->getForeignKeyName();
                    break;
                case BelongsToMany::class:
                    $field = array_merge(
                        $this->fieldDefaultValues(),
                        [
                            'field'      => $relation->getRelationName(),
                            'reference'  => $related . '.' . $relation->getParentKeyName(),
                            'inverse_of' => $relation->getRelatedKeyName(),
                        ]
                    );
                    $name = $relation->getRelationName();
                    break;
                case HasMany::class:
                case HasOne::class:
                case MorphOne::class:
                case MorphMany::class:
                    $field = array_merge(
                        $this->fieldDefaultValues(),
                        [
                            'field'      => $name,
                            'reference'  => $related . '.' . $relation->getForeignKeyName(),
                            'inverse_of' => $relation instanceof MorphOneOrMany ? null : $relation->getLocalKeyName(),
                        ]
                    );
                    $name = $relation->getRelated()->getTable();
                    break;
            }


            if (in_array($type, [BelongsToMany::class, HasMany::class, MorphMany::class], true)) {
                $field['type'] = ['Number'];
            } else {
                $field['type'] = $this->getType(Types::INTEGER);
            }
            $field['field'] = Str::camel($field['field']);
            $field['relationship'] = $this->mapRelationships($type);
            $fields->put($name, $field);
        }

        return $fields;
    }

    /**
     * @param string|null $name
     * @return array
     */
    protected function fieldDefaultValues(?string $name = null): array
    {
        return [
            'field'         => $name,
            'type'          => 'string',
            'default_value' => null,
            'enums'         => null,
            'integration'   => null,
            'is_filterable' => true,
            'is_read_only'  => false,
            'is_required'   => false,
            'is_sortable'   => true,
            'is_virtual'    => false,
            'reference'     => null,
            'inverse_of'    => null,
            'widget'        => null,
            'validations'   => [],
        ];
    }
}
