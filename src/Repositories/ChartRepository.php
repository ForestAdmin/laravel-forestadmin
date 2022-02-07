<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\DatabaseHelper;
use ForestAdmin\LaravelForestAdmin\Services\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class ChartRepository
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
abstract class ChartRepository extends BaseRepository
{
    use DatabaseHelper;

    /**
     * @var array
     */
    protected array $params;

    /**
     * @var string
     */
    protected string $aggregate;

    /**
     * @var string
     */
    protected string $aggregateField;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        parent::__construct($model);
        $this->params = request()->except('type', 'collection', 'aggregate', 'aggregate_field');
        $this->aggregate = Str::lower(request()->input('aggregate'));
        $this->aggregateField = request()->input('aggregate_field', '*');
        $this->table = $model->getConnection()->getTablePrefix() . $model->getTable();
        if (strpos($this->table, '.')) {
            [$this->database, $this->table] = explode('.', $this->table);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function get(): array
    {
        $query = $this->query()->{$this->aggregate}($this->aggregateField);

        return $this->serialize($query);
    }

    /**
     * @param $data
     * @return array
     */
    abstract public function serialize($data): array;

    /**
     * @return Builder
     * @throws Exception
     */
    protected function query(): Builder
    {
        return QueryBuilder::of($this->model, $this->params);
    }
}
