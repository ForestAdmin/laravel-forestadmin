<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class Leaderboard
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Leaderboard extends ChartRepository
{
    /**
     * @return array
     * @throws Exception
     */
    public function get(): array
    {
        $groupBy = $this->handleGroupByField(request()->input('label_field'));
        $relationName = request()->input('relationship_field');
        $relation = $this->model->$relationName();
        $relatedModel = $relation->getRelated();

        if ($this->aggregate === 'count') {
            $this->aggregateField = $relatedModel->getTable() . '.' . $relatedModel->getKeyName();
        } else {
            $this->aggregateField = $this->handleField($relatedModel, $this->aggregateField);
        }

        $query = $this->query()->select(DB::raw($this->aggregate . '(' . $this->aggregateField . ') AS ' . $this->aggregate), $groupBy['field']);

        switch (get_class($relation)) {
            case HasMany::class:
                $query->join($relatedModel->getTable(), $relatedModel->getTable() . '.' . $relation->getForeignKeyName(), '=', $this->table . '.' . $relation->getLocalKeyName());
                break;
            case BelongsToMany::class:
                $query->join($relation->getTable(), $relation->getTable() . '.' . $relation->getForeignPivotKeyName(), '=', $this->table . '.' . $relation->getParentKeyName());
                $query->join($relatedModel->getTable(), $relatedModel->getTable() . '.' . $relation->getRelatedKeyName(), '=', $relation->getTable() . '.' . $relation->getRelatedPivotKeyName());
                break;
            default:
                throw new ForestException("Unsupported relation");
        }

        $query = $query->groupBy($groupBy['field'])
            ->limit($this->params['limit'])
            ->orderBy($this->aggregate, 'DESC')
            ->get()
            ->mapWithKeys(fn($item, $key) => [Arr::get($item, $groupBy['responseField']) => $item->{$this->aggregate}])
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
            $result[] = compact('key', 'value');
        }

        return $result;
    }
}
