<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ResourceGetter
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceGetter extends BaseRepository
{
    /**
     * @var array
     */
    protected array $params;

    /**
     * @param Model $model
     * @throws \Exception
     */
    public function __construct(Model $model)
    {
        $this->params = request()->query();
        parent::__construct($model);
    }

    /**
     * @param  bool $paginate
     * @return LengthAwarePaginator|Collection
     * @throws Exception
     */
    public function all(bool $paginate = true)
    {
        $pageParams = $this->params['page'] ?? [];
        if ($paginate) {
            return $this->query()->paginate(
                $pageParams['size'] ?? null,
                '*',
                'page',
                $pageParams['number'] ?? null
            );
        } else {
            return $this->query()->get();
        }
    }

    /**
     * @param $id
     * @return Model
     * @throws Exception
     */
    public function get($id): Model
    {
        $record = $this->query()->find($id);
        if (!$record) {
            $this->throwException('Collection not found');
        }

        return $record;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function count(): int
    {
        return $this->query()->count();
    }

    /**
     * @return Builder
     * @throws Exception
     */
    protected function query(): Builder
    {
        return QueryBuilder::of($this->model, $this->params);
    }
}
