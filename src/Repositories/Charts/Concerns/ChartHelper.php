<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Concerns;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use Illuminate\Support\Collection;

/**
 * Trait ChartHelper
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait ChartHelper
{
    /**
     * @param bool       $condition
     * @param Collection $result
     * @param string     $keyNames
     * @return void
     * @throws ForestException
     */
    protected function abortIf(bool $condition, Collection $result, string $keyNames): void
    {
        if ($condition) {
            $resultKeys = $result->keys()->implode(',');
            throw new ForestException("The result columns must be named '$keyNames' instead of '$resultKeys'");
        }
    }
}
