<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartActions;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

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
     * @var array
     */
    protected array $change = [];

    /**
     * @var Closure
     */
    protected Closure $execute;

    /**
     * @var Closure|null
     */
    private ?Closure $load = null;

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
        $this->hooks();
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return Str::slug($this->name);
    }

    /**
     * @return Collection
     */
    public function getFields(): Collection
    {
        return $this->fields;
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
        try {
            return $this->change[$key];
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
            'load'   => !is_null($this->load),
            'change' => $this->change,
        ];
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
