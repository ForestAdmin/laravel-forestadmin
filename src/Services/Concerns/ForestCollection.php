<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use ForestAdmin\LaravelForestAdmin\Services\SmartActions\SmartAction;
use Illuminate\Support\Collection;

/**
 * Class ForestCollection
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait ForestCollection
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function schemaFields(): array
    {
        return [];
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function searchFields(): array
    {
        return [];
    }

    /**
     * @return Collection
     */
    public function smartActions(): Collection
    {
        return collect();
    }

    /**
     * @param string $name
     * @return SmartAction
     */
    public function getSmartAction(string $name): SmartAction
    {
        return $this->smartActions()->first(
            fn ($item) => $item->getKey() === $name
        );
    }
}
