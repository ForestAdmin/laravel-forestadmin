<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class HasSort
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait HasSort
{
    /**
     * @param Builder $query
     * @param string  $sort
     * @return Builder
     */
    protected function appendSort(Builder $query, string $sort): Builder
    {
        [$sortBy, $direction] = $this->sortByAndDirection($sort);
        $query->orderBy($sortBy, $direction);

        return $query;
    }

    /**
     * @param string $sort
     * @return array
     */
    public function sortByAndDirection(string $sort): array
    {
        if ($sort[0] === '-') {
            return [substr($sort, 1), 'DESC'];
        }

        return [$sort, 'ASC'];
    }
}
