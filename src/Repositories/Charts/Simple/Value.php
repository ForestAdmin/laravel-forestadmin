<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Class Value
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Value extends ChartRepository
{
    use HasFilters;

    /**
     * @return array
     * @throws Exception
     * @throws \JsonException
     */
    public function get(): array
    {
        $currentPeriodQuery = $this->query();
        $previousPeriodQuery = null;
        $appendPreviousPeriod = $this->appendPreviousPeriod();

        if ($appendPreviousPeriod['apply']) {
            $previousPeriodQuery = clone $currentPeriodQuery;
            $this->timezone = new \DateTimeZone($this->params['timezone'] ?? config('app.timezone'));

            $wheres = $previousPeriodQuery->getQuery()->wheres;
            $conditionPeriod = array_filter($wheres, fn($where) => $where['type'] === 'between');
            foreach ($previousPeriodQuery->getQuery()->bindings['where'] as $key => $value) {
                if (in_array($value, $conditionPeriod[key($conditionPeriod)]['values'], true)) {
                    unset($previousPeriodQuery->getQuery()->bindings['where'][$key]);
                }
            }
            unset($wheres[key($conditionPeriod)]);
            $previousPeriodQuery->getQuery()->wheres = $wheres;

            $condition = $conditionPeriod[key($conditionPeriod)];
            $previousPeriodQuery->whereBetween(
                $condition['column'],
                $this->applyDateFiltersOnPreviousPeriod($appendPreviousPeriod['filter']['operator'], $appendPreviousPeriod['filter']['value']),
                $this->appendPreviousPeriod()['aggregator']
            );

            $previousPeriodQuery = $previousPeriodQuery->{$this->aggregate}($this->aggregateField);
        }
        $currentPeriodQuery = $currentPeriodQuery->{$this->aggregate}($this->aggregateField);

        return $this->serialize(
            [
                $currentPeriodQuery,
                $previousPeriodQuery
            ]
        );
    }

    /**
     * @param mixed $data
     * @return array
     */
    public function serialize($data): array
    {
        return [
            'countCurrent'  => $data[0],
            'countPrevious' => $data[1],
        ];
    }

    /**
     * @param string      $operator
     * @param string|null $value
     * @return array
     */
    public function applyDateFiltersOnPreviousPeriod(string $operator, ?string $value = null): array
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

        return $interval;
    }

    /**
     * @return array
     * @throws \JsonException
     */
    private function appendPreviousPeriod(): array
    {
        $previousFilter = 0;
        $condition = null;
        $aggregator = 'and';

        if (array_key_exists('filters', $this->params)) {
            [$aggregator, $filters] = $this->parseFilters($this->params['filters']);
            foreach ($filters as $filter) {
                if (is_array($filter) && array_key_exists('operator', $filter) && Str::startsWith($filter['operator'], ['previous_', 'today', 'yesterday'])) {
                    $previousFilter++;
                    $condition = $filter;
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
