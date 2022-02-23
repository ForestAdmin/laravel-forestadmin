<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Concerns;

use ForestAdmin\LaravelForestAdmin\Services\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Class QueryBuilderPreviousPeriod
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class QueryBuilderPreviousPeriod extends QueryBuilder
{
    /**
     * @param Builder     $query
     * @param string      $field
     * @param string      $operator
     * @param string|null $value
     * @return Builder
     */
    public function dateFilters(Builder $query, string $field, string $operator, ?string $value = null): Builder
    {
        switch ($operator) {
            case 'today':
                $interval = [
                    Carbon::now($this->timezone)->subDay()->startOfDay(),
                    Carbon::now($this->timezone)->subDay()->endOfDay(),
                ];
                break;
            case 'previous_x_days':
                $interval = [
                    Carbon::now($this->timezone)->subDays($value * 2)->startOfDay(),
                    Carbon::now($this->timezone)->subDays(2)->endOfDay(),
                ];
                break;
            case 'previous_x_days_to_date':
                $interval = [
                    Carbon::now($this->timezone)->subDays($value * 2)->startOfDay(),
                    Carbon::now($this->timezone)->subDay()->endOfDay(),
                ];
                break;
            case 'yesterday':
            case 'previous_week':
            case 'previous_month':
            case 'previous_quarter':
            case 'previous_year':
            case 'previous_week_to_date':
            case 'previous_month_to_date':
            case 'previous_quarter_to_date':
            case 'previous_year_to_date':
                $period = $operator === 'yesterday' ? 'Day' : Str::ucfirst(Str::of($operator)->explode('_')->get(1));
                $sub = 'sub' . $period . 's';
                $start = 'startOf' . $period;
                $end = 'endOf' . $period;
                if (Str::endsWith($operator, 'to_date')) {
                    $interval = [Carbon::now($this->timezone)->$sub(2)->$start(), Carbon::now($this->timezone)->$sub()];
                } else {
                    $interval = [Carbon::now($this->timezone)->$sub(2)->$start(), Carbon::now($this->timezone)->$sub(2)->$end()];
                }
                break;
            default:
                $interval = [];
                break;
        }

        if (!empty($interval)) {
            $query->whereBetween($field, $interval);
        }

        return $query;
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public function appendPreviousPeriod(): array
    {
        $previousFilter = 0;
        $condition = null;
        $aggregator = 'and';

        if (array_key_exists('filters', $this->params)) {
            [$aggregator, $filters] = $this->parseFilters($this->params['filters']);
            if ($aggregator === 'and') {
                foreach ($filters as $filter) {
                    if (is_array($filter) && array_key_exists('operator', $filter) && Str::startsWith($filter['operator'], ['previous_', 'today', 'yesterday'])) {
                        $previousFilter++;
                        $condition = $filter;
                    }
                }
            }
        }

        return [
            'apply'      => $previousFilter === 1,
            'filter'     => $previousFilter === 1 ? $condition : null,
            'aggregator' => $aggregator,
        ];
    }
}
