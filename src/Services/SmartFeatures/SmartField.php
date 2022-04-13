<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use Illuminate\Database\Eloquent\Builder;

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
     * @var \Closure
     */
    public \Closure $get;

    /**
     * @var \Closure
     */
    public \Closure $set;

    /**
     * @var \Closure
     */
    public \Closure $sort;

    /**
     * @var \Closure
     */
    public \Closure $filter;

    /**
     * @var \Closure
     */
    public \Closure $search;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        $this->is_read_only = $attributes['is_read_only'] ?? true;
        $this->initializeClosures();
    }

    /**
     * @return void
     */
    private function initializeClosures(): void
    {
        $this->get = fn() => null;
        $this->set = fn() => null;
        $this->sort = fn(Builder $query, string $direction) => $query;
        $this->filter = fn(Builder $query, $value, string $operator, string $aggregator) => $query;
        $this->search = fn(Builder $query, $value) => $query;
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
     * @param \Closure $sort
     * @return SmartField
     */
    public function sort(\Closure $sort): SmartField
    {
        $this->sort = $sort;

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
        $this->is_filterable = true;

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
