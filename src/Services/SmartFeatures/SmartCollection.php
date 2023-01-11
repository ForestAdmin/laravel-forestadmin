<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class SmartCollection
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartCollection
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var bool
     */
    protected bool $is_read_only = false;

    /**
     * @var bool
     */
    protected bool $is_searchable = false;

    /**
     * @var array
     */
    protected array $relations = [];

    /**
     * @return Collection
     */
    public function fields(): Collection
    {
        return collect();
    }

    /**
     * @return array
     */
    public function serializeFields(): array
    {
        $this->isValid();

        $methods = (new \ReflectionClass($this))->getMethods(\ReflectionMethod::IS_PUBLIC);
        $smartRelationships = new Collection();

        foreach ($methods as $method) {
            if (($returnType = $method->getReturnType()) && $returnType->getName() === SmartRelationship::class && $method->getName() !== lcfirst(class_basename(SmartRelationship::class))) {
                $smartRelationships->push($this->{$method->getName()}()->serialize());
            }
        }

        return $this->fields()
            ->map(fn($item) => $item->serialize())
            ->merge($smartRelationships)
            ->all();
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'name'                   => $this->name,
            'name_old'               => $this->name,
            'class'                  => static::class,
            'icon'                   => null,
            'is_read_only'           => $this->is_read_only,
            'is_virtual'             => true,
            'is_searchable'          => $this->is_searchable,
            'only_for_relationships' => false,
            'pagination_type'        => 'page',
            'fields'                 => $this->serializeFields(),
            'actions'                => [],
            'segments'               => [],
        ];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $filter = $this->fields()->filter(fn($item) => $item instanceof SmartField ? null : $item);

        if (!empty($filter->all())) {
            throw new ForestException("Each field of a SmartCollection must be an instance of SmartField");
        }

        return true;
    }

    /**
     * @param $record
     * @return static
     */
    public static function hydrate($record): self
    {
        if ($record instanceof Model) {
            $record = $record->toArray();
        } else {
            $record = (array) $record;
        }
        $object = new static();
        foreach ($object->getAttributes() as $attribute) {
            if (isset($record[$attribute])) {
                $object->$attribute = $record[$attribute];
            }
        }

        return $object;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->fields()->map(fn(SmartField $field) => $field->getField())->toArray();
    }

    /**
     * @param $relation
     * @param $value
     * @return $this
     */
    public function setRelation($relation, $value): SmartCollection
    {
        $this->relations[$relation] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return array
     */
    public function attributesToArray(): array
    {
        $record = [];
        foreach ($this->getAttributes() as $attribute) {
            $record[$attribute] = $this->$attribute ?? null;
        }

        return $record;
    }
}
