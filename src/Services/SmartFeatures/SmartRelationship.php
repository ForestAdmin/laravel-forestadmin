<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use Illuminate\Support\Str;

/**
 * Class SmartRelationship
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartRelationship extends AbstractField
{
    /**
     * @var \Closure
     */
    public \Closure $get;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
        $this->get = fn() => null;
    }

    /**
     * @param \Closure $get
     * @return SmartRelationship
     */
    public function get(\Closure $get): SmartRelationship
    {
        $this->get = $get;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelated(): string
    {
        return Str::before($this->reference, '.');
    }
}
