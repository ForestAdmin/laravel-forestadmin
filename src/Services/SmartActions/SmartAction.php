<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartActions;

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
     * @var \Closure
     */
    protected \Closure $execute;

    /**
     * @param string   $model
     * @param string   $name
     * @param string   $type
     * @param \Closure $execute
     */
    public function __construct(string $model, string $name, string $type, \Closure $execute)
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
     * @return \Closure
     */
    public function getExecute(): \Closure
    {
        return $this->execute;
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
     * @param mixed $load
     * @param mixed $change
     * @return $this
     */
    public function hooks($load = false, array $change = []): SmartAction
    {
        $this->hooks = compact('load', 'change');

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
            'hooks'    => $this->hooks,
        ];
    }
}
