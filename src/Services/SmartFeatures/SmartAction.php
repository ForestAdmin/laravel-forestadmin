<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use Closure;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
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
     * @var Closure|null
     */
    protected ?Closure $execute;

    /**
     * @var Closure|null
     */
    protected ?Closure $load = null;

    /**
     * @var array
     */
    protected array $change = [];

    /**
     * @var string
     */
    protected string $methodName;

    /**
     * @param string       $model
     * @param string       $name
     * @param string       $type
     * @param string       $methodName
     * @param Closure|null $execute
     */
    public function __construct(string $model, string $name, string $type, string $methodName, ?Closure $execute = null)
    {
        $this->model = $model;
        $this->name = $name;
        $this->type = $type;
        $this->execute = $execute;
        $this->fields = collect();
        $this->methodName = $methodName;
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
     * @return SmartActionField
     * @throws \Exception
     */
    public function getField(string $key): SmartActionField
    {
        $field = $this->fields->first(fn($field) => $field->getField() === $key);
        if (null !== $field) {
            return $field;
        } else {
            throw new ForestException("There is no field $key in your smart-action");
        }
    }

    /**
     * @return Closure
     */
    public function getExecute(): Closure
    {
        return $this->execute;
    }

    /**
     * @return Closure|null
     */
    public function getLoad()
    {
        return $this->load;
    }

    /**
     * @param string $key
     * @return Closure
     * @throws \Exception
     */
    public function getChange(string $key): Closure
    {
        $field = $this->getField($key);
        try {
            return $this->change[$field->getHook()];
        } catch (\Exception $e) {
            throw new ForestException("There is no hook on the field $key");
        }
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function addField(array $attributes): SmartAction
    {
        $field = App::makeWith(SmartActionField::class, ['attributes' => $attributes]);
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
            'id'         => $this->model . '.' . $this->name,
            'name'       => $this->name,
            'methodName' => $this->methodName,
            'fields'     => $this->fields->map(fn($item) => $item->serialize())->all(),
            'endpoint'   => '/forest/smart-actions/' . strtolower($this->model) . '_' . $this->getKey(),
            'type'       => $this->type,
            'download'   => $this->download,
            'hooks'      => $this->hooks(),
        ];
    }
}
