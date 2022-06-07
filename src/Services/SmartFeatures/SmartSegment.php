<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class SmartSegment
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartSegment
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
    protected string $methodName;

    /**
     * @var Closure
     */
    protected Closure $execute;

    /**
     * @param string  $model
     * @param string  $name
     * @param string  $methodName
     * @param Closure $execute
     */
    public function __construct(string $model, string $name, string $methodName, Closure $execute)
    {
        $this->model = $model;
        $this->name = $name;
        $this->methodName = $methodName;
        $this->execute = $execute;
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
        ];
    }

    /**
     * @return Closure
     */
    public function getExecute(): Closure
    {
        return $this->execute;
    }
}
