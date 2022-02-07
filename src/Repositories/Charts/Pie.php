<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Pie
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Pie extends ChartRepository
{
    /**
     * @var string
     */
    protected string $groupByField;

    /**
     * @param Model $model
     * @throws Exception
     */
    public function __construct(Model $model)
    {
        parent::__construct($model);
        $this->groupByField = $this->handleField(request()->input('group_by_field'));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function get(): array
    {
        $requestField = request()->input('group_by_field');
        $query = $this->query()
            ->select(DB::raw($this->aggregate . '(' . $this->groupByField . ')'), $this->groupByField)
            ->groupBy($this->groupByField)
            ->get()
            ->mapWithKeys(fn ($item, $key) => [$item->$requestField => $item->{$this->aggregate}])
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

    /**
     * @param $dataField
     * @return string
     * @throws Exception
     */
    protected function handleField($dataField): string
    {
        $table = $this->model->getTable();
        $field = null;
        $columnsKeys = collect(array_keys($this->getColumns($this->model)));
        if ($columnsKeys->contains($dataField)) {
            $field = $table . '.' . $dataField;
        }

        // TODO A CHECKER POUR LES RELATIONS MAYBE
        /*if (!in_array($table . '.' . $model->getKeyName(), $fields, true)) {
            $fields[] = $table . '.' . $model->getKeyName();
        }*/

        if (!$field) {
            throw new ForestException("The field $dataField doesn't exist in the table $table");
        }

        return $field;
    }
}
