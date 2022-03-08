<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Fractal;

use League\Fractal\Manager as ManagerLeagueFractal;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope as ScopeLeagueFractal;

/**
 * Class Manager
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
class Manager extends ManagerLeagueFractal
{
    /**
     * @param ResourceInterface       $resource
     * @param                         $scopeIdentifier
     * @param ScopeLeagueFractal|null $parentScopeInstance
     * @return Scope|ScopeLeagueFractal
     */
    public function createData(ResourceInterface $resource, $scopeIdentifier = null, ScopeLeagueFractal $parentScopeInstance = null)
    {
        if ($parentScopeInstance !== null) {
            $scopeInstance = new Scope($this, $resource, $scopeIdentifier);
            $scopeArray = $parentScopeInstance->getParentScopes();
            $scopeArray[] = $parentScopeInstance->getScopeIdentifier();
            $scopeInstance->setParentScopes($scopeArray);

            return $scopeInstance;
        }

        return new Scope($this, $resource, $scopeIdentifier);
    }
}
