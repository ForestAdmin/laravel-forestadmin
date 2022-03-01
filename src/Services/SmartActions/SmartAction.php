<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartActions;

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
    protected string $name;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string
     */
    protected string $endpoint;

    /**
     * @var array
     */
    protected array $fields;

    /**
     * @var bool
     */
    protected bool $download;

    /**
     * @var string
     */
    protected string $model;

    /**
     * @param string $model
     * @param string $name
     * @param string $endpoint
     * @param array  $fields
     * @param string $type
     * @param bool   $download
     * @return $this
     */
    public function __construct(string $model, string $name, string $endpoint, array $fields = [], string $type = 'bulk', bool $download = false)
    {
        $this->model = $model;
        $this->name = $name;
        $this->endpoint = $endpoint;
        $this->fields = $fields;
        $this->type = $type;
        $this->download = $download;
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
        ];
    }
}
