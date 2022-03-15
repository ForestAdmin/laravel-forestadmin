<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

/**
 * Class SmartField
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartField extends AbstractField
{
    /**
     * @var mixed
     */
    protected \Closure $get;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
    }

    /**
     * @param \Closure $get
     * @return SmartField
     */
    public function get(\Closure $get): SmartField
    {
        $this->get = $get;

        return $this;
    }

    /**
     * @return \Closure
     */
    public function call(): \Closure
    {
        return $this->get;
    }
}
