<?php

namespace ForestAdmin\LaravelForestAdmin\Schema\Concerns;

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
     */
    public function searchFields(): array
    {
        return [];
    }
}
