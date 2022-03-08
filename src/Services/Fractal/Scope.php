<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Fractal;

use League\Fractal\Scope as ScopeLeagueFractal;

/**
 * Class Scope
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
class Scope extends ScopeLeagueFractal
{
    /**
     * Set parent scopes.
     *
     * @internal
     *
     * @param string[] $parentScopes Value to set.
     *
     * @return Scope
     */
    public function setParentScopes($parentScopes)
    {
        $this->parentScopes = $parentScopes;

        return $this;
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
