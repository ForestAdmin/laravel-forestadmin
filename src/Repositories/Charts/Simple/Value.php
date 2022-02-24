<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Concerns\QueryBuilderPreviousPeriod;

/**
 * Class Value
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Value extends ChartRepository
{
    /**
     * @return array
     * @throws Exception
     * @throws \JsonException
     */
    public function get(): array
    {
        $currentPeriodQuery = $this->query()->{$this->aggregate}($this->aggregateField);
        $previousPeriodQuery = null;
        $queryBuilderPreviousPeriod = new QueryBuilderPreviousPeriod($this->model, $this->params);

        if ($queryBuilderPreviousPeriod->appendPreviousPeriod()['apply']) {
            $previousPeriodQuery = $queryBuilderPreviousPeriod->query()->{$this->aggregate}($this->aggregateField);
        }

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
}
