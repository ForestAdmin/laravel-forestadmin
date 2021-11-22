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
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

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
            'name'                   => $this->name,
            'old_name'               => $this->oldName,
            'icon'                   => $this->icon,
            'is_read_only'           => $this->isReadOnly,
            'is_virtual'             => $this->isVirtual,
            'only_for_relationships' => $this->onlyForRelationships,
            'pagination_type'        => $this->paginationType,
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
            $fields->put($field['field'], array_merge($field, $values));
        }

        return $fields->values()->toArray();
    }


    /**
     * @return Collection
     * @throws Exception
     */
    private function fetchFieldsFromTable(): Collection
    {
        $fields = new Collection();
        $connexion = $this->model->getConnection()->getDoctrineSchemaManager();
        $columns = $connexion->listTableColumns($this->table, $this->database);

        if ($columns) {
            foreach ($columns as $column) {
                $field = $this->fieldDefaultValues();
                $field['field'] = $column->getName();
                $field['type'] = $this->getType($column->getType()->getName());
                $field['is_required'] = $column->getNotnull();
                $field['default_value'] = $column->getDefault();
                $fields->put($column->getName(), $field);
            }
        }

        return $this->mergeFieldsWithRelations($fields, $this->getRelations($this->model));
    }


    /**
     * @param Collection $fields
     * @param array      $relations
     * @return Collection
     */
    private function mergeFieldsWithRelations(Collection $fields, array $relations): Collection
    {
        foreach ($relations as $name => $type) {
            $relation = $this->model->$name();

            switch ($type) {
                case BelongsTo::class:
                    $field = $fields->firstWhere('field', $relation->getForeignKeyName());
                    $field = array_merge(
                        $field,
                        [
                            'field'      => $relation->getRelationName(),
                            'reference'  => $relation->getRelated()->getTable() . '.' . $relation->getOwnerKeyName(),
                            'inverse_of' => $relation->getOwnerKeyName(),
                        ]
                    );
                    $name = $relation->getForeignKeyName();
                    break;
                case BelongsToMany::class:
                case MorphToMany::class:
                    $field = array_merge(
                        $this->fieldDefaultValues(),
                        [
                            'field'      => $relation->getRelationName(),
                            'inverse_of' => $relation->getRelatedPivotKeyName()
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
                            'field'      => $relation->getRelated()->getTable(),
                            'reference'  => $relation->getRelated()->getTable() . '.' . $relation->getForeignKeyName(),
                            'inverse_of' => $type instanceof HasOneOrMany ? $relation->getForeignKeyName() : null,
                        ]
                    );
                    $name = $relation->getRelated()->getTable();
                    break;
                case HasOneThrough::class:
                case HasManyThrough::class:
                    $field = array_merge(
                        $this->fieldDefaultValues(),
                        [
                            'field'     => $relation->getParent()->getTable(),
                            'reference' => $relation->getRelated()->getTable() . '.' . $relation->getLocalKeyName(),
                        ]
                    );
                    $name = $relation->getParent()->getTable();
                    break;
            }

            $field['type'] = $this->getType(Types::INTEGER);
            $field['relationship'] = $this->mapRelationships($type);
            $fields->put($name, $field);
        }

        return $fields;
    }

    /**
     * @param string|null $name
     * @return array
     */
    private function fieldDefaultValues(?string $name = null): array
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
