<?php

namespace ForestAdmin\LaravelForestAdmin\Auth\Guard\Model;

use Illuminate\Support\Collection;

/**
 * Class ForestUser
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestUser
{
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
     * @var Collection
     */
    protected Collection $chartPermissions;

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
     * @param  string $key
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
        return isset($this->getPermissions()[$key]) && in_array($action, $this->getPermissions()[$key], true);
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
        return in_array($action, $this->getSmartActionPermissions()[$key], true);
    }

    /**
     * @param string $query
     * @return bool
     */
    public function hasLiveQueryPermission(string $query): bool
    {
        foreach ($this->stats['queries'] as $queryAllowed) {
            if (trim($queryAllowed) === trim($query)) {
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
}
