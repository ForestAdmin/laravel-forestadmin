<?php

namespace ForestAdmin\LaravelForestAdmin\Auth\Model;

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
     * @var array
     */
    protected array $permissions = [];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
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
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     * @return ForestUser
     */
    public function setPermissions(array $permissions): ForestUser
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function getPermission($key)
    {
        return $this->getPermissions()[$key] ?? null;
    }
}
