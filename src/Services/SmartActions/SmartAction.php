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
     * @var string
     */
    protected string $httpMethod;

    /**
     * @var array
     */
    protected array $fields;

    /**
     * @var bool
     */
    protected bool $download;

    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->type = $options['type'] ?? 'bulk';
        $this->endpoint = $options['endpoint'] ?? '';
        $this->httpMethod = $options['httpMethod'] ?? 'POST';
        $this->fields = $options['fields'] ?? [];
        $this->download = $options['download'] ?? false;
    }
}
