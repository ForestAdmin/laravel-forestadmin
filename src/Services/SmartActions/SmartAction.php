<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartActions;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

/**
 * Class SmartAction
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartAction
{
    /**
     * @var string
     */
    protected string $model;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var Collection
     */
    protected Collection $fields;

    /**
     * @var bool
     */
    protected bool $download = false;

    /**
     * @var array
     */
    protected array $hooks;

    /**
     * @var Closure
     */
    protected Closure $execute;

    /**
     * @var Closure|null
     */
    protected ?Closure $load = null;

    /**
     * @var array
     */
    protected array $change = [];

    /**
     * @param string  $model
     * @param string  $name
     * @param string  $type
     * @param Closure $execute
     */
    public function __construct(string $model, string $name, string $type, Closure $execute)
    {
        $this->model = $model;
        $this->name = $name;
        $this->type = $type;
        $this->execute = $execute;
        $this->fields = collect();
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return Str::slug($this->name);
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields->mapWithKeys(
            function ($item) {
                $data = $item->serialize();
                return [$data['field'] => $data];
            }
        )->all();
    }

    /**
     * @param string $key
     * @return Field
     */
    public function getField(string $key): Field
    {
        return $this->fields->first(fn ($field) => $field->getField() === $key);
    }

    /**
     * @return Closure
     */
    public function getExecute(): Closure
    {
        return $this->execute;
    }

    /**
     * @return Closure
     */
    public function getLoad(): Closure
    {
        return $this->load;
    }

    /**
     * @param string $key
     * @return Closure
     */
    public function getChange(string $key): Closure
    {
        $field = $this->getField($key);
        try {
            return $this->change[$field->getHook()];
        } catch (\Exception $exception) {
            // todo throw exception
        }
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function addField(array $attributes): SmartAction
    {
        $field = App::makeWith(Field::class, ['attributes' => $attributes]);
        $this->fields->push($field);

        return $this;
    }

    /**
     * @param bool $value
     * @return SmartAction
     */
    public function download(bool $value = false): SmartAction
    {
        $this->download = $value;

        return $this;
    }

    /**
     * @param Closure|null $closure
     * @return void
     */
    public function load(Closure $closure = null): SmartAction
    {
        $this->load = $closure;

        return $this;
    }

    /**
     * @param array $closures
     * @return $this
     */
    public function change(array $closures = []): SmartAction
    {
        $this->change = $closures;

        return $this;
    }

    /**
     * @return array
     */
    public function hooks(): array
    {
        return [
            'load'   => $this->load !== null,
            'change' => array_keys($this->change),
        ];
    }

    /**
     * @param array $fields
     * @return SmartAction
     */
    public function mergeRequestFields(array $fields): SmartAction
    {
        $this->fields->map(
            function ($item) use ($fields) {
                $fieldKey = array_search($item->getField(), array_column($fields, 'field'), true);
                $item->merge($fields[$fieldKey]);
            }
        );

        return $this;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'id'       => $this->model . '.' . $this->name,
            'name'     => $this->name,
            'fields'   => $this->fields->map(fn($item) => $item->serialize())->all(),
            'endpoint' => '/forest/smart-actions/' . strtolower($this->model) . '_' . $this->getKey(),
            'type'     => $this->type,
            'download' => $this->download,
            'hooks'    => $this->hooks(),
        ];
    }
}