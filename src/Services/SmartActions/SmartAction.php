<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartActions;

use Illuminate\Support\Collection;

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
    protected string $endpoint;

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
    protected array $hooks = [];

    /**
     * @param string $model
     * @param string $name
     * @param string $endpoint
     * @param string $type
     */
    public function __construct(string $model, string $name, string $endpoint, string $type)
    {
        $this->model = $model;
        $this->name = $name;
        $this->endpoint = $endpoint;
        $this->type = $type;
    }

    /**
     * @return $this
     */
    public function addField(): SmartAction
    {
        // LOGIC ADD FIELD
        //$field = app()->make('....');

        $field = 1; // a virer

        $this->fields->put($field);

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
    public function hooks($load = false, $change = false): SmartAction
    {
        $this->hooks = compact('load', 'change');

        return $this;
    }

    /**
     * @return Collection
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'id'       => $this->model . '.' . $this->name,
            'name'     => $this->name,
            'endpoint' => $this->endpoint,
            'fields'   => collect($this->fields)->map(fn($item) => $item->serialize())->all(),
            'type'     => $this->type,
            'download' => $this->download,
            'hooks'    => $this->hooks,
        ];
    }
}
