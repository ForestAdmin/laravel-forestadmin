<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
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
     * @return void
     */
    protected function appendSort(Builder $query, string $sort): void
    {
        [$sortBy, $direction] = $this->sortByAndDirection($sort);
        $smartFields = ForestSchema::getSmartFields(class_basename($this->model));
        if (isset($smartFields[$sortBy])) {
            call_user_func($this->model->{$sortBy}()->sort, $query, $direction);
        } else {
            $query->orderBy($sortBy, $direction);
        }
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
