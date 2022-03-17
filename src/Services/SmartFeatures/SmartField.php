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
    public \Closure $get;

    /**
     * @var mixed
     */
    public \Closure $set;

    /**
     * @var mixed
     */
    public \Closure $filter;

    /**
     * @var mixed
     */
    public \Closure $search;

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
     * @param \Closure $set
     * @return SmartField
     */
    public function set(\Closure $set): SmartField
    {
        $this->set = $set;

        return $this;
    }

    /**
     * @param \Closure $filter
     * @return SmartField
     */
    public function filter(\Closure $filter): SmartField
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param \Closure $search
     * @return SmartField
     */
    public function search(\Closure $search): SmartField
    {
        $this->search = $search;

        return $this;
    }
}
