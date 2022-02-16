<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class Pie
 *
 * @package Laravel-forestaxdmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Pie extends ChartRepository
{
    /**
     * @return array
     * @throws Exception
     */
    public function get(): array
    {
        $groupBy = $this->handleGroupByField(request()->input('group_by_field'));
        if ($this->aggregate === 'count') {
            $this->aggregateField = $groupBy['field'];
        } else {
            $this->aggregateField = $this->table . '.' . $this->aggregateField;
        }

        $query = $this->query()->select(DB::raw($this->aggregate . '(' . $this->aggregateField . ') AS ' . $this->aggregate), $groupBy['field']);

        if (array_key_exists('relationTable', $groupBy)) {
            $query = $query->join($groupBy['relationTable'], $groupBy['keys'][0], '=', $groupBy['keys'][1]);
        }

        $query = $query->groupBy($groupBy['field'])
            ->get()
            ->mapWithKeys(fn($item, $key) => [Arr::get($item, $groupBy['responseField']) => $item->{$this->aggregate}])
            ->all();

        return $this->serialize($query);
    }

    /**
     * @param mixed $data
     * @return array
     */
    public function serialize($data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = compact('key', 'value');
        }

        return $result;
    }
}
