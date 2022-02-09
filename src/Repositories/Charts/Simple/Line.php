<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class Line
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Line extends ChartRepository
{
    /**
     * @return array
     * @throws Exception
     */
    public function get(): array
    {
        $groupBy = $this->handleGroupByField(request()->input('group_by_date_field'));
        if ($this->aggregate === 'count') {
            $this->aggregateField = $groupBy['field'];
        } else {
            $this->aggregateField = $this->table . '.' . $this->aggregateField;
        }

        $query = $this->query()->select(DB::raw($this->aggregate . '(' . $this->aggregateField . ')'), $groupBy['field'])
            ->groupBy($groupBy['field'])
            ->get()
            ->mapWithKeys(fn($item, $key) => [Arr::get($item, $groupBy['responseField'])->format($this->getFormat()) => $item->{$this->aggregate}])
            ->all();

        return $this->serialize($query);
    }

    /**
     * @param $data
     * @return array
     */
    public function serialize($data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = [
                'label'  => $key,
                'values' => compact('value'),
            ];
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        switch (Str::lower($this->params['time_range'])) {
            case 'day':
                $format = 'd/m/Y';
                break;
            case 'week':
                $format = '\WW-Y';
                break;
            case 'month':
                $format = 'M Y';
                break;
            case 'year':
                $format = 'Y';
                break;
        }

        return $format;
    }
}
