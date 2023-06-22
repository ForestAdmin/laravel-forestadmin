<?php

namespace ForestAdmin\LaravelForestAdmin\Auth\Guard\Model;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\ForestUserFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class ForestUser
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestUser
{
    public const ALLOWED_PERMISSION_LEVELS = ['admin', 'editor', 'developer'];
    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var Collection
     */
    protected Collection $permissions;

    /**
     * @var Collection
     */
    protected Collection $smartActionPermissions;

    /**
     * @var array
     */
    protected array $stats;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
        $this->permissions = new Collection();
        $this->smartActionPermissions = new Collection();
    }

    /**
     * @return int
     */
    public function getKey(): int
    {
        return (int) $this->attributes['id'];
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->getAttributes()[$key] ?? null;
    }

    /**
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * @param Collection $permissions
     * @return ForestUser
     */
    public function setPermissions(Collection $permissions): ForestUser
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @param string $key
     * @param array  $values
     * @return ForestUser
     */
    public function addPermission(string $key, array $values): ForestUser
    {
        if (!empty($values)) {
            $this->permissions->put($key, $values);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $action
     * @return bool
     */
    public function hasPermission(string $key, string $action): bool
    {
        if (!(isset($this->getPermissions()[$key]) && in_array($action, $this->getPermissions()[$key], true))) {
            app(ForestUserFactory::class)->makePermissionToUser($this, $this->getAttribute('rendering_id'), true);

            return isset($this->getPermissions()[$key]) && in_array($action, $this->getPermissions()[$key], true);
        }

        return true;
    }

    /**
     * @return Collection
     */
    public function getSmartActionPermissions(): Collection
    {
        return $this->smartActionPermissions;
    }

    /**
     * @param Collection $smartActionPermissions
     * @return ForestUser
     */
    public function setSmartActionPermissions(Collection $smartActionPermissions): ForestUser
    {
        $this->smartActionPermissions = $smartActionPermissions;

        return $this;
    }

    /**
     * @param string $key
     * @param array  $values
     * @return ForestUser
     */
    public function addSmartActionPermission(string $key, array $values): ForestUser
    {
        if (!empty($values)) {
            $this->smartActionPermissions->put($key, $values);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $action
     * @return bool
     */
    public function hasSmartActionPermission(string $key, string $action): bool
    {
        return in_array($action, $this->getSmartActionPermissions()->get($key, []), true);
    }

    /**
     * @param string $query
     * @return bool
     */
    public function hasLiveQueryPermission(string $query): bool
    {
        if (in_array($this->getAttribute('permission_level'), self::ALLOWED_PERMISSION_LEVELS, true)) {
            return true;
        }

        if (!$this->hasQuery($query)) {
            app(ForestUserFactory::class)->makePermissionToUser($this, $this->getAttribute('rendering_id'), true);

            return $this->hasQuery($query);
        }

        return true;
    }

    /**
     * @param string $query
     * @return bool
     */
    public function hasQuery(string $query): bool
    {
        foreach ($this->stats['queries'] as $queryAllowed) {
            if (trim($queryAllowed) === trim($query)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $chart
     * @return bool
     */
    public function hasSimpleChartPermission(array $chart): bool
    {
        if (in_array($this->getAttribute('permission_level'), self::ALLOWED_PERMISSION_LEVELS, true)) {
            return true;
        }

        $type = strtolower(Str::plural($chart['type']));
        $chart = $this->formatChartPayload($chart);

        if (!$this->hasChart($type, $chart)) {
            app(ForestUserFactory::class)->makePermissionToUser($this, $this->getAttribute('rendering_id'), true);

            return $this->hasChart($type, $chart);
        }

        return true;
    }

    /**
     * @param string $type
     * @param array  $chart
     * @return bool
     */
    public function hasChart(string $type, array $chart): bool
    {
        foreach ($this->stats[$type] as $chartAllowed) {
            if (empty(array_diff_key($chart, $chartAllowed)) && empty(array_diff($chart, $chartAllowed))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * @param array $stats
     * @return ForestUser
     */
    public function setStats(array $stats): ForestUser
    {
        $this->stats = $stats;
        return $this;
    }

    /**
     * @param array $chart
     * @return array
     */
    private function formatChartPayload(array $chart): array
    {
        $keys = [
            'aggregate'           => 'aggregator',
            'aggregate_field'     => 'aggregateFieldName',
            'collection'          => 'sourceCollectionId',
            'filters'             => 'filter',
            'group_by_field'      => 'groupByFieldName',
            'group_by_date_field' => 'groupByFieldName',
            'time_range'          => 'timeRange',
            'relationship_field'  => 'relationshipFieldName',
            'label_field'         => 'labelFieldName'
        ];

        foreach ($chart as $key => $value) {
            if (is_null($value)) {
                unset($chart[$key]);
            } else {
                if ($key === 'group_by_field') {
                    $value = Str::before($value, ':');
                }
                if (isset($keys[$key])) {
                    $chart[$keys[$key]] = $value;
                    unset($chart[$key]);
                }
            }
        }

        return $chart;
    }
}
